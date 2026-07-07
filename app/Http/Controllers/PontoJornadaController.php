<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PontoJornada;
use App\Models\PontoJornadaDia;

class PontoJornadaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ponto_jornada_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ponto_jornada_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ponto_jornada_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:ponto_jornada_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = PontoJornada::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->descricao), function ($q) use ($request) {
            return $q->where('descricao', 'LIKE', "%$request->descricao%");
        })
        ->orderBy('descricao', 'asc')
        ->paginate(__itensPagina());

        return view('ponto_jornada.index', compact('data'));
    }

    public function create()
    {
        return view('ponto_jornada.create');
    }

    public function edit($id)
    {
        $item = PontoJornada::with('dias')->findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('ponto_jornada.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {

            $jornada = PontoJornada::create([
                'empresa_id' => $request->empresa_id,
                'descricao' => $request->descricao,
                'intervalo_minutos' => $request->intervalo_minutos,
                'tolerancia_atraso' => $request->tolerancia_atraso,
                'hora_extra_apos_minutos' => $request->hora_extra_apos_minutos,
                'ativo' => $request->ativo ?? 0
            ]);

            if($request->dia_semana && sizeof($request->dia_semana) > 0){
                for($i=0; $i<sizeof($request->dia_semana); $i++){
                    PontoJornadaDia::create([
                        'jornada_id' => $jornada->id,
                        'dia_semana' => $request->dia_semana[$i],
                        'entrada' => $request->entrada[$i] ?? null,
                        'intervalo_inicio' => $request->intervalo_inicio[$i] ?? null,
                        'intervalo_fim' => $request->intervalo_fim[$i] ?? null,
                        'saida' => $request->saida[$i] ?? null,
                    ]);
                }
            }

            session()->flash("flash_success", "Jornada criada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('ponto-jornada.index');
    }

    public function update(Request $request, $id)
    {
        $item = PontoJornada::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $item->descricao = $request->descricao;
            $item->intervalo_minutos = $request->intervalo_minutos;
            $item->tolerancia_atraso = $request->tolerancia_atraso;
            $item->hora_extra_apos_minutos = $request->hora_extra_apos_minutos;
            $item->ativo = $request->ativo ?? 0;
            $item->save();

            $item->dias()->delete();

            if($request->dia_semana && sizeof($request->dia_semana) > 0){
                for($i=0; $i<sizeof($request->dia_semana); $i++){
                    PontoJornadaDia::create([
                        'jornada_id' => $item->id,
                        'dia_semana' => $request->dia_semana[$i],
                        'entrada' => $request->entrada[$i] ?? null,
                        'intervalo_inicio' => $request->intervalo_inicio[$i] ?? null,
                        'intervalo_fim' => $request->intervalo_fim[$i] ?? null,
                        'saida' => $request->saida[$i] ?? null,
                    ]);
                }
            }

            session()->flash("flash_success", "Jornada alterada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('ponto-jornada.index');
    }

    public function destroy($id)
    {
        $item = PontoJornada::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->dias()->delete();
            $item->delete();
            session()->flash("flash_success", "Jornada removida com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('ponto-jornada.index');
    }

    public function show($id)
    {
        $item = PontoJornada::with('dias')->findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('ponto_jornada.show', compact('item'));
    }
}