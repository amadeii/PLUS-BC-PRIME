<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\ItemAdicional;
use App\Models\ConfigGeral;

class ComandaController extends Controller
{
    public function getComandas(Request $request)
    {
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

        return response()->json([
            'success' => true,
            'comandasConfiguradas' => $comandasConfiguradas,
            'abertas' => $abertas,
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

    public function findComanda($id, Request $request)
    {
        $empresaId = $request->empresa_id;

        $comanda = Pedido::with([
            'itens' => function ($q) {
                $q->with('produto:id,nome');
            }
        ])
        ->where('empresa_id', $empresaId)
        ->where('id', $id)
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
                    'id' => $item->id,
                    'produto_id' => $item->produto_id,
                    'produto_nome' => $item->produto->nome ?? '',
                    'quantidade' => $item->quantidade,
                    'valor_unitario' => $item->valor_unitario,
                    'sub_total' => $item->sub_total,
                    'observacao' => $item->observacao
                ];
            })
        ]);
    }

    public function adicionarItemComanda(Request $request)
    {
        $pedido = Pedido::findOrfail($request->pedido_id);

        $itemPedido = ItemPedido::create([
            'pedido_id' => $request->pedido_id,
            'produto_id' => $request->produto_id,
            'quantidade' => $request->quantidade,
            'valor_unitario' => $request->valor_unitario,
            'sub_total' => $request->quantidade * $request->valor_unitario,
            'observacao' => $request->observacao
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

        return response()->json([
            'success' => true,
            'item' => $itemPedido
        ]);
    }

}
