<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\Produto;
use App\Models\ItemPedido;
use App\Models\ItemAdicional;
use App\Models\ConfigGeral;

class ComandaController extends Controller
{
    public function comandas(Request $request){
        $data = Pedido::where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->orderBy('created_at', 'desc')
        ->orderBy('comanda')
        ->get();

        $abertasNums = $data->pluck('comanda')->toArray();

        $config = ConfigGeral::where('empresa_id', $request->empresa_id)->first();
        $comandasConfiguradas = 1;
        $comandasFechadas = [];

        if ($config == null || $config->numero_inicial_comanda == null || $config->numero_final_comanda == null) {
            $comandasConfiguradas = 0;
        } else {
            for ($i = $config->numero_inicial_comanda; $i <= $config->numero_final_comanda; $i++) {
                if (!in_array($i, $abertasNums)) {
                    $comandasFechadas[] = [
                        'numero' => $i,
                        'total'  => 0,
                        'pedido_id' => null,
                    ];
                }
            }
        }

        // ABERTAS no formato que o app espera
        $abertas = $data->map(function ($p) {
            return [
                'numero' => (int) $p->comanda,
                'total' => (float) ($p->total ?? 0),
                'pedido_id' => (int) $p->id,
            ];
        })->values();

        $mesas = Mesa::where('status', 1)
        ->where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        return response()->json([
            'success' => true,
            'comandasConfiguradas' => $comandasConfiguradas,
            'abertas' => $abertas,
            'mesas' => $mesas,
            'disponiveis' => $comandasFechadas,
        ]);
    }

    public function abrirComanda(Request $request)
    {
        $empresaId = $request->empresa_id;
        $numero = $request->numero;

        $existe = Pedido::where('empresa_id', $empresaId)
        ->where('status', 1)
        ->where('comanda', $numero)
        ->first();

        if ($existe) {
            return response()->json([
                'success' => true,
                'pedido_id' => $existe->id,
                'numero' => (int) $existe->comanda,
                'total' => (float) ($existe->valor_total ?? 0),
            ]);
        }

        $pedido = Pedido::create([
            'empresa_id' => $empresaId,
            'comanda' => $numero,
            'status' => 1,
            'cliente_nome' => $request->nome,
            'cliente_fone' => $request->telefone,
            'total' => 0,
        ]);

        return response()->json([
            'success' => true,
            'pedido_id' => $pedido->id,
            'numero' => (int) $pedido->comanda,
            'total' => 0,
        ]);
    }

    public function comandaDetalhe(Request $request)
    {
        $empresaId = $request->empresa_id;
        $pedido_id = $request->pedido_id;

        $comanda = Pedido::with([
            'itens' => function ($q) {
                $q->with('produto:id,nome');
            }
        ])
        ->where('empresa_id', $empresaId)
        ->where('id', $pedido_id)
        ->first();

        if (!$comanda) {
            $comanda = Pedido::create([
                'empresa_id' => $empresaId,
                'comanda' => $numero,
                'status' => 1,
                'valor_total' => 0,
            ]);
        }

        return response()->json([
            'id' => $comanda->id,
            'numero' => $comanda->comanda,
            'aberta' => $comanda->status ? true : false,
            'itens' => $comanda->itens->map(function ($item) {
                return [
                    'item_id' => $item->id,
                    'produto_id' => $item->produto_id,
                    'produto_nome' => $item->produto->nome ?? '',
                    'quantidade' => $item->quantidade,
                    'valor_unitario' => $item->valor_unitario,
                    'sub_total' => $item->sub_total,
                    'observacao' => $item->observacao,
                    'adicionais' => $item->adicionais->map(function ($a) {
                        return [
                            'id' => $a->adicional->id ?? $a->adicional_id,
                            'nome' => $a->adicional->nome ?? '',
                            'valor' => $a->adicional->valor ?? 0,
                        ];
                    })
                ];
            })
        ]);
    }

    public function comandaAddItem(Request $request)
    {
        $pedido = Pedido::findOrFail($request->pedido_id);

        $itemPedido = ItemPedido::create([
            'pedido_id' => $request->pedido_id,
            'produto_id' => $request->produto_id,
            'quantidade' => $request->quantidade,
            'valor_unitario' => $request->valor_unitario,
            'sub_total' => $request->sub_total,
            'observacao' => $request->observacao ?? '',
            'adicionais' => []
        ]);

        if (!empty($request->adicionais)) {
            foreach ($request->adicionais as $adicionalId) {
                ItemAdicional::create([
                    'item_pedido_id' => $itemPedido->id,
                    'adicional_id' => $adicionalId
                ]);
            }
        }

        $pedido->sumTotal();
        $pedido->refresh();

        return response()->json([
            'success' => true,
            'item_id' => $itemPedido->id,
            'item' => $itemPedido,
            'total' => $pedido->total
        ]);
    }

    public function comandaUpdateItem(Request $request){
        $item = ItemPedido::findOrFail($request->item_id);

        $item->quantidade = $request->quantidade;
        $item->sub_total = $request->sub_total;

        $item->adicionais()->delete();
        if (!empty($request->adicionais)) {
            foreach ($request->adicionais as $adicionalId) {
                ItemAdicional::create([
                    'item_pedido_id' => $item->id,
                    'adicional_id' => $adicionalId
                ]);
            }
        }

        $item->save();

        $pedido = Pedido::findOrFail($item->pedido_id);

        $pedido->sumTotal();
        $pedido->refresh();

        return response()->json([
            'success' => true,
            'item' => $item,
            'total' => $pedido->total
        ]);
    }

    public function comandaRemoveItem(Request $request)
    {
        $item = ItemPedido::findOrFail($request->item_id);
        $pedido = $item->pedido;

        ItemAdicional::where('item_pedido_id', $item->id)->delete();
        $item->delete();
        $pedido->sumTotal();
        $pedido->refresh();

        return response()->json([
            'success' => true,
            'total' => $pedido->total
        ]);
    }

    public function adicionais(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|integer'
        ]);

        $produto = Produto::with('adicionaisMobile')->findOrFail($request->produto_id);

        $adicionais = $produto->adicionaisMobile->map(function ($item) {
            return [
                'id' => $item->adicional_id,
                'nome' => $item->adicional->nome ?? '',
                'valor' => (float)($item->valor ?? $item->adicional->valor ?? 0),
            ];
        })->values();

        return response()->json($adicionais);
    }

}
