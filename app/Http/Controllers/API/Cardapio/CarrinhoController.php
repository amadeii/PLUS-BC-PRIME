<?php

namespace App\Http\Controllers\API\Cardapio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemCarrinhoCardapio;
use App\Models\CarrinhoCardapio;

class CarrinhoController extends Controller
{
    public function carrinhoCount(Request $request)
    {
        $cartToken = $request->input('session_cart_cardapio');
        $userToken = $request->input('session_cart_user');

        $carrinho = CarrinhoCardapio::where('session_cart_cardapio', $cartToken)
        ->where('session_cart_user', $userToken)
        ->first();

        if (!$carrinho) {
            return response()->json(0, 200);
        }

        $count = ItemCarrinhoCardapio::where('carrinho_id', $carrinho->id)->count();

        return response()->json($count, 200);
    }

    public function validaEstoque(Request $request){
        $item = ItemCarrinhoCardapio::findOrFail($request->item_id);
        if($item->servico){
            return response()->json("estoque ok", 200);
        }
        $quantidade = $request->quantidade;
        $produto = $item->produto;
        if($produto->gerenciar_estoque){
            if(!$produto->estoque || $produto->estoque->quantidade < $quantidade){
                return response()->json("Estoque insuficiente!", 401);
            }
        }
        return response()->json("estoque ok", 200);
    }

    public function atualizaQuantidade(Request $request){
        $item = ItemCarrinhoCardapio::findOrFail($request->item_id);
        $item->quantidade = $request->quantidade;
        $item->sub_total = $item->quantidade * $item->valor_unitario;
        $item->save();

        $carrinho = CarrinhoCardapio::findOrFail($item->carrinho_id);
        $carrinho->valor_total = $carrinho->itens->sum('sub_total') + $carrinho->valor_frete;

        $carrinho->save();
        $item->total_carrinho = $carrinho->valor_total;
        return response()->json($item, 200);
    }

    public function removeItem(Request $request){
        $item = ItemCarrinhoCardapio::findOrFail($request->item_id);
        $item->adicionais()->delete();
        $item->sabores()->delete();
        $item->delete();

        $carrinho = $item->carrinho;
        $carrinho->valor_total = $carrinho->itens->sum('sub_total') + $carrinho->valor_frete - $carrinho->valor_desconto;
        $carrinho->save();
        return response()->json($carrinho, 200);
    }

}
