<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nbs;

class NbsController extends Controller
{
    private function validaNbs(){

        if(Nbs::count() > 0){
            return;
        }
        $texto = file_get_contents(public_path('nbs.txt'));
        $lines = preg_split("/\r\n|\r|\n/", $texto);

        foreach($lines as $l){
            $str1 = explode(';', $l);
            $codigo = $str1[0];
            $descricao = $str1[1];

            $c = preg_replace('/[^0-9]/', '', $codigo);
            if(strlen($c) == 9){
                Nbs::create([
                    'codigo' => $codigo,
                    'descricao' => $descricao
                ]);
            }
        }
    }

    public function create(){
        return view('nbs.create');
    }

    public function edit($id){
        $item = Nbs::findOrFail($id);
        return view('nbs.edit', compact('item'));
    }

    public function store(Request $request){
        Nbs::create($request->all());
        session()->flash('flash_success', 'Registro adicionado!');
        return redirect()->route('nbs.index');
    }

    public function update(Request $request, $id){
        $item = Nbs::findOrFail($id);

        $item->fill($request->all())->save();
        session()->flash('flash_success', 'Registro atualizado!');
        return redirect()->route('nbs.index');
    }

    public function destroy($id){
        $item = Nbs::findOrFail($id);
        $item->delete();
        session()->flash('flash_success', 'Registro removido!');
        return redirect()->back();
    }

    public function index(Request $request){

        $this->validaNbs();
        $data = Nbs::
        when($request->codigo, function ($q) use ($request) {
            return $q->where('codigo','LIKE', "%$request->codigo%");
        })
        ->when($request->descricao, function ($q) use ($request) {
            return $q->where('descricao','LIKE', "%$request->descricao%");
        })
        ->paginate(30);

        return view('nbs.index', compact('data'));
    }
}
