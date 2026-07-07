<?php

namespace App\Http\Controllers;

use App\Models\MotivoRefugo;
use Illuminate\Http\Request;

class MotivoRefugoController extends Controller
{
    public function index(Request $request)
    {
        $query = MotivoRefugo::where('empresa_id', $request->empresa_id);

        $query->when($request->nome, function($q) use ($request){
            return $q->where('nome', 'like', "%{$request->nome}%");
        });

        $query->when($request->status != '', function($q) use ($request){
            return $q->where('ativo', $request->status);
        });

        $data = $query
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        return view('motivo_refugo.index', compact('data'));
    }

    public function create()
    {
        return view('motivo_refugo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|max:120',
        ],[
            'nome.required' => 'Informe o nome do motivo.',
        ]);

        try{

            MotivoRefugo::create([
                'empresa_id' => $request->empresa_id,
                'codigo' => $request->codigo,
                'nome' => $request->nome,
                'ativo' => $request->ativo ? 1 : 0,
            ]);

            session()->flash('flash_success', 'Motivo de refugo cadastrado com sucesso!');

        }catch(\Exception $e){
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('motivo-refugo.index');
    }

    public function show($id)
    {
        return redirect()->route('motivo-refugo.index');
    }

    public function edit(Request $request, $id)
    {
        $item = MotivoRefugo::where('empresa_id', $request->empresa_id)
        ->findOrFail($id);

        return view('motivo_refugo.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|max:120',
        ],[
            'nome.required' => 'Informe o nome do motivo.',
        ]);

        try{

            $item = MotivoRefugo::where('empresa_id', $request->empresa_id)
            ->findOrFail($id);

            $item->codigo = $request->codigo;
            $item->nome = $request->nome;
            $item->ativo = $request->ativo ? 1 : 0;
            $item->save();

            session()->flash('flash_success', 'Motivo de refugo atualizado com sucesso!');

        }catch(\Exception $e){
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('motivo-refugo.index');
    }

    public function destroy(Request $request, $id)
    {
        try{

            $item = MotivoRefugo::where('empresa_id', $request->empresa_id)
            ->findOrFail($id);

            $item->delete();

            session()->flash('flash_success', 'Motivo removido com sucesso!');

        }catch(\Exception $e){
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('motivo-refugo.index');
    }
}