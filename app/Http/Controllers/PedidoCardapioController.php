<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\ItemPedidoServico;
use App\Models\Empresa;
use App\Models\Nfce;
use App\Models\ConfigGeral;
use App\Models\Produto;
use App\Models\SitefConfig;
use App\Models\ScopeConfig;
use App\Models\ImpressoraPedido;
use App\Models\CarrinhoCardapio;
use App\Models\ItemAdicional;
use App\Models\ItemPizzaPedido;
use App\Models\Adicional;
use App\Models\Marca;
use App\Models\CategoriaProduto;
use App\Models\ConfiguracaoCardapio;
use App\Models\TamanhoPizza;
use App\Models\Caixa;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use App\Models\ImpressoraPedidoProduto;
use App\Models\Mesa;
use App\Models\UsuarioEmissao;
use App\Utils\PrintUtil;
use App\Models\ItemNfce;
use App\Models\FaturaNfce;
use App\Models\PedidoFinalizacaoParcial;
use App\Models\PedidoFinalizacaoParcialItem;

class PedidoCardapioController extends Controller
{

    protected $printUtil;
    public function __construct(PrintUtil $printUtil){
        $this->printUtil = $printUtil;
    }

    public function index(Request $request){

        $data = Pedido::query()
        ->where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->where('local_pedido', '!=', 'BALANCA')
        ->orderBy('created_at', 'desc')
        ->get();

        $balanca = Pedido::query()
        ->where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->where('local_pedido', 'BALANCA')
        ->orderBy('created_at', 'desc')
        ->get();

        $mesas = Mesa::where('status', 1)
        ->where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        return view('pedidos.index', compact(
            'data',
            'balanca',
            'mesas'
        ));
    }
    public function store(Request $request){
        $cliente_id = $request->cliente_id;
        $comanda = $request->comanda;
        $mesa = $request->mesa;
        $clienteNome = $request->cliente_nome;
        $clienteFone = $request->cliente_fone;
        $item = Pedido::where('status', 1)
        ->where('empresa_id', $request->empresa_id)
        ->where('comanda', $comanda)
        ->first();

        if($item != null){
            session()->flash("flash_error", 'Comanda já está aberta');
            return redirect()->back();
        }

        try{
            $data = [
                'cliente_id' => $cliente_id,
                'cliente_nome' => $clienteNome,
                'cliente_fone' => $clienteFone,
                'comanda' => $comanda,
                'mesa_id' => $request->mesa_id,
                'total' => 0,
                'empresa_id' => $request->empresa_id,
                'local_pedido' => 'PDV'
            ];

            Mesa::where('id', $request->mesa_id)->update(['ocupada' => 1]);

            Pedido::create($data);
            session()->flash("flash_success", "Comanda aberta!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function show($id){
        $item = Pedido::with([
            'itens.produto',
            'itensServico.servico',
            'finalizacoesParciais.itens.itemPedido.produto',
            'finalizacoesParciais.nfce'
        ])->findOrFail($id);

        $tamanhosPizza = TamanhoPizza::where('empresa_id', request()->empresa_id)
        ->get();

        $config = ConfiguracaoCardapio::where('empresa_id', request()->empresa_id)
        ->first();

        $configGeral = ConfigGeral::where('empresa_id', request()->empresa_id)
        ->first();
        if($config && $config->percentual_taxa_servico){
            $item->acrescimo = $item->total * ($config->percentual_taxa_servico/100);
            $item->save();
        }

        $clientes = [];
        $push = [];

        $totalClientes = ItemPedido::where('pedido_id', $id)
        ->select('nome_cardapio')->distinct('nome_cardapio')->count();

        if($totalClientes > 1){
            $valorPorCliente = 0;
            if($item->acrescimo > 0){
                $valorPorCliente = (float)number_format($item->acrescimo/$totalClientes,2);
            }

            foreach($item->itens as $i){
                if($i->nome_cardapio && !$i->finalizado_pdv){
                    if(!in_array($i->nome_cardapio, $push)){
                        $push[] = $i->nome_cardapio;
                        $clientes[$i->nome_cardapio] = (float)$i->sub_total;
                    }else{
                        $clientes[$i->nome_cardapio] += $i->sub_total;
                    }
                }
            }
            if($valorPorCliente > 0){
                foreach($clientes as $key => $c){
                    $clientes[$key] += $valorPorCliente;
                }
            }
            // dd($clientes);
        }

        $tiposPagamento = Nfce::tiposPagamento();
        // dd($tiposPagamento);
        if($config != null){
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if(sizeof($config->tipos_pagamento_pdv) > 0){
                foreach($tiposPagamento as $key => $t){
                    if(in_array($t, $config->tipos_pagamento_pdv)){
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        $mesas = Mesa::where('status', 1)
        ->where('empresa_id', request()->empresa_id)
        ->orderBy('nome')
        ->get();

        return view('pedidos.show', compact('item', 'tamanhosPizza', 'config', 'configGeral', 'clientes', 'mesas', 'tiposPagamento'));
    }

    public function updateTable(Request $request, $id){
        $pedido = Pedido::findOrfail($id);
        $pedido->mesa_id = $request->mesa_id;

        if($request->comanda){
            $outroPedido = Pedido::where('empresa_id', $request->empresa_id)
            ->where('comanda', $request->comanda)->where('status', 1)->first();

            if($outroPedido && $outroPedido->id != $id){
                session()->flash("flash_warning", "Essa comanda já está aberta");
                return redirect()->back();
            }
            $pedido->comanda = $request->comanda;
        }

        $pedido->save();

        session()->flash("flash_success", "Mesa/comanda alterada!");
        return redirect()->back();
    }

    public function storeServico(Request $request, $id){
        try {
            DB::transaction(function () use ($request, $id) {
                $pedido = Pedido::findOrfail($id);

                $data = [
                    'pedido_id' => $id,
                    'servico_id' => $request->servico_id,
                    'observacao' => $request->observacao,
                    'quantidade' => __convert_value_bd($request->quantidade),
                    'valor_unitario' => __convert_value_bd($request->valor_unitario),
                    'sub_total' => __convert_value_bd($request->sub_total),
                ];
                $itemPedido = ItemPedidoServico::create($data);
                $pedido->sumTotal();

            });
            session()->flash("flash_success", "Serviço adicionado!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }

        return redirect()->back();
    }

    private function validaItemImpressao($produto_id){

        $imprime = ImpressoraPedidoProduto::where('produto_id', $produto_id)->first();
        return $imprime != null ? 0 : 1;
    }

    public function storeItem(Request $request, $id){
        try {
            DB::transaction(function () use ($request, $id) {

                $adicionais = $request->adicionais;
                $adicionais = explode(",", $adicionais);

                $pedido = Pedido::findOrfail($id);
                $impresso = $this->validaItemImpressao($request->produto_cardapio);
                $data = [
                    'pedido_id' => $id,
                    'produto_id' => $request->produto_cardapio,
                    'observacao' => $request->observacao,
                    'quantidade' => __convert_value_bd($request->quantidade),
                    'valor_unitario' => __convert_value_bd($request->valor_unitario),
                    'sub_total' => __convert_value_bd($request->sub_total),
                    'estado' => $request->estado,
                    'ponto_carne' => $request->ponto_carne,
                    'tamanho_id' => $request->tamanho_id,
                    'impresso' => $impresso
                ];
                $itemPedido = ItemPedido::create($data);

                $produto = Produto::findOrFail($request->produto_cardapio);
                if($produto != null){
                    if($produto->categoria && $produto->categoria->tipo_pizza){
                        $pizzas = explode(",", $request->pizzas);
                        foreach($pizzas as $pz){
                            ItemPizzaPedido::create([
                                'item_pedido_id' => $itemPedido->id,
                                'produto_id' => $pz
                            ]);
                        }
                    }
                }
                foreach($adicionais as $a){
                    if($a){
                        $adicional = Adicional::findOrFail($a);
                        $dataItemAdicional = [
                            'item_pedido_id' => $itemPedido->id,
                            'adicional_id' => $adicional->id,
                        ];
                        ItemAdicional::create($dataItemAdicional);

                    }
                }

                $pedido->sumTotal();

            });
            session()->flash("flash_success", "Produto adicionado!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }

        return redirect()->back();

    }

    public function delete(Request $request){
        $item = Pedido::findOrFail($request->comanda_id);
        $carrinho = CarrinhoCardapio::where('session_cart_cardapio', $item->session_cart_cardapio)->first();
        if($carrinho){
            foreach($carrinho->itens as $it){
                $it->adicionais()->delete();
                $it->pizzas()->delete();
            }
            $carrinho->itens()->delete();
            $carrinho->delete();
        }

        if($item->_mesa){
            $mesa = $item->_mesa;
            $mesa->ocupada = 0;
            $mesa->save();
        }
        try {
            foreach($item->itens as $it){
                $it->adicionais()->delete();
                $it->pizzas()->delete();
                $it->delete();
            }
            $item->notificacoes()->delete();
            $item->delete();

            session()->flash("flash_success", "Comanda removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado '. $e->getMessage());
        }

        if(isset(request()->redirect_mesas_pdv)){
            return redirect()->route('frontbox.mesas');
        }
        return redirect()->back();
    }

    public function destroy($id){
        $item = Pedido::findOrFail($id);
        $carrinho = CarrinhoCardapio::where('session_cart_cardapio', $item->session_cart_cardapio)->first();
        if($carrinho){
            foreach($carrinho->itens as $it){
                $it->adicionais()->delete();
                $it->pizzas()->delete();
            }
            $carrinho->itens()->delete();
            $carrinho->delete();
        }

        if($item->_mesa){
            $mesa = $item->_mesa;
            $mesa->ocupada = 0;
            $mesa->save();
        }
        try {
            foreach($item->itens as $it){
                $it->adicionais()->delete();
                $it->pizzas()->delete();
                $it->delete();
            }
            $item->notificacoes()->delete();
            $item->finalizacoesParciais()->delete();
            $item->delete();

            session()->flash("flash_success", "Comanda removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado '. $e->getMessage());
        }

        if(isset(request()->redirect_mesas_pdv)){
            return redirect()->route('frontbox.mesas');
        }
        return redirect()->back();
    }

    public function removerTodosBalanca()
    {
        try {
            $pedidos = Pedido::where('empresa_id', request()->empresa_id)
            ->where('local_pedido', 'BALANCA')
            ->where('status', 1)
            ->get();

            foreach ($pedidos as $pedido) {
                foreach($pedido->itens as $it){
                    $it->adicionais()->delete();
                    $it->pizzas()->delete();
                    $it->delete();
                }
                $pedido->delete();
            }

            session()->flash('flash_success', 'Pedidos da balança removidos com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao remover pedidos: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function destroyItem($id){
        $item = ItemPedido::findOrFail($id);
        try {
            $pedido = $item->pedido;
            $item->adicionais()->delete();
            $item->pizzas()->delete();
            $item->delete();
            $pedido->sumTotal();

            session()->flash("flash_success", "Item removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado '. $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroyItemServico($id){
        $item = ItemPedidoServico::findOrFail($id);
        try {
            $pedido = $item->pedido;
            $item->delete();
            $pedido->sumTotal();

            session()->flash("flash_success", "Serviço removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado '. $e->getMessage());
        }
        return redirect()->back();
    }

    public function print($id)
    {
        $item = Pedido::with([
            'itens.produto',
            'itens.adicionais',
            'itens.pizzas.sabor',
            'itens.tamanho',
            'itensServico.servico',
            '_mesa'
        ])->findOrFail($id);

        $config = Empresa::find($item->empresa_id);

        return view('pedidos.imprimir', compact('config', 'item'));
    }

    public function imprimirSocket($id){

        $item = Pedido::findOrFail($id);

        $impressoras = ImpressoraPedido::where('empresa_id', $item->empresa_id)
        ->where('status', 1)->where('printer', '!=', null)->get();

        foreach($impressoras as $imp){

            $produtosDaImpressora = $imp->produtos->pluck('produto_id')->toArray();
            $content = [
                ['type' => 'center', 'value' => 'IMPRESSAO DE PEDIDO'],
                ['type' => 'hr'],
                ['type' => 'bold', 'value' => 'COMANDA #'.$item->comanda],
            ];
            $imprimir = false;
            foreach ($item->itens as $i) {
                if(in_array($i->produto_id, $produtosDaImpressora)){
                    $imprimir = true;
                    $content[] = [
                        'type'  => 'left',
                        'value' => __removerAcentos($i->produto->nome)
                    ];

                    if(sizeof($i->pizzas) > 0){
                        $sabores = "";
                        foreach($i->pizzas as $s){
                            $sabores .= $s->sabor->nome . " | ";
                        }
                        $sabores = substr($sabores, 0, strlen($sabores)-2);
                        $content[] = [
                            'type'  => 'left',
                            'value' => $sabores
                        ];
                    }

                    if(sizeof($i->adicionais) > 0){
                        $add = 'Adicioanis: ' . $i->getAdicionaisStr();
                        $content[] = [
                            'type'  => 'left',
                            'value' => $add
                        ];
                    }

                    if(strlen($i->observacao) > 3){
                        $content[] = [
                            'type'  => 'left',
                            'value' => 'Observacao: ' . $i->observacao
                        ];
                    }

                    if($i->tamanho){
                        $content[] = [
                            'type'  => 'left',
                            'value' => 'Tamanho: ' . $i->tamanho->nome
                        ];
                    }

                    $content[] = [
                        'type'  => 'left',
                        'value' => 'R$ ' . __moeda($i->produto->valor_unitario) . ' | ' . number_format($i->quantidade, 0) . 'x = R$ ' .
                        __moeda($i->sub_total)
                    ];
                    $content[] = ['type' => 'hr'];
                }
            }
            $content[] = ['type' => 'cut'];

            try{
                if($imprimir){
                    $response = $this->printUtil->sendPrint(
                        agentUrl: 'http://127.0.0.1:9100',
                        token: 'SLYM_SECRET_123',
                        printer: $imp->printer,
                        content: $content
                    );
                }

            }catch(\Exception $e){
                echo "Erro: " . $e->getMessage();
            }
        }
    }

    public function printHtml($id){
        $item = Pedido::findOrFail($id);

        $height = 180;
        $height += $item->countItens()*25;
        $config = Empresa::where('id', $item->empresa_id)->first();

        return view('pedidos.imprimir_hmtl', compact('config', 'item'));

    }

    public function finish($id){
        $pedido = Pedido::findOrFail($id);

        if($pedido->status == 0){
            session()->flash("flash_warning", 'Pedido já esta finalizado');
            return redirect()->back();
        }

        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->where('status', 1)
        ->where('categoria_id', null)
        ->get();

        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('usuario_id', get_id_user())
        ->where('status', 1)
        ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if($config == null){
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if($config->natureza_id_pdv == null){
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

        $itens = $pedido->itens;
        foreach($itens as $i){
            $i->valor_unitario = $i->sub_total/$i->quantidade;
        }
        $caixa = __isCaixaAberto();

        $config = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $tiposPagamento = Nfce::tiposPagamento();
        if($config != null){
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if(sizeof($config->tipos_pagamento_pdv) > 0){
                foreach($tiposPagamento as $key => $t){
                    if(in_array($t, $config->tipos_pagamento_pdv)){
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        $isVendaSuspensa = 0;

        $view = 'front_box.create';
        $produtos = [];
        $marcas = [];
        $servicos = $pedido->itensServico;
        foreach($servicos as $s){
            $s->valor = $s->valor_unitario;
        }

        if($config != null && $config->modelo == 'compact'){
            $view = 'front_box.create2';
            $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)
            ->where('categoria_id', null)
            ->orderBy('nome', 'asc')
            ->where('status', 1)
            ->where('categoria_id', null)
            ->paginate(4);

            $marcas = Marca::where('empresa_id', request()->empresa_id)
            ->orderBy('nome', 'asc')
            ->paginate(4);

            $produtos = Produto::select('produtos.*', \DB::raw('sum(quantidade) as quantidade'))
            ->where('empresa_id', request()->empresa_id)
            ->where('produtos.status', 1)
            ->where('status', 1)
            ->leftJoin('item_nfces', 'item_nfces.produto_id', '=', 'produtos.id')
            ->groupBy('produtos.id')
            ->orderBy('quantidade', 'desc')
            ->paginate(12);
        }
        $local_id = $caixa->local_id;
        $acrescimo = $pedido->acrescimo;

        $configuracaoTef = SitefConfig::where('empresa_id', request()->empresa_id)
        ->where('usuario_id', get_id_user())
        ->where('habilitado', 1)
        ->first();

        $configuracaoTefScope = ScopeConfig::where('empresa_id', request()->empresa_id)
        ->where('usuario_id', get_id_user())
        ->where('habilitado', 1)
        ->first();

        if($configuracaoTef || $configuracaoTefScope){
            $tiposPagamento['00'] = 'TEF';
        }
        return view($view, compact('categorias', 'abertura', 'funcionarios', 'pedido', 'itens', 'caixa', 'config', 
            'tiposPagamento', 'isVendaSuspensa', 'produtos', 'marcas', 'servicos', 'local_id', 'acrescimo', 'configuracaoTef'));
    }

    public function liberarMesa($id){
        $item = Pedido::findOrfail($id);
        $item->confirma_mesa = 1;
        $item->save();
        session()->flash("flash_success", "Mesa liberada!");
        return redirect()->back();
    }

    public function finishClient(Request $request){

        $pedido = Pedido::findOrfail($request->pedido_id);

        if($pedido->status == 0){
            session()->flash("flash_warning", 'Pedido já esta finalizado');
            return redirect()->back();
        }

        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->where('status', 1)->get();

        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('usuario_id', get_id_user())
        ->where('status', 1)
        ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if($config == null){
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if($config->natureza_id_pdv == null){
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

        $itens = [];
        $pushItensPedido = [];

        $total = 0;
        foreach($pedido->itens as $i){
            if($i->nome_cardapio == $request->nome){
                $itens[] = $i;
                $pushItensPedido[] = $i->id;
                $total = $i->sub_total;
            }
        }

        foreach($itens as $i){
            $i->valor_unitario = $i->sub_total/$i->quantidade;
        }
        $caixa = __isCaixaAberto();

        $config = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $tiposPagamento = Nfce::tiposPagamento();
        if($config != null){
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if(sizeof($config->tipos_pagamento_pdv) > 0){
                foreach($tiposPagamento as $key => $t){
                    if(in_array($t, $config->tipos_pagamento_pdv)){
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        $isVendaSuspensa = 0;

        $view = 'front_box.create';
        $produtos = [];
        $marcas = [];
        $servicos = $pedido->itensServico;
        foreach($servicos as $s){
            $s->valor = $s->valor_unitario;
        }

        if($config != null && $config->modelo == 'compact'){
            $view = 'front_box.create2';
            $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)
            ->where('categoria_id', null)
            ->orderBy('nome', 'asc')
            ->where('status', 1)
            ->paginate(4);

            $marcas = Marca::where('empresa_id', request()->empresa_id)
            ->orderBy('nome', 'asc')
            ->paginate(4);

            $produtos = Produto::select('produtos.*', \DB::raw('sum(quantidade) as quantidade'))
            ->where('empresa_id', request()->empresa_id)
            ->where('produtos.status', 1)
            ->where('status', 1)
            ->leftJoin('item_nfces', 'item_nfces.produto_id', '=', 'produtos.id')
            ->groupBy('produtos.id')
            ->orderBy('quantidade', 'desc')
            ->paginate(12);
        }
        $local_id = $caixa->local_id;
        $acrescimo = $request->valor - $total;
        return view($view, compact('categorias', 'abertura', 'funcionarios', 'pedido', 'itens', 'caixa', 'config', 
            'tiposPagamento', 'isVendaSuspensa', 'produtos', 'marcas', 'servicos', 'local_id', 'acrescimo', 'pushItensPedido'));
    }

    public function historico(Request $request){
        $data = Pedido::
        where('empresa_id', $request->empresa_id)
        ->where('status', 0)
        ->when(!empty($request->comanda), function ($q) use ($request) {
            return $q->where('comanda', $request->comanda);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(__itensPagina());

        $mesas = Mesa::where('status', 1)
        ->where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        return view('pedidos.historico', compact('data', 'mesas'));
    }

    public function finalizarParcial(Request $request, $id)
    {
        $pedido = Pedido::with(['itens.produto'])->findOrFail($id);

        if($pedido->status == 0){
            return response()->json([
                'message' => 'Pedido já está finalizado.'
            ], 422);
        }

        if(!__isCaixaAberto()){
            return response()->json([
                'message' => 'Abra o caixa antes de continuar.'
            ], 422);
        }

        $tipoParcial = $request->tipo_parcial ?? 'valor';
        $pagamentos = json_decode($request->pagamentos, true) ?? [];
        $itensSelecionados = $request->itens_parciais ?? [];

        $valorParcial = __convert_value_bd($request->valor_parcial);

        $totalPedido = (float) ($pedido->total + $pedido->acrescimo);
        $totalPagoAnterior = PedidoFinalizacaoParcial::where('pedido_id', $pedido->id)->sum('valor_pago');
        $saldoAntes = $totalPedido - $totalPagoAnterior;

        if($valorParcial <= 0){
            return response()->json([
                'message' => 'Informe um valor parcial válido.'
            ], 422);
        }

        if(round($valorParcial, 2) > round($saldoAntes, 2)){
            return response()->json([
                'message' => 'O valor parcial não pode ser maior que o saldo restante.'
            ], 422);
        }

        if(sizeof($pagamentos) == 0){
            return response()->json([
                'message' => 'Adicione ao menos uma forma de pagamento.'
            ], 422);
        }

        $totalPagamentos = collect($pagamentos)->sum(fn($p) => (float) $p['valor']);

        if(round($totalPagamentos, 2) != round($valorParcial, 2)){
            return response()->json([
                'message' => 'O total das formas de pagamento precisa fechar com o valor parcial.'
            ], 422);
        }

        try{
            $result = DB::transaction(function () use ($request, $pedido, $valorParcial, $pagamentos, $saldoAntes, $tipoParcial, $itensSelecionados) {

                $empresa = Empresa::findOrFail($pedido->empresa_id);

                if($empresa->natureza_id_pdv == null){
                    throw new \Exception('Configure a natureza de operação padrão para continuar.');
                }

                $caixa = Caixa::where('empresa_id', $pedido->empresa_id)
                ->where('usuario_id', get_id_user())
                ->where('status', 1)
                ->first();

                if(!$caixa){
                    throw new \Exception('Caixa não encontrado.');
                }

                $itensParaNfce = $this->montarItensFinalizacaoParcial($pedido, $valorParcial, $tipoParcial, $itensSelecionados);

                if(sizeof($itensParaNfce) == 0){
                    throw new \Exception('Nenhum item disponível para gerar a finalização parcial.');
                }

                $totalItens = collect($itensParaNfce)->sum(fn($i) => (float) $i['sub_total']);

                if(round($totalItens, 2) != round($valorParcial, 2)){
                    throw new \Exception('O total dos itens não fecha com o valor parcial.');
                }

                $saldoDepois = round($saldoAntes - $valorParcial, 2);

                if($saldoDepois < 0){
                    $saldoDepois = 0;
                }
                $tipoPagamentoPrincipal = $pagamentos[0]['tipo'] ?? '01';

                $numeroSerieNfce = $empresa->numero_serie_nfce ? $empresa->numero_serie_nfce : 1;
                $configUsuarioEmissao = UsuarioEmissao::where('usuario_empresas.empresa_id', $pedido->empresa_id)
                ->join('usuario_empresas', 'usuario_empresas.usuario_id', '=', 'usuario_emissaos.usuario_id')
                ->select('usuario_emissaos.*')
                ->where('usuario_emissaos.usuario_id', get_id_user())
                ->first();

                $numero_nfce = $empresa->numero_ultima_nfce_producao;
                if ($empresa->ambiente == 2) {
                    $numero_nfce = $empresa->numero_ultima_nfce_homologacao;
                }

                if($configUsuarioEmissao != null){
                    $numeroSerieNfce = $configUsuarioEmissao->numero_serie_nfce;
                    $numero_nfce = $configUsuarioEmissao->numero_ultima_nfce;
                }

                $nfce = Nfce::create([
                    'empresa_id' => $pedido->empresa_id,
                    'emissor_nome' => $empresa->nome,
                    'emissor_cpf_cnpj' => $empresa->cpf_cnpj,
                    'numero_serie' => $numeroSerieNfce,
                    'numero' => $numero_nfce + 1,
                    'cliente_nome' => $pedido->cliente_nome,
                    'cliente_cpf_cnpj' => $request->cpf_nota ? preg_replace('/[^0-9]/', '', $request->cpf_nota) : null,
                    'estado' => 'novo',
                    'total' => $valorParcial,
                    'desconto' => 0,
                    'acrescimo' => 0,
                    'natureza_id' => $empresa->natureza_id_pdv,
                    'observacao' => $request->observacao,
                    'cliente_id' => $pedido->cliente_id,
                    'caixa_id' => $caixa->id,
                    'dinheiro_recebido' => $valorParcial,
                    'troco' => 0,
                    'tipo_pagamento' => $tipoPagamentoPrincipal,
                    'user_id' => get_id_user(),
                    'local_id' => $caixa->local_id,
                    'tipo' => 'pdv',
                    'numero_sequencial' => $this->getLastNumero($pedido->empresa_id)
                ]);

                $parcial = PedidoFinalizacaoParcial::create([
                    'empresa_id' => $pedido->empresa_id,
                    'pedido_id' => $pedido->id,
                    'nfce_id' => $nfce->id,
                    'valor_pago' => $valorParcial,
                    'saldo_antes' => $saldoAntes,
                    'saldo_depois' => $saldoDepois,
                    'cpf_nota' => $request->cpf_nota ? preg_replace('/[^0-9]/', '', $request->cpf_nota) : null,
                    'observacao' => $request->observacao,
                    'status' => 'salvo'
                ]);

                foreach($itensParaNfce as $item){
                    $itemPedido = $item['item_pedido'];
                    $produto = $itemPedido->produto;

                    ItemNfce::create([
                        'nfce_id' => $nfce->id,
                        'produto_id' => $itemPedido->produto_id,
                        'quantidade' => $item['quantidade'],
                        'valor_unitario' => $item['valor_unitario'],
                        'sub_total' => $item['sub_total'],
                        'perc_icms' => $produto->perc_icms ?? 0,
                        'perc_pis' => $produto->perc_pis ?? 0,
                        'perc_cofins' => $produto->perc_cofins ?? 0,
                        'perc_ipi' => $produto->perc_ipi ?? 0,
                        'cst_csosn' => $produto->cst_csosn ?? '102',
                        'cst_pis' => $produto->cst_pis ?? '99',
                        'cst_cofins' => $produto->cst_cofins ?? '99',
                        'cst_ipi' => $produto->cst_ipi ?? '99',
                        'perc_red_bc' => $produto->perc_red_bc ?? 0,
                        'cfop' => $produto->cfop_estadual ?? $produto->cfop ?? '5102',
                        'ncm' => $produto->ncm ?? '00000000',
                        'origem' => $produto->origem ?? 0,
                        'cEnq' => $produto->cEnq ?? null,
                        'pST' => $produto->pST ?? null,
                        'vBCSTRet' => $produto->vBCSTRet ?? null,
                        'cest' => $produto->cest ?? null,
                        'codigo_beneficio_fiscal' => $produto->codigo_beneficio_fiscal ?? null,
                        'variacao_id' => null,
                        'tamanho_id' => $itemPedido->tamanho_id,
                        'observacao' => $itemPedido->observacao
                    ]);

                    PedidoFinalizacaoParcialItem::create([
                        'pedido_finalizacao_parcial_id' => $parcial->id,
                        'pedido_id' => $pedido->id,
                        'item_pedido_id' => $itemPedido->id,
                        'produto_id' => $itemPedido->produto_id,
                        'quantidade' => $item['quantidade'],
                        'valor_unitario' => $item['valor_unitario'],
                        'sub_total' => $item['sub_total'],
                    ]);

                    if($item['finalizar_item']){
                        $itemPedido->update([
                            'finalizado_pdv' => 1
                        ]);
                    }
                }

                foreach($pagamentos as $pagamento){
                    FaturaNfce::create([
                        'nfce_id' => $nfce->id,
                        'tipo_pagamento' => $pagamento['tipo'],
                        'data_vencimento' => now()->format('Y-m-d'),
                        'valor' => (float) $pagamento['valor'],
                        'observacao' => $pagamento['tipo_texto'] ?? null
                    ]);
                }

                return [
                    'nfce' => $nfce,
                    'parcial' => $parcial
                ];
            });

return response()->json([
    'success' => true,
    'id' => $result['parcial']->id,
    'nfce_id' => $result['nfce']->id,
    'redirect_nfce' => route('nfce.edit', $result['nfce']->id)
]);

}catch(\Exception $e){
    return response()->json([
        'message' => $e->getMessage()
    ], 422);
}
}

private function getLastNumero($empresa_id){
    $last = Nfce::where('empresa_id', $empresa_id)
    ->orderBy('numero_sequencial', 'desc')
    ->where('numero_sequencial', '>', 0)->first();
    $numero = $last != null ? $last->numero_sequencial : 0;
    $numero++;
    return $numero;
}

private function montarItensFinalizacaoParcial($pedido, float $valorParcial, string $tipoParcial, array $itensSelecionados = [])
{
    if($tipoParcial == 'itens' && sizeof($itensSelecionados) > 0){
        return $this->montarItensSelecionadosParcial($pedido, $valorParcial, $itensSelecionados);
    }

    return $this->montarItensProporcionaisParcial($pedido, $valorParcial);
}

private function montarItensSelecionadosParcial($pedido, float $valorParcial, array $itensSelecionados)
{
    $itens = ItemPedido::with('produto')
    ->where('pedido_id', $pedido->id)
    ->whereIn('id', $itensSelecionados)
    ->where(function($q){
        $q->whereNull('finalizado_pdv')->orWhere('finalizado_pdv', 0);
    })
    ->get();

    if($itens->count() == 0){
        throw new \Exception('Nenhum item selecionado disponível para finalizar.');
    }

    $totalItens = $itens->sum('sub_total');

    if(round($totalItens, 2) != round($valorParcial, 2)){
        throw new \Exception('O valor parcial precisa ser igual ao total dos itens selecionados.');
    }

    $dados = [];

    foreach($itens as $item){
        if(!$item->produto){
            throw new \Exception('O item ' . $item->id . ' está sem produto vinculado.');
        }

        $dados[] = [
            'item_pedido' => $item,
            'quantidade' => (float) $item->quantidade,
            'valor_unitario' => (float) $item->valor_unitario,
            'sub_total' => (float) $item->sub_total,
            'finalizar_item' => true
        ];
    }

    return $dados;
}

private function montarItensProporcionaisParcial($pedido, float $valorParcial)
{
    $itens = ItemPedido::with('produto')
    ->where('pedido_id', $pedido->id)
    ->where(function($q){
        $q->whereNull('finalizado_pdv')->orWhere('finalizado_pdv', 0);
    })
    ->where('sub_total', '>', 0)
    ->orderBy('id')
    ->get();

    if($itens->count() == 0){
        throw new \Exception('Não existem itens disponíveis para montar a finalização parcial.');
    }

    $totalDisponivel = (float) $itens->sum('sub_total');

    if($valorParcial > $totalDisponivel){
        throw new \Exception('O valor parcial é maior que o total dos itens disponíveis.');
    }

    $dados = [];
    $restante = round($valorParcial, 2);
    $ultimoIndex = $itens->count() - 1;

    foreach($itens as $index => $item){
        if($restante <= 0){
            break;
        }

        if(!$item->produto){
            throw new \Exception('O item ' . $item->id . ' está sem produto vinculado.');
        }

        $subTotalItem = round((float) $item->sub_total, 2);

        if($index == $ultimoIndex){
            $valorUsado = $restante;
        }else{
            $proporcao = $subTotalItem / $totalDisponivel;
            $valorUsado = round($valorParcial * $proporcao, 2);

            if($valorUsado > $restante){
                $valorUsado = $restante;
            }

            if($valorUsado > $subTotalItem){
                $valorUsado = $subTotalItem;
            }
        }

        if($valorUsado <= 0){
            continue;
        }

        $quantidadeOriginal = (float) $item->quantidade;
        $valorUnitarioOriginal = (float) $item->valor_unitario;

        if($valorUnitarioOriginal <= 0){
            throw new \Exception('O item ' . $item->id . ' está com valor unitário inválido.');
        }

        $quantidadeProporcional = round($valorUsado / $valorUnitarioOriginal, 4);

        if($quantidadeProporcional > $quantidadeOriginal){
            $quantidadeProporcional = $quantidadeOriginal;
            $valorUsado = round($quantidadeProporcional * $valorUnitarioOriginal, 2);
        }

        $dados[] = [
            'item_pedido' => $item,
            'quantidade' => $quantidadeProporcional,
            'valor_unitario' => $valorUnitarioOriginal,
            'sub_total' => $valorUsado,
            'finalizar_item' => round($valorUsado, 2) >= round($subTotalItem, 2)
        ];

        $restante = round($restante - $valorUsado, 2);
    }

    if($restante != 0 && sizeof($dados) > 0){
        $ultimo = count($dados) - 1;
        $dados[$ultimo]['sub_total'] = round($dados[$ultimo]['sub_total'] + $restante, 2);
        $dados[$ultimo]['quantidade'] = round($dados[$ultimo]['sub_total'] / $dados[$ultimo]['valor_unitario'], 4);
    }

    return $dados;
}

}
