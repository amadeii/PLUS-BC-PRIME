<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index(Request $request){

        $empresaId = $request->empresa_id;
        $data = Cliente::where('empresa_id', $empresaId)
        ->orderBy('razao_social')
        ->select('id', 'razao_social as nome', 'cpf_cnpj as documento')
        ->where('status', 1)->get();

        return response()->json($data, 200);
    }
}
