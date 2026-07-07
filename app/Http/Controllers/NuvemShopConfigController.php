<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NuvemShopConfig;
use App\Models\NuvemShopExecucao;
use App\Utils\NuvemShopUtil;

class NuvemShopConfigController extends Controller
{

    protected $util;

    public function __construct(NuvemShopUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request){
        $item = NuvemShopConfig::where('empresa_id', $request->empresa_id)
        ->first();

        return view('nuvem_shop_config.index', compact('item'));
    }

    public function store(Request $request){
        $item = NuvemShopConfig::where('empresa_id', $request->empresa_id)
        ->first();

        if($item == null){
            NuvemShopConfig::create($request->all());
            session()->flash("flash_success", "Configuração criada com sucesso!");
        }else{
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Configuração atualizada com sucesso!");
        }
        return redirect()->back();
    }

    public function logsCron(Request $request)
    {
        $empresa_id = $request->empresa_id;
        $status = $request->status;
        $data_inicio = $request->data_inicio;
        $data_fim = $request->data_fim;

        $data = NuvemShopExecucao::when($empresa_id, function ($q) use ($empresa_id) {
            return $q->where('empresa_id', $empresa_id);
        })
        ->when($status, function ($q) use ($status) {
            return $q->where('status', $status);
        })
        ->when($data_inicio, function ($q) use ($data_inicio) {
            return $q->whereDate('created_at', '>=', $data_inicio);
        })
        ->when($data_fim, function ($q) use ($data_fim) {
            return $q->whereDate('created_at', '<=', $data_fim);
        })
        ->orderBy('id', 'desc')
        ->paginate(20);

        return view('nuvem_shop_config.logs_cron', compact('data'));
    }
}
