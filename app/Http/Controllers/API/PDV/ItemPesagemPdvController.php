<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use App\Models\PdvPesagemItem;
use App\Models\ComandaPesoItem;
use App\Models\Pedido;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemPesagemPdvController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = $request->empresa_id;

        $itensPesagem = PdvPesagemItem::query()
        ->with('produto:id,nome,codigo_barras')
        ->where('empresa_id', $empresa_id)
        ->where('status', true)
        ->orderBy('ordem')
        ->get()
        ->map(function ($item) {

            return [
                'id' => $item->id,
                'produto_id' => $item->produto_id,
                'nome' => $item->produto?->nome ?? '',
                'codigo_barras' => $item->produto?->codigo_barras ?? '',
                'valor' => (float) $item->valor,
                'sem_peso' => (bool) $item->sem_peso,
                'ordem' => $item->ordem,
            ];

        });

        $itensComanda = ComandaPesoItem::query()
        ->with('produto:id,nome,codigo_barras')
        ->where('empresa_id', $empresa_id)
        ->where('ativo', true)
        ->orderBy('ordem')
        ->get()
        ->map(function ($item) {

            return [
                'id' => $item->id,
                'produto_id' => $item->produto_id,
                'nome' => $item->produto?->nome ?? '',
                'codigo_barras' => $item->produto?->codigo_barras ?? '',
                'ordem' => $item->ordem,
            ];

        });

        return response()->json([
            'success' => true,
            'data' => [
                'itens_pesagem' => $itensPesagem,
                'itens_comanda' => $itensComanda
            ]
        ]);
    }

    public function salvarPedido(Request $request)
    {
        DB::beginTransaction();

        try {

            $pedido = new Pedido();
            $pedido->empresa_id = $request->empresa_id;
            $pedido->cliente_id = null;
            $pedido->funcionario_id = $request->funcionario_id ?? null;
            $pedido->cliente_nome = 'BALANÇA';
            $pedido->cliente_fone = null;
            $pedido->comanda = $request->comanda;
            $pedido->observacao = $request->usando_livre ? 'Pedido balança - refeição livre' : 'Pedido balança - peso';
            $pedido->tipo_pagamento = null;
            $pedido->mesa = null;
            $pedido->data_fechamento = null;
            $pedido->total = $request->total;
            $pedido->acrescimo = 0;
            $pedido->desconto = 0;
            $pedido->status = 1;
            $pedido->em_atendimento = 1;
            $pedido->confirma_mesa = 0;
            $pedido->nfce_id = null;
            $pedido->mesa_id = null;
            $pedido->local_pedido = 'BALANCA';
            $pedido->session_cart_cardapio = null;
            $pedido->session_cart_user = null;
            $pedido->save();

            $item = new ItemPedido();
            $item->pedido_id = $pedido->id;
            $item->produto_id = $request->produto_id;
            $item->funcionario_id = $request->funcionario_id ?? null;
            $item->observacao = $request->usando_livre ? 'Refeição livre pela balança' : 'Pesagem automática';
            $item->estado = 'finalizado';
            $item->quantidade = $request->peso > 0 ? $request->peso : 1;
            $item->valor_unitario = $request->valor_kg;
            $item->sub_total = $request->total;
            $item->tempo_preparo = null;
            $item->ponto_carne = null;
            $item->tamanho_id = null;
            $item->impresso = 1;
            $item->nome_cardapio = null;
            $item->telefone_cardapio = null;
            $item->finalizado_pdv = 1;
            $item->peso_balanca = $request->peso ?? 0;
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido salvo com sucesso',
                'pedido_id' => $pedido->id,
                'item_id' => $item->id
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
}