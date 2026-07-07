<?php

namespace App\Http\Controllers\Cardapio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracaoCardapio;
use App\Models\CarrinhoCardapio;
use App\Models\Mesa;
use App\Models\ItemCarrinhoCardapio;
use App\Models\NotificaoCardapio;
use App\Models\ItemPizzaCarrinhoCardapio;
use App\Models\ItemCarrinhoAdicionalCardapio;
use App\Models\Produto;
use App\Models\ItemAdicional;
use App\Models\ItemPizzaPedido;
use App\Models\ItemPedido;
use App\Models\CategoriaProduto;
use App\Models\ImpressoraPedidoProduto;
use App\Models\Pedido;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CarrinhoController extends Controller
{
    // Nomes dos cookies
    private string $cookieCart = 'cardapio_cart_token';
    private string $cookieUser = 'cardapio_user_token';
    private string $cookieNome = 'cardapio_nome';
    private string $cookieTel  = 'cardapio_tel';

    // Duração em minutos (30 dias)
    private int $cookieMinutes = 60 * 24 * 30;

    public function __construct(){
        // NÃO usar session_start() no Laravel
    }

    /**
     * Garante que existam tokens persistentes em cookie.
     * Retorna [cartToken, userToken].
     */
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

    private function getCarrinhoByCookies(Request $request): ?CarrinhoCardapio
    {
        [$cartToken, $userToken] = $this->getOrCreateTokens($request);

        return CarrinhoCardapio::where('session_cart_cardapio', $cartToken)
            ->where('session_cart_user', $userToken)
            ->first();
    }

    private function _atualizaValorCarrinho($carrinho_id){
        $item = CarrinhoCardapio::findOrfail($carrinho_id);
        $item->valor_total = $item->itens->sum('sub_total') + $item->valor_frete;
        $item->save();
    }

    public function index(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);

        $mesa = Mesa::where('empresa_id', $config->empresa_id)
            ->where('hash', $request->link)
            ->first();

        if(!$mesa){
            session()->flash("flash_error", "Mesa não encontrada!");
            return redirect()->back();
        }

        // garante tokens e pega carrinho por cookie
        $carrinho = $this->getCarrinhoByCookies($request);

        $session_nome_cardapio = $request->cookie($this->cookieNome); // opcional
        $session_telefone_cardapio = $request->cookie($this->cookieTel); // opcional

        $categorias = CategoriaProduto::where('cardapio', 1)
            ->orderBy('nome', 'asc')
            ->where('status', 1)
            ->where('empresa_id', $config->empresa_id)
            ->get();

        $notSearch = true;
        $pedido = null;

        if($carrinho && $session_nome_cardapio){
            $pedido = Pedido::where('empresa_id', $carrinho->empresa_id)
                ->where('status', 1)
                ->where('session_cart_cardapio', $carrinho->session_cart_cardapio)
                ->join('item_pedidos', 'item_pedidos.pedido_id', '=', 'pedidos.id')
                ->where('item_pedidos.nome_cardapio', $session_nome_cardapio)
                ->first();
        }

        $travaLimite = 0;
        if($config->limite_pessoas_qr_code && $carrinho){
            $contPedidos = Pedido::where('empresa_id', $carrinho->empresa_id)
                ->where('status', 1)
                ->where('session_cart_cardapio', $carrinho->session_cart_cardapio)
                ->count();

            if($contPedidos >= $config->limite_pessoas_qr_code){
                $travaLimite = 1;
            }
        }

        $notInfoHeader = 1;
        $link = $request->link;

        return view('qr_code_cardapio.carrinho', compact(
            'config', 'categorias', 'carrinho', 'notSearch', 'notInfoHeader', 'link',
            'pedido', 'travaLimite', 'mesa'
        ));
    }

    public function adicionar(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);
        $quantidade = (float)__convert_value_bd($request->quantidade);

        $produto_id = $request->produto_id ? $request->produto_id : ($request->pizza_id[0] ?? null);
        $produto = Produto::findOrFail($produto_id);

        if($produto->gerenciar_estoque){
            if(!$produto->estoque || $produto->estoque->quantidade < $quantidade){
                session()->flash("flash_error", "Estoque insuficiente!");
                return redirect()->back();
            }
        }

        try{
            $carrinho = DB::transaction(function () use ($request, $config, $quantidade, $produto_id) {

                [$cartToken, $userToken] = $this->getOrCreateTokens($request);

                $adicionarSaborUnico = 0;
                if(isset($request->tamanho_id)){
                    if(!isset($request->pizza_id)){
                        $adicionarSaborUnico = 1;
                    }
                }

                $carrinho = CarrinhoCardapio::where('session_cart_cardapio', $cartToken)
                    ->where('session_cart_user', $userToken)
                    ->first();

                if($carrinho == null){
                    $carrinho = CarrinhoCardapio::create([
                        'empresa_id' => $config->empresa_id,
                        'estado' => 'pendente',
                        'valor_total' => $request->sub_total,
                        'endereco_id' => null,
                        'valor_frete' => 0,
                        'session_cart_cardapio' => $cartToken,
                        'session_cart_user' => $userToken,
                    ]);
                }

                $itemCarrinho = ItemCarrinhoCardapio::create([
                    'carrinho_id' => $carrinho->id,
                    'produto_id' => $produto_id,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $request->sub_total / max(1, $quantidade),
                    'sub_total' => $request->sub_total,
                    'observacao' => $request->observacao ?? '',
                    'tamanho_id' => isset($request->tamanho_id) ? $request->tamanho_id : null
                ]);

                if($request->adicional){
                    for($i=0; $i<sizeof($request->adicional); $i++){
                        ItemCarrinhoAdicionalCardapio::create([
                            'item_carrinho_id' => $itemCarrinho->id,
                            'adicional_id' => $request->adicional[$i]
                        ]);
                    }
                }

                if(isset($request->pizza_id)){
                    for($i=0; $i<sizeof($request->pizza_id); $i++){
                        ItemPizzaCarrinhoCardapio::create([
                            'item_carrinho_id' => $itemCarrinho->id,
                            'produto_id' => $request->pizza_id[$i]
                        ]);
                    }
                }

                if($adicionarSaborUnico){
                    ItemPizzaCarrinhoCardapio::create([
                        'item_carrinho_id' => $itemCarrinho->id,
                        'produto_id' => $produto_id
                    ]);
                }

                session()->flash("flash_success", "Produto adicionado ao carrinho!");
                return $carrinho;
            });

        } catch(\Exception $e){
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }

        $this->_atualizaValorCarrinho($carrinho->id);

        // ajuste de redirect: passe parâmetros como array
        return redirect()->route('cardapio.carrinho', [
            'link' => $request->link,
            'config_id' => $request->config_id
        ]);
    }

    public function enviarPedido(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);

        $mesa = Mesa::where('empresa_id', $config->empresa_id)
            ->where('hash', $request->link)
            ->first();

        if(!$mesa){
            session()->flash("flash_error", "Mesa não encontrada!");
            return redirect()->back();
        }

        // pega tokens do cookie
        [$cartToken, $userToken] = $this->getOrCreateTokens($request);

        // valida telefone (se vier)
        if ($request->filled('telefone')) {
            $pedidoAberto = Pedido::where('empresa_id', $mesa->empresa_id)
                ->where('cliente_fone', $request->telefone)
                ->where('status', 1)
                ->first();

            if ($pedidoAberto && $pedidoAberto->mesa_id != $mesa->id) {
                session()->flash("flash_error", "Você já possui pedido em outra mesa!");
                return redirect()->back();
            }

            // grava em cookie (persistente)
            cookie()->queue($this->cookieTel, $request->telefone, $this->cookieMinutes);
        }

        if ($request->filled('nome')) {
            cookie()->queue($this->cookieNome, $request->nome, $this->cookieMinutes);
        }

        $carrinho = $this->getCarrinhoByCookies($request);

        if(!$carrinho){
            session()->flash("flash_error", "Carrinho não encontrado. Adicione itens antes de enviar!");
            return redirect()->back();
        }

        // Se mesa já está ocupada: reaproveita o token do último pedido da mesa
        if($mesa->ocupada == 1){
            $pedidoMesa = Pedido::where('empresa_id', $mesa->empresa_id)
                ->where('status', 1)
                ->where('mesa_id', $mesa->id)
                ->orderBy('id', 'desc')
                ->first();

            if($pedidoMesa){
                // troca token do carrinho para o da mesa
                $cartToken = $pedidoMesa->session_cart_cardapio;
                cookie()->queue($this->cookieCart, $cartToken, $this->cookieMinutes);

                $carrinho->session_cart_cardapio = $cartToken;
                $carrinho->save();
            }
        }

        $pedido = DB::transaction(function () use ($request, $config, $mesa, $userToken, $carrinho, $cartToken) {

            $pedido = Pedido::where('empresa_id', $carrinho->empresa_id)
                ->where('status', 1)
                ->where('session_cart_cardapio', $cartToken)
                ->first();

            $nome = $request->filled('nome') ? $request->nome : request()->cookie($this->cookieNome);
            $tel  = $request->filled('telefone') ? $request->telefone : request()->cookie($this->cookieTel);

            if($pedido == null){
                $pedido = Pedido::create([
                    'empresa_id' => $config->empresa_id,
                    'cliente_nome' => $nome,
                    'cliente_fone' => $tel,
                    'mesa_id' => $mesa->id,
                    'confirma_mesa' => $config->confirma_mesa,
                    'total' => $carrinho->valor_total,
                    'session_cart_cardapio' => $cartToken,
                    'session_cart_user' => $userToken,
                ]);

                $carrinho->cliente_nome = $nome ?? '';
                $carrinho->save();

                NotificaoCardapio::create([
                    'empresa_id' => $config->empresa_id,
                    'mesa' => $pedido->_mesa->nome,
                    'pedido_id' => $pedido->id,
                    'tipo' => 'fechar_mesa',
                    'observacao' => 'Abrindo mesa',
                    'avaliacao' => '',
                    'tipo_pagamento' => '',
                ]);

                $mesa->ocupada = 1;
                $mesa->save();

            } else {
                $pedido->total += $carrinho->valor_total;
                $pedido->save();
            }

            foreach($carrinho->itens as $i){
                $impresso = $this->validaItemImpressao($i->produto_id);

                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'produto_id' => $i->produto_id,
                    'observacao' => $i->observacao,
                    'estado' => 'novo',
                    'quantidade' => $i->quantidade,
                    'valor_unitario' => $i->valor_unitario,
                    'sub_total' => $i->sub_total,
                    'tamanho_id' => $i->tamanho_id,
                    'impresso' => $impresso,
                    'nome_cardapio' => $nome,
                    'telefone_cardapio' => $tel,
                ]);
            }

            // (mantive seu fluxo de apagar itens do carrinho)
            foreach($carrinho->itens as $it){
                $it->adicionais()->delete();
                $it->sabores()->delete();
                $it->delete();
            }

            $carrinho->valor_total = 0;
            $carrinho->save();

            return $pedido;
        });

        if(isset($pedido->id)){
            session()->flash("flash_success", "Pedido realizado!");
        }

        return redirect()->route('cardapio.index', [
            'link' => $request->link,
            'config_id' => $request->config_id
        ]);
    }

    private function validaItemImpressao($produto_id){
        $imprime = ImpressoraPedidoProduto::where('produto_id', $produto_id)->first();
        return $imprime != null ? 0 : 1;
    }

    public function pedirFechar(Request $request)
    {
        $config = ConfiguracaoCardapio::findOrFail($request->config_id);
        $carrinho = $this->getCarrinhoByCookies($request);

        if(!$carrinho){
            session()->flash("flash_error", "Carrinho não encontrado!");
            return redirect()->back();
        }

        $pedido = Pedido::where('empresa_id', $carrinho->empresa_id)
            ->where('status', 1)
            ->where('session_cart_cardapio', $carrinho->session_cart_cardapio)
            ->first();

        if($pedido){
            $pedido->em_atendimento = 0;
            $pedido->save();

            NotificaoCardapio::create([
                'empresa_id' => $config->empresa_id,
                'mesa' => $pedido->_mesa->nome,
                'pedido_id' => $pedido->id,
                'tipo' => 'fechar_mesa',
                'observacao' => $request->observacao,
                'avaliacao' => $request->avaliacao,
                'tipo_pagamento' => $request->tipo_pagamento,
            ]);
        }

        // limpa cookies (token + nome/tel)
        cookie()->queue(cookie()->forget($this->cookieCart));
        cookie()->queue(cookie()->forget($this->cookieUser));
        cookie()->queue(cookie()->forget($this->cookieNome));
        cookie()->queue(cookie()->forget($this->cookieTel));

        session()->flash("flash_success", "Mesa finalizada!");
        return redirect()->route('cardapio.index', [
            'link' => $request->link,
            'config_id' => $request->config_id
        ]);
    }
}