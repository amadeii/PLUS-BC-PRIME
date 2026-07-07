<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setor;
use App\Models\CentroCusto;

class SetorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:setor_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setor_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:setor_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:setor_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Setor::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->codigo), function ($q) use ($request) {
            return $q->where('codigo', 'LIKE', "%$request->codigo%");
        })
        ->orderBy('nome', 'asc')
        ->paginate(__itensPagina());

        return view('setor.index', compact('data'));
    }

    public function create()
    {   
        $centrosDeCusto = CentroCusto::where('empresa_id', request()->empresa_id)
        ->orderBy('nome')->get();
        return view('setor.create', compact('centrosDeCusto'));
    }

    public function edit($id)
    {
        $item = Setor::findOrFail($id);
        __validaObjetoEmpresa($item);
        $centrosDeCusto = CentroCusto::where('empresa_id', request()->empresa_id)
        ->orderBy('nome')->get();
        return view('setor.edit', compact('item', 'centrosDeCusto'));
    }

    public function store(Request $request)
    {
        try {

            $request->merge([
                'custo_hora' => __convert_value_bd($request->custo_hora)
            ]);

            $request->validate([
                'codigo' => 'required|max:10|unique:setors,codigo,NULL,id,empresa_id,' . request()->empresa_id,
            ], [
                'codigo.required' => 'Informe o código!',
                'codigo.unique' => 'Já existe um setor com este código para esta empresa!',
            ]);

            Setor::create($request->all());
            session()->flash("flash_success", "Setor criado com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('setor.index');
    }

    public function update(Request $request, $id)
    {
        $item = Setor::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $request->merge([
                'custo_hora' => __convert_value_bd($request->custo_hora)
            ]);

            $request->validate([
                'codigo' => 'required|max:10|unique:setors,codigo,' . $item->id . ',id,empresa_id,' . request()->empresa_id,
            ], [
                'codigo.required' => 'Informe o código!',
                'codigo.unique' => 'Já existe um setor com este código para esta empresa!',
            ]);

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Setor alterado com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('setor.index');
    }

    public function destroy($id)
    {
        $item = Setor::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $item->delete();
            session()->flash("flash_success", "Setor removido com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('setor.index');
    }

    public function show($id)
    {
        $item = Setor::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('setor.show', compact('item'));
    }
}