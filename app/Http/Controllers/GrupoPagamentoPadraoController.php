<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GrupoPagamentoPadrao;

class GrupoPagamentoPadraoController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:grupo_pagamento_padrao_create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:grupo_pagamento_padrao_edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:grupo_pagamento_padrao_view', ['only' => ['show', 'index']]);
        // $this->middleware('permission:grupo_pagamento_padrao_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = GrupoPagamentoPadrao::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome_pagador), function ($q) use ($request) {
            return $q->where('nome_pagador', 'LIKE', "%$request->nome_pagador%");
        })
        ->when(!empty($request->documento_pagador), function ($q) use ($request) {
            return $q->where('documento_pagador', 'LIKE', "%$request->documento_pagador%");
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        return view('grupo_pagamento_padrao.index', compact('data'));
    }

    public function create()
    {
        return view('grupo_pagamento_padrao.create');
    }

    public function edit($id)
    {
        $item = GrupoPagamentoPadrao::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('grupo_pagamento_padrao.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {

            $request->merge([
                'valor_transporte' => $request->valor_transporte ? __convert_value_bd($request->valor_transporte) : 0,
                'valor_componente' => $request->valor_componente ? __convert_value_bd($request->valor_componente) : 0,
                'valor_parcela' => $request->valor_parcela ? __convert_value_bd($request->valor_parcela) : 0,
            ]);

            $request->validate([
                'nome_pagador' => 'required|max:80',
            ], [
                'nome_pagador.required' => 'Informe o nome do pagador!',
            ]);

            GrupoPagamentoPadrao::create($request->all());
            session()->flash("flash_success", "Grupo de pagamento padrão criado com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('grupo-pagamento-padrao.index');
    }

    public function update(Request $request, $id)
    {
        $item = GrupoPagamentoPadrao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $request->merge([
                'valor_transporte' => $request->valor_transporte ? __convert_value_bd($request->valor_transporte) : 0,
                'valor_componente' => $request->valor_componente ? __convert_value_bd($request->valor_componente) : 0,
                'valor_parcela' => $request->valor_parcela ? __convert_value_bd($request->valor_parcela) : 0,
            ]);

            $request->validate([
                'nome_pagador' => 'required|max:80',
            ], [
                'nome_pagador.required' => 'Informe o nome do pagador!',
            ]);

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Grupo de pagamento padrão alterado com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('grupo-pagamento-padrao.index');
    }

    public function destroy($id)
    {
        $item = GrupoPagamentoPadrao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $item->delete();
            session()->flash("flash_success", "Grupo de pagamento padrão removido com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('grupo-pagamento-padrao.index');
    }

    public function show($id)
    {
        $item = GrupoPagamentoPadrao::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('grupo_pagamento_padrao.show', compact('item'));
    }

    public function getById($id)
    {
        $item = GrupoPagamentoPadrao::where('empresa_id', request()->empresa_id)
        ->findOrFail($id);

        return response()->json($item);
    }
}