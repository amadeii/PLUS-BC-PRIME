<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecorrenciaContratoModelo;

class RecorrenciaContratoModeloController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:recorrencia_contrato_modelo_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:recorrencia_contrato_modelo_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:recorrencia_contrato_modelo_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:recorrencia_contrato_modelo_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = RecorrenciaContratoModelo::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->orderBy('nome', 'asc')
        ->paginate(__itensPagina());

        return view('recorrencia_contrato_modelos.index', compact('data'));
    }

    public function create()
    {
        return view('recorrencia_contrato_modelos.create');
    }

    public function store(Request $request)
    {
        try {

            RecorrenciaContratoModelo::create([
                'empresa_id' => request()->empresa_id,
                'nome' => $request->nome,
                'conteudo' => $request->conteudo,
                'ativo' => $request->ativo ? 1 : 0,
            ]);

            session()->flash("flash_success", "Modelo criado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-contrato-modelos.index');
    }

    public function edit($id)
    {
        $item = RecorrenciaContratoModelo::findOrFail($id);

        __validaObjetoEmpresa($item);

        return view('recorrencia_contrato_modelos.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RecorrenciaContratoModelo::findOrFail($id);

        __validaObjetoEmpresa($item);

        try {

            $item->fill([
                'nome' => $request->nome,
                'conteudo' => $request->conteudo,
                'ativo' => $request->ativo ? 1 : 0,
            ])->save();

            session()->flash("flash_success", "Modelo alterado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-contrato-modelos.index');
    }

    public function destroy($id)
    {
        $item = RecorrenciaContratoModelo::findOrFail($id);

        __validaObjetoEmpresa($item);

        try {

            $item->delete();

            session()->flash("flash_success", "Modelo removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-contrato-modelos.index');
    }

    public function show($id)
    {
        $item = RecorrenciaContratoModelo::findOrFail($id);

        __validaObjetoEmpresa($item);

        return view('recorrencia_contrato_modelos.show', compact('item'));
    }
}