<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdemProducao;

class OrdemProducaoViewController extends Controller
{
    public function index($hash){
        $ordem = OrdemProducao::where('hash_link', $hash)->first();

        $empresa = $ordem->empresa;
        return view('ordem_producao.link', compact('ordem', 'empresa'));
    }
}
