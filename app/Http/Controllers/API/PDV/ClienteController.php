<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Cidade;
use App\Models\Funcionario;

class ClienteController extends Controller
{
    public function all(Request $request){
        $data = Cliente::where('empresa_id', $request->empresa_id)
        ->select('id', 'razao_social', 'cpf_cnpj', 'rua', 'numero', 'bairro', 'complemento', 'status', 'ie', 'cidade_id', 'cep', 'nome_fantasia')
        ->with('cidade')
        ->where('status', 1)
        ->get();
        return response()->json($data, 200);
    }

    public function vendedores(Request $request){
        $data = Funcionario::where('empresa_id', $request->empresa_id)
        ->select('id', 'nome', 'cpf_cnpj', 'rua', 'numero', 'bairro', 'status', 'cidade_id')
        ->with('cidade')
        ->where('status', 1)
        ->get();
        return response()->json($data, 200);
    }

    public function cidades(){
        $data = Cidade::select('id', 'nome', 'uf', 'codigo')->get();
        return response()->json($data, 200);
    }

    public function store(Request $request){

        $cliente = Cliente::create([
            'empresa_id' => $request->empresa_id,
            'razao_social' => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'cpf_cnpj' => $request->cpf_cnpj,
            'ie' => $request->ie,
            'rua' => $request->rua,
            'numero' => $request->numero,
            'bairro' => $request->bairro,
            'cidade_id' => $request->cidade_id,
            'cep' => $request->cep,
            'status' => $request->status,
            'complemento' => $request->complemento ?? '',
        ]);

        $cliente->load('cidade');

        return response()->json($cliente, 201);
    }

    public function update(Request $request){

        $cliente = Cliente::findOrFail($request->id);

        $data = [
            'razao_social' => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'cpf_cnpj' => $request->cpf_cnpj,
            'ie' => $request->ie,
            'rua' => $request->rua,
            'numero' => $request->numero,
            'bairro' => $request->bairro,
            'cidade_id' => $request->cidade_id,
            'cep' => $request->cep,
            'status' => $request->status,
            'complemento' => $request->complemento ?? '',
        ];

        $cliente->fill($data)->save();
        $cliente->load('cidade');

        return response()->json($cliente, 201);
    }
}
