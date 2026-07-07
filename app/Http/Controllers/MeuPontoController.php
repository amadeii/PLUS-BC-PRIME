<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PontoRegistro;
use App\Models\PontoConfiguracao;
use Carbon\Carbon;

class MeuPontoController extends Controller
{
    public function index(Request $request)
    {
        $funcionario = auth()->user()->funcionario;

        if (!$funcionario) {
            session()->flash('flash_error', 'Usuário não está vinculado a um funcionário.');
            return redirect()->back();
        }

        $registros = PontoRegistro::where('funcionario_id', $funcionario->id)
            ->orderBy('data_hora', 'desc')
            ->limit(20)
            ->get();

        $ultimoRegistro = PontoRegistro::where('funcionario_id', $funcionario->id)
            ->orderBy('data_hora', 'desc')
            ->first();

        $status = 'Pronto para entrada';
        $proximoTipo = 'entrada';

        if ($ultimoRegistro && $ultimoRegistro->tipo == 'entrada') {
            $status = 'Aguardando saída';
            $proximoTipo = 'saida';
        }

        return view('ponto.meu_ponto', compact(
            'registros',
            'status',
            'proximoTipo',
            'ultimoRegistro'
        ));
    }

    public function baterPonto(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:entrada,saida',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $funcionario = auth()->user()->funcionario;

        if (!$funcionario) {
            return response()->json([
                'error' => true,
                'message' => 'Usuário não está vinculado a um funcionário.'
            ], 422);
        }

        $ultimoRegistro = PontoRegistro::where('funcionario_id', $funcionario->id)
            ->orderBy('data_hora', 'desc')
            ->first();

        if ($ultimoRegistro) {
            if ($ultimoRegistro->tipo == $request->tipo) {
                return response()->json([
                    'error' => true,
                    'message' => $request->tipo == 'entrada'
                        ? 'Você já registrou entrada. Registre a saída primeiro.'
                        : 'Você já registrou saída. Registre a entrada primeiro.'
                ], 422);
            }
        } else {
            if ($request->tipo == 'saida') {
                return response()->json([
                    'error' => true,
                    'message' => 'Você precisa registrar uma entrada antes da saída.'
                ], 422);
            }
        }

        if ($request->tipo == 'saida') {
            $temEntradaHoje = PontoRegistro::where('funcionario_id', $funcionario->id)
                ->whereDate('data_hora', now()->toDateString())
                ->where('tipo', 'entrada')
                ->exists();

            if (!$temEntradaHoje) {
                return response()->json([
                    'error' => true,
                    'message' => 'Você precisa registrar uma entrada hoje antes da saída.'
                ], 422);
            }
        }

        $config = PontoConfiguracao::where('empresa_id', $funcionario->empresa_id)->first();

        if ($config && $config->latitude && $config->longitude) {
            $distancia = $this->calcularDistancia(
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $config->latitude,
                (float) $config->longitude
            );

            if ($distancia > (int) $config->raio_permitido) {
                return response()->json([
                    'error' => true,
                    'message' => 'Fora da área permitida. Distância aproximada: ' . number_format($distancia, 0, ',', '.') . 'm'
                ], 422);
            }
        }

        $registro = PontoRegistro::create([
            'empresa_id' => $funcionario->empresa_id,
            'funcionario_id' => $funcionario->id,
            'data_hora' => now(),
            'tipo' => $request->tipo,
            'ip' => $request->ip(),
            'device_id' => $request->header('User-Agent'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 1,
            'hash_integridade' => md5(
                $funcionario->id .
                now()->format('Y-m-d H:i:s') .
                $request->tipo .
                $request->ip()
            )
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->tipo == 'entrada'
                ? 'Entrada registrada com sucesso!'
                : 'Saída registrada com sucesso!',
            'data' => $registro
        ]);
    }

    private function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        $raioTerra = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $raioTerra * $c;
    }
}