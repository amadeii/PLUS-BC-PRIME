<?php

namespace App\Http\Controllers\Cardapio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracaoCardapio;
use App\Models\CategoriaProduto;
use App\Models\Produto;
use App\Models\Pedido;
use App\Models\Nfce;
use App\Models\Mesa;
use App\Models\ConfigGeral;
use App\Models\ItemPedido;
use App\Models\CarrinhoCardapio;
use App\Models\TamanhoPizza;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    private string $cookieCart = 'cardapio_cart_token';
    private string $cookieUser = 'cardapio_user_token';
    private string $cookieMesa = 'cardapio_mesa_hash';
    private string $cookieNome = 'cardapio_nome';
    private string $cookieTel  = 'cardapio_tel';
    private int $cookieMinutes = 60 * 24 * 30;

    public function __construct(){
        // NÃO usar session_start() no Laravel
    }

    private function _validaHash($config){
        $categorias = CategoriaProduto::where('cardapio', 1)
        ->where('empresa_id', $config->empresa_id)
        ->orderBy('nome', 'asc')
        ->whereNull('hash_delivery')
        ->where('status', 1)
        ->get();

        foreach($categorias as $c){
            $c->hash_delivery = Str::random(50);
            $c->save();
        }

        $produtos = Produto::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->where('cardapio', 1)
        ->whereNull('hash_delivery')
        ->get();

        foreach($produtos as $p){
            $p->hash_delivery = Str::random(50);
            $p->save();
        }
    }

    private function getCategorias($empresa_id){
        return CategoriaProduto::where('cardapio', 1)
        ->orderBy('nome', 'asc')
        ->where('status', 1)
        ->where('empresa_id', $empresa_id)
        ->get();
    }

    private function produtosEmDestaque($empresa_id){
        $data = Produto::where('empresa_id', $empresa_id)
        ->where('destaque_cardapio', 1)
        ->where('status', 1)
        ->where('cardapio', 1)
        ->get();

        $produtos = [];
        foreach($data as $item){
            if($item->gerenciar_estoque){
                if($item->estoque && $item->estoque->quantidade > 0){
                    $produtos[] = $item;
                }
            }else{
                $produtos[] = $item;
            }
        }
        return $produtos;
    }

    private function getOrCreateCartToken(Request $request): string
    {
        $token = $request->cookie($this->cookieCart);
        if(!$token){
            $token = Str::random(30);
            cookie()->queue($this->cookieCart, $token, $this->cookieMinutes);
        }
        return $token;
    }

    private function getOrCreateTokens(Request $request): array
    {
        $cartToken = $request->cookie($this->cookieCart);
        $userToken = $request->cookie($this->cookieUser);

        if (!$cartToken) {
            $cartToken = Str::random(30);
            cookie()->queue($this->cookieCart, $cartToken, $this->cookieMinutes);
        }

        if (!$userToken) {
            $userToken = Str::random(30);
            cookie()->queue($this->cookieUser, $userToken, $this->cookieMinutes);
        }

        return [$cartToken, $userToken];
    }

    private function _getCarrinho(Request $request)
    {
        [$cartToken, $userToken] = $this->getOrCreateTokens($request);

        return CarrinhoCardapio::where('session_cart_cardapio', $cartToken)
        ->where('session_cart_user', $userToken)
        ->first();
    }

    public function index(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);
        $this->_validaHash($config);

        $categorias = $this->getCategorias($config->empresa_id);
        $produtosEmDestaque = $this->produtosEmDestaque($config->empresa_id);

        $link = $request->link;
        $mesa = Mesa::where('hash', $link)->first();

        if(!$mesa){
            session()->flash("flash_error", "Mesa não encontrada!");
            return redirect()->back();
        }

        $mesaHashCookie = $request->cookie($this->cookieMesa);

        if (!$mesaHashCookie || $mesaHashCookie != $mesa->hash) {
            cookie()->queue(cookie()->forget($this->cookieCart));
            cookie()->queue(cookie()->forget($this->cookieUser));
            cookie()->queue(cookie()->forget($this->cookieNome));
            cookie()->queue(cookie()->forget($this->cookieTel));
        }
        // seta a mesa atual no cookie (persistente)
        cookie()->queue($this->cookieMesa, $mesa->hash, $this->cookieMinutes);

        // garante token do carrinho (persistente)
        $this->getOrCreateCartToken($request);

        $carrinho = $this->_getCarrinho($request);

        return view('qr_code_cardapio.index', compact(
            'config', 'categorias', 'produtosEmDestaque', 'carrinho', 'link', 'mesa'
        ));
    }

    public function pesquisa(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);
        $this->_validaHash($config);

        $categorias = $this->getCategorias($config->empresa_id);
        $pesquisa = $request->pesquisa;

        $produtos = Produto::where('produtos.empresa_id', $config->empresa_id)
        ->select('produtos.*')
        ->where('produtos.status', 1)
        ->where('produtos.cardapio', 1)
        ->when(!empty($pesquisa), function ($query) use ($pesquisa) {
            return $query->where('produtos.nome', 'like', "%$pesquisa%");
        })
        ->get();

        $link = $request->link;

        // garante token e pega carrinho
        $this->getOrCreateCartToken($request);
        $carrinho = $this->_getCarrinho($request);

        return view('qr_code_cardapio.pesquisa', compact(
            'config', 'categorias', 'carrinho', 'link', 'produtos'
        ));
    }

    public function ofertas(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);
        $categorias = $this->getCategorias($config->empresa_id);

        $produtos = Produto::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->where('oferta_cardapio', 1)
        ->where('cardapio', 1)
        ->get();

        $tamanho = TamanhoPizza::where('empresa_id', $config->empresa_id)
        ->orderBy('maximo_sabores', 'desc')
        ->first();

        $maximo_sabores_pizza = $tamanho ? $tamanho->maximo_sabores : 0;

        $tamanhosPizza = TamanhoPizza::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->with('produtos')
        ->get();

        $link = $request->link;

        $this->getOrCreateCartToken($request);
        $carrinho = $this->_getCarrinho($request);

        return view('qr_code_cardapio.ofertas', compact(
            'config', 'categorias', 'produtos', 'maximo_sabores_pizza', 'tamanhosPizza', 'link', 'carrinho'
        ));
    }

    public function conta(Request $request)
    {

        $config = ConfiguracaoCardapio::findOrFail($request->config_id);

        $this->getOrCreateCartToken($request);
        $carrinho = $this->_getCarrinho($request);

        if(!$carrinho){
            session()->flash("flash_error", "Nenhum item adicionado!");
            return redirect()->back();
        }

        $pedido = Pedido::where('empresa_id', $carrinho->empresa_id)
        ->where('status', 1)
        ->where('session_cart_cardapio', $carrinho->session_cart_cardapio)
        ->first();

        if($pedido == null){
            // limpa tokens como você fazia na sessão
            cookie()->queue(cookie()->forget($this->cookieCart));
            cookie()->queue(cookie()->forget($this->cookieNome));
            cookie()->queue(cookie()->forget($this->cookieTel));

            return redirect()->route('cardapio.index', [
                'link' => $request->link,
                'config_id' => $request->config_id
            ]);
        }

        $totalClientes = ItemPedido::where('pedido_id', $pedido->id)
        ->select('nome_cardapio')
        ->distinct()
        ->count();

        $itens = [];
        $subtotal = 0;

        $nomeAtual = $request->cookie($this->cookieNome); // equivalente ao session_nome_cardapio

        foreach($pedido->itens as $i){
            if($nomeAtual && $i->nome_cardapio == $nomeAtual){
                $itens[] = $i;
                $subtotal += $i->sub_total;
            }
        }

        $pedido->itens = $itens;

        $link = $request->link;
        $categorias = $this->getCategorias($config->empresa_id);

        $configGeral = ConfigGeral::where('empresa_id', $config->empresa_id)->first();
        $tiposPagamento = Nfce::tiposPagamento();

        if($configGeral != null){
            $configGeral->tipos_pagamento_pdv = $configGeral != null && $config->tipos_pagamento_pdv
            ? json_decode($configGeral->tipos_pagamento_pdv)
            : [];

            $temp = [];
            if(sizeof($configGeral->tipos_pagamento_pdv) > 0){
                foreach($tiposPagamento as $key => $t){
                    if(in_array($t, $configGeral->tipos_pagamento_pdv)){
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        return view('qr_code_cardapio.conta', compact(
            'pedido', 'link', 'config', 'categorias', 'carrinho', 'tiposPagamento', 'subtotal'
        ));
    }

    public function produtosDaCategoria(Request $request, $hash)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);
        $categorias = $this->getCategorias($config->empresa_id);

        $categoria = CategoriaProduto::where('hash_delivery', $hash)->first();
        if(!$categoria){
            abort(404);
        }

        $data = Produto::where('empresa_id', $config->empresa_id)
        ->where('categoria_id', $categoria->id)
        ->where('status', 1)
        ->where('cardapio', 1)
        ->get();

        $produtos = [];
        foreach($data as $item){
            if($item->gerenciar_estoque){
                if($item->estoque && $item->estoque->quantidade > 0){
                    $produtos[] = $item;
                }
            }else{
                $produtos[] = $item;
            }
        }

        $this->getOrCreateCartToken($request);
        $carrinho = $this->_getCarrinho($request);

        $tamanho = TamanhoPizza::where('empresa_id', $config->empresa_id)
        ->orderBy('maximo_sabores', 'desc')
        ->first();

        $maximo_sabores_pizza = $tamanho ? $tamanho->maximo_sabores : 0;

        $tamanhosPizza = TamanhoPizza::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->with('produtos')
        ->get();

        $link = $request->link;

        return view('qr_code_cardapio.produtos_categoria', compact(
            'config', 'categorias', 'categoria', 'produtos', 'carrinho',
            'maximo_sabores_pizza', 'tamanhosPizza', 'link'
        ));
    }

    public function produtoDetalhe(Request $request, $hash)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);

        $produto = Produto::where('empresa_id', $config->empresa_id)
        ->where('hash_delivery', $hash)
        ->where('status', 1)
        ->first();

        $this->_validaHash($config);
        $categorias = $this->getCategorias($config->empresa_id);
        $produtosEmDestaque = $this->produtosEmDestaque($config->empresa_id);

        $this->getOrCreateCartToken($request);
        $carrinho = $this->_getCarrinho($request);

        $link = $request->link;

        return view('qr_code_cardapio.index', compact(
            'config', 'categorias', 'produtosEmDestaque', 'carrinho', 'produto', 'link'
        ));
    }
}