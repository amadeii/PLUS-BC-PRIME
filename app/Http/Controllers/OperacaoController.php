<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operacao;
use App\Models\Setor;

class OperacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:operacao_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:operacao_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:operacao_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:operacao_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Operacao::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->codigo), function ($q) use ($request) {
            return $q->where('codigo', 'LIKE', "%$request->codigo%");
        })
        ->orderBy('nome', 'asc')
        ->paginate(__itensPagina());

        return view('operacao.index', compact('data'));
    }

    public function create()
    {
        $setores = Setor::where('empresa_id', request()->empresa_id)
        ->orderBy('nome')->get();
        return view('operacao.create', compact('setores'));
    }

    public function edit($id)
    {
        $item = Operacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        $setores = Setor::where('empresa_id', request()->empresa_id)
        ->orderBy('nome')->get();
        return view('operacao.edit', compact('item', 'setores'));
    }

    public function store(Request $request)
    {
        try {

            $request->merge([
                'empresa_id' => request()->empresa_id
            ]);

            $request->validate([
                'codigo' => 'required|max:10|unique:operacaos,codigo,NULL,id,empresa_id,' . request()->empresa_id,
            ], [
                'codigo.required' => 'Informe o código!',
                'codigo.unique' => 'Já existe uma operação com este código para esta empresa!',
            ]);

            Operacao::create($request->all());
            session()->flash("flash_success", "Operação criada com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('operacao.index');
    }

    public function update(Request $request, $id)
    {
        $item = Operacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $request->validate([
                'codigo' => 'required|max:10|unique:operacaos,codigo,' . $item->id . ',id,empresa_id,' . request()->empresa_id,
            ], [
                'codigo.required' => 'Informe o código!',
                'codigo.unique' => 'Já existe uma operação com este código para esta empresa!',
            ]);

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Operação alterada com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('operacao.index');
    }

    public function destroy($id)
    {
        $item = Operacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {

            $item->delete();
            session()->flash("flash_success", "Operação removida com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('operacao.index');
    }

    public function show($id)
    {
        $item = Operacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('operacao.show', compact('item'));
    }
}