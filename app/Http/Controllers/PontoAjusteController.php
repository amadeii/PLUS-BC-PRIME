<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PontoRegistro;
use App\Models\PontoAjuste;

class PontoAjusteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ponto_ajuste_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ponto_ajuste_view', ['only' => ['show', 'index']]);
    }

    public function index(Request $request)
    {
        $data = PontoAjuste::with(['registro.funcionario', 'usuario'])
        ->whereHas('registro', function($q){
            $q->where('empresa_id', request()->empresa_id);
        })
        ->when(!empty($request->funcionario_id), function ($q) use ($request) {
            return $q->whereHas('registro', function($sub) use ($request){
                $sub->where('funcionario_id', $request->funcionario_id);
            });
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        $funcionarios = \App\Models\Funcionario::where('empresa_id', request()->empresa_id)
        ->orderBy('nome', 'asc')
        ->get();

        return view('ponto_ajuste.index', compact('data', 'funcionarios'));
    }

    public function create($registro_id)
    {
        $registro = PontoRegistro::with('funcionario')->findOrFail($registro_id);
        __validaObjetoEmpresa($registro);

        return view('ponto_ajuste.create', compact('registro'));
    }

    public function store(Request $request, $registro_id)
    {
        $registro = PontoRegistro::findOrFail($registro_id);
        __validaObjetoEmpresa($registro);

        try {

            $antes = [
                'data_hora' => $registro->data_hora,
                'tipo' => $registro->tipo,
                'status' => $registro->status,
                'ip' => $registro->ip,
                'device_id' => $registro->device_id,
                'latitude' => $registro->latitude,
                'longitude' => $registro->longitude,
                'hash_integridade' => $registro->hash_integridade,
            ];

            $registro->data_hora = $request->data_hora;
            $registro->tipo = $request->tipo;
            $registro->status = 'ajustado';
            $registro->save();

            $depois = [
                'data_hora' => $registro->data_hora,
                'tipo' => $registro->tipo,
                'status' => $registro->status,
                'ip' => $registro->ip,
                'device_id' => $registro->device_id,
                'latitude' => $registro->latitude,
                'longitude' => $registro->longitude,
                'hash_integridade' => $registro->hash_integridade,
            ];

            PontoAjuste::create([
                'ponto_registro_id' => $registro->id,
                'usuario_id' => auth()->id(),
                'motivo' => $request->motivo,
                'justificativa' => $request->justificativa,
                'antes_json' => $antes,
                'depois_json' => $depois,
            ]);

            session()->flash("flash_success", "Ajuste realizado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('ponto-registro.show', [$registro->id]);
    }

    public function show($id)
    {
        $item = PontoAjuste::with(['registro.funcionario', 'usuario'])->findOrFail($id);

        if($item->registro){
            __validaObjetoEmpresa($item->registro);
        }

        return view('ponto_ajuste.show', compact('item'));
    }
}