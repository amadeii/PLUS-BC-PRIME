<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VendaTemporaria;
use App\Models\User;

class VendaTemporariaController extends Controller
{
    public function index(Request $request){

        $usuarios = User::where('usuario_empresas.empresa_id', request()->empresa_id)
        ->join('usuario_empresas', 'users.id', '=', 'usuario_empresas.usuario_id')
        ->select('users.*')
        ->get();

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $usuario_id = $request->usuario_id;
        $start_time = $request->start_time;
        $end_time = $request->end_time;
        $estado = $request->estado;
        $tabela = $request->tabela;

        if($start_date){
            if($start_time){
                $start_date .= " $start_time:59";
            }else{
                $start_date .= " 00:00:00";
            }
        }

        if($end_date){
            if($end_time){
                $end_date .= " $end_time:59";
            }else{
                $end_date .= " 23:59:59";
            }
        }

        $data = VendaTemporaria::where('empresa_id', $request->empresa_id)
        ->orderBy('id', 'desc')
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->where('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->where('created_at', '<=', $end_date);
        })
        ->when(!empty($usuario_id), function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->when(!empty($tabela), function ($query) use ($tabela) {
            return $query->where('tabela', $tabela);
        })
        ->paginate(30);

        return view('venda_temporaria.index', compact('data', 'usuarios'));
    }
}
