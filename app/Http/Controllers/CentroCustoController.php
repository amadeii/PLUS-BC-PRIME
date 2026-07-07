<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CentroCusto;

class CentroCustoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:centro_custo_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:centro_custo_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:centro_custo_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:centro_custo_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = CentroCusto::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->codigo), function ($q) use ($request) {
            return $q->where('codigo', 'LIKE', "%$request->codigo%");
        })
        ->orderBy('nome', 'asc')
        ->paginate(__itensPagina());

        return view('centro_custo.index', compact('data'));
    }

    public function create()
    {
        return view('centro_custo.create');
    }

    public function edit($id)
    {
        $item = CentroCusto::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('centro_custo.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {

            $request->merge([
                'empresa_id' => request()->empresa_id,
                'codigo' => strtoupper(trim($request->codigo))
            ]);

            $request->validate([
                'codigo' => 'required|max:10|unique:centro_custos,codigo,NULL,id,empresa_id,' . request()->empresa_id,
                'nome' => 'required|max:100'
            ], [
                'codigo.required' => 'Informe o código!',
                'codigo.unique' => 'Já existe um centro de custo com este código para esta empresa!',
                'nome.required' => 'Informe o nome!',
            ]);

            CentroCusto::create($request->all());
            session()->flash("flash_success", "Centro de custo criado com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('centro-custo.index');
    }

    public function update(Request $request, $id)
    {
        $item = CentroCusto::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $request->merge([
                'codigo' => strtoupper(trim($request->codigo))
            ]);

            $request->validate([
                'codigo' => 'required|max:10|unique:centro_custos,codigo,' . $item->id . ',id,empresa_id,' . request()->empresa_id,
                'nome' => 'required|max:100'
            ], [
                'codigo.required' => 'Informe o código!',
                'codigo.unique' => 'Já existe um centro de custo com este código para esta empresa!',
                'nome.required' => 'Informe o nome!',
            ]);

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Centro de custo alterado com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('centro-custo.index');
    }

    public function destroy($id)
    {
        $item = CentroCusto::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $item->delete();
            session()->flash("flash_success", "Centro de custo removido com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('centro-custo.index');
    }

    public function show($id)
    {
        $item = CentroCusto::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('centro_custo.show', compact('item'));
    }
}