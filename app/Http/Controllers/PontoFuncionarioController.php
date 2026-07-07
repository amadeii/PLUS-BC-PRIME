<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PontoFuncionario;
use App\Models\PontoJornada;
use App\Models\Funcionario;

class PontoFuncionarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ponto_funcionario_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ponto_funcionario_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ponto_funcionario_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:ponto_funcionario_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = PontoFuncionario::with(['funcionario', 'jornada'])
        ->where('empresa_id', request()->empresa_id)
        ->when(!empty($request->funcionario_id), function ($q) use ($request) {
            return $q->where('funcionario_id', $request->funcionario_id);
        })
        ->when(!empty($request->jornada_id), function ($q) use ($request) {
            return $q->where('jornada_id', $request->jornada_id);
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->orderBy('nome', 'asc')
        ->get();

        $jornadas = PontoJornada::where('empresa_id', request()->empresa_id)
        ->where('ativo', 1)
        ->orderBy('descricao', 'asc')
        ->get();

        return view('ponto_funcionario.index', compact('data', 'funcionarios', 'jornadas'));
    }

    public function create()
    {
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->orderBy('nome', 'asc')
        ->get();

        if(sizeof($funcionarios) == 0){
            session()->flash("flash_warning", 'Cadastre um funcionário!');
            return redirect()->route('funcionarios.create');
        }

        $jornadas = PontoJornada::where('empresa_id', request()->empresa_id)
        ->where('ativo', 1)
        ->orderBy('descricao', 'asc')
        ->get();

        if(sizeof($jornadas) == 0){
            session()->flash("flash_warning", 'Cadastre uma jornada!');
            return redirect()->route('ponto-jornada.create');
        }

        return view('ponto_funcionario.create', compact('funcionarios', 'jornadas'));
    }

    public function edit($id)
    {
        $item = PontoFuncionario::findOrFail($id);
        __validaObjetoEmpresa($item);

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->orderBy('nome', 'asc')
        ->get();

        if(sizeof($funcionarios) == 0){
            session()->flash("flash_warning", 'Cadastre um funcionário!');
            return redirect()->route('funcionarios.create');
        }

        $jornadas = PontoJornada::where('empresa_id', request()->empresa_id)
        ->where('ativo', 1)
        ->orWhere('id', $item->jornada_id)
        ->orderBy('descricao', 'asc')
        ->get();

        if(sizeof($jornadas) == 0){
            session()->flash("flash_warning", 'Cadastre uma jornada!');
            return redirect()->route('ponto-jornada.create');
        }

        return view('ponto_funcionario.edit', compact('item', 'funcionarios', 'jornadas'));
    }

    public function store(Request $request)
    {
        try {

            PontoFuncionario::create($request->all());
            session()->flash("flash_success", "Vínculo criado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('ponto-funcionario.index');
    }

    public function update(Request $request, $id)
    {
        $item = PontoFuncionario::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Vínculo alterado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('ponto-funcionario.index');
    }

    public function destroy($id)
    {
        $item = PontoFuncionario::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->delete();
            session()->flash("flash_success", "Vínculo removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('ponto-funcionario.index');
    }

    public function show($id)
    {
        $item = PontoFuncionario::with(['funcionario', 'jornada'])->findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('ponto_funcionario.show', compact('item'));
    }
}