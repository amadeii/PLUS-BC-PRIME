<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PontoRegistro;
use App\Models\Funcionario;
use App\Models\PontoConfiguracao;
use Carbon\Carbon;

class PontoRegistroController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ponto_registro_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ponto_registro_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:ponto_registro_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = PontoRegistro::with('funcionario')
            ->where('empresa_id', request()->empresa_id)
            ->when(!empty($request->funcionario_id), function ($q) use ($request) {
                return $q->where('funcionario_id', $request->funcionario_id);
            })
            ->when(!empty($request->tipo), function ($q) use ($request) {
                return $q->where('tipo', $request->tipo);
            })
            ->when(!empty($request->data_inicial), function ($q) use ($request) {
                return $q->whereDate('data_hora', '>=', $request->data_inicial);
            })
            ->when(!empty($request->data_final), function ($q) use ($request) {
                return $q->whereDate('data_hora', '<=', $request->data_final);
            })
            ->orderBy('data_hora', 'desc')
            ->paginate(__itensPagina());

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
            ->orderBy('nome', 'asc')
            ->get();

        return view('ponto_registro.index', compact('data', 'funcionarios'));
    }

    public function create()
    {
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
            ->orderBy('nome', 'asc')
            ->get();

        if (sizeof($funcionarios) == 0) {
            session()->flash("flash_warning", 'Cadastre um funcionário!');
            return redirect()->route('funcionarios.create');
        }

        return view('ponto_registro.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        try {
            $funcionario = Funcionario::findOrFail($request->funcionario_id);
            __validaObjetoEmpresa($funcionario);

            $config = PontoConfiguracao::where('empresa_id', request()->empresa_id)->first();

            if ($config && $config->temGeolocalizacao()) {
                if (empty($request->latitude) || empty($request->longitude)) {
                    throw new \Exception('É obrigatório informar a localização para registrar o ponto.');
                }

                $dentroDoRaio = $config->dentroDoRaio($request->latitude, $request->longitude);

                if (!$dentroDoRaio && !$config->permitir_fora_area) {
                    throw new \Exception('Você está fora da área permitida para registrar o ponto.');
                }

                // Futuramente, se adicionar campo observacao no request:
                // if (!$dentroDoRaio && $config->permitir_fora_area && $config->exigir_observacao_fora_area && empty($request->observacao)) {
                //     throw new \Exception('Informe uma observação para registrar o ponto fora da área permitida.');
                // }
            }

            $agora = Carbon::now();

            $registrosHoje = PontoRegistro::where('empresa_id', request()->empresa_id)
                ->where('funcionario_id', $request->funcionario_id)
                ->whereDate('data_hora', $agora->format('Y-m-d'))
                ->orderBy('data_hora', 'asc')
                ->get();

            $tipo = $this->proximoTipo($registrosHoje);

            $hash = hash(
                'sha256',
                request()->empresa_id . '|' .
                $request->funcionario_id . '|' .
                $agora->format('Y-m-d H:i:s') . '|' .
                $tipo . '|' .
                $request->ip() . '|' .
                ($request->device_id ?? '') . '|' .
                ($request->latitude ?? '') . '|' .
                ($request->longitude ?? '') . '|' .
                config('app.key')
            );

            PontoRegistro::create([
                'empresa_id' => request()->empresa_id,
                'funcionario_id' => $request->funcionario_id,
                'data_hora' => $agora,
                'tipo' => $tipo,
                'ip' => $request->ip(),
                'device_id' => $request->device_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'valido',
                'hash_integridade' => $hash
            ]);

            session()->flash("flash_success", "Ponto registrado como {$this->nomeTipo($tipo)} com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('ponto-registro.index');
    }

    public function show($id)
    {
        $item = PontoRegistro::with('funcionario')->findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('ponto_registro.show', compact('item'));
    }

    public function destroy($id)
    {
        $item = PontoRegistro::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->delete();
            session()->flash("flash_success", "Registro removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('ponto-registro.index');
    }

    private function proximoTipo($registrosHoje)
    {
        $tipos = $registrosHoje->pluck('tipo')->toArray();

        if (!in_array('entrada', $tipos)) {
            return 'entrada';
        }

        if (!in_array('intervalo_inicio', $tipos)) {
            return 'intervalo_inicio';
        }

        if (!in_array('intervalo_fim', $tipos)) {
            return 'intervalo_fim';
        }

        if (!in_array('saida', $tipos)) {
            return 'saida';
        }

        throw new \Exception('Todos os registros do dia já foram lançados para este funcionário.');
    }

    private function nomeTipo($tipo)
    {
        return match ($tipo) {
            'entrada' => 'Entrada',
            'intervalo_inicio' => 'Início de intervalo',
            'intervalo_fim' => 'Fim de intervalo',
            'saida' => 'Saída',
            default => $tipo
        };
    }
}