<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\VendaTemporaria;
use App\Models\ItemVendaTemporaria;
use App\Models\ItemVendaTemporariaRemovido;

class VendaTemporariaController extends Controller
{
    
    public function store(Request $request){

        $vendaTemporaria = VendaTemporaria::findOrFail($request->venda_temporaria_id);
        $vendaTemporaria->cliente_id = $request->cliente_id;
        $total = 0;

        $vendaTemporaria->itens()->delete();

        $itens = is_array($request->itens)
        ? $request->itens
        : json_decode($request->itens, true);

        if($itens){
            foreach($itens as $i){
                $total += (float)($i['quantidade']) * (float)($i['valor']);
                ItemVendaTemporaria::create([
                    'venda_id' => $vendaTemporaria->id,
                    'quantidade' => ($i['quantidade']),
                    'valor' => ($i['valor']),
                    'produto_id' => $i['produto_id']
                ]);

            }
        }
        $vendaTemporaria->total = $total;
        $vendaTemporaria->save();
    }

    public function abandonar(Request $request){
        $vendaTemporaria = VendaTemporaria::findOrFail($request->venda_temporaria_id);
        $vendaTemporaria->estado = 'abandonada';
        $vendaTemporaria->save();
    }

    public function itemRemove(Request $request){
        $vendaTemporaria = VendaTemporaria::findOrFail($request->venda_temporaria_id);

        ItemVendaTemporariaRemovido::create([
            'venda_id' => $vendaTemporaria->id,
            'quantidade' => ($request->quantidade),
            'valor' => ($request->valor),
            'produto_id' => $request->produto_id
        ]);

        $vendaTemporaria->itens()->delete();

        $itens = is_array($request->itens)
        ? $request->itens
        : json_decode($request->itens, true);
        $total = 0;
        foreach($itens as $i){
            $total += (float)($i['quantidade']) * (float)($i['valor']);
            ItemVendaTemporaria::create([
                'venda_id' => $vendaTemporaria->id,
                'quantidade' => ($i['quantidade']),
                'valor' => ($i['valor']),
                'produto_id' => $i['produto_id']
            ]);

        }
        $vendaTemporaria->total = $total;
        $vendaTemporaria->save();

    }

}
