<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Motorista;

class MotoristaController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('permission:motorista_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:motorista_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:motorista_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:motorista_delete', ['only' => ['destroy']]);
    }

    public function index(){
        $data = Motorista::where('empresa_id', request()->empresa_id)
        ->paginate(__itensPagina());

        return view('motorista.index', compact('data'));
    }

    public function create(){
        return view('motorista.create');
    }

    public function edit($id){
        $item = Motorista::findOrfail($id);
        __validaObjetoEmpresa($item);
        return view('motorista.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {

        if($request->padrao == 1){
            Motorista::where('empresa_id', $request->empresa_id)
            ->update(['padrao' => 0]);
        }

        $item = Motorista::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Padrão atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('motoristas.index');
    }

    public function store(Request $request)
    {
        // dd($request);
        if($request->padrao == 1){
            Motorista::where('empresa_id', $request->empresa_id)
            ->update(['padrao' => 0]);
        }
        try {
            Motorista::create($request->all());
            session()->flash("flash_success", "Motorista cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('motoristas.index');
    }

    public function destroy($id)
    {
        $item = Motorista::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $item->delete();
            session()->flash("flash_success", "Motorista removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

}
