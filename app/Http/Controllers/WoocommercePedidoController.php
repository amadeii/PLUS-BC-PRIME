<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\WoocommerceUtil;
use App\Models\WoocommercePedido;
use App\Models\Cidade;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\ProdutoLocalizacao;
use App\Models\Nfe;

class WoocommercePedidoController extends Controller
{
    protected $util;
    protected $endpoint = 'orders';

    public function __construct(WoocommerceUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $woocommerceClient = $this->util->getConfig($request->empresa_id);

        try {
            $data = $woocommerceClient->get($this->endpoint);
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage() . ", revise as credenciais!");
            return redirect()->route('woocommerce-config.index');
        }

        foreach ($data as $pedido) {
            $this->util->criaPedido($request->empresa_id, $pedido);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_nome = $request->get('cliente_nome');

        $data = WoocommercePedido::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('data', '<=', $end_date);
        })
        ->when(!empty($cliente_nome), function ($query) use ($cliente_nome) {
            return $query->where('nome', 'LIKE', "%$cliente_nome%");
        })
        ->orderBy('pedido_id', 'desc')
        ->paginate(30);

        return view('woocommerce_pedidos.index', compact('data'));
    }

    public function show($id)
    {
        $item = WoocommercePedido::findOrFail($id);
        return view('woocommerce_pedidos.show', compact('item'));
    }

    public function gerarNfe($id)
    {
        $item = WoocommercePedido::findOrFail($id);
        $woocommerceClient = $this->util->getConfig(request()->empresa_id);

        try {
            $pedidoWoo = $woocommerceClient->get('orders/' . $item->pedido_id);
        } catch (\Exception $e) {
            session()->flash("flash_error", "Erro ao buscar pedido no WooCommerce: " . $e->getMessage());
            return redirect()->back();
        }

        $caixa = __isCaixaAberto();

        foreach ($item->itens as $i) {

            if ($i->produto_id) {
                continue;
            }

            $itemWoo = null;

            foreach ($pedidoWoo->line_items as $line) {

                if ((int)$i->item_id === (int)$line->id) {
                    $itemWoo = $line;
                    break;
                }

                if (!$itemWoo && trim((string)$i->item_nome) === trim((string)$line->name)) {
                    $itemWoo = $line;
                }
            }

            if (!$itemWoo) {
                session()->flash("flash_error", "Não foi possível localizar o item '{$i->item_nome}' no pedido do WooCommerce.");
                return redirect()->back();
            }

            try {
                $produto = $this->criarOuVincularProdutoWoo($woocommerceClient, $itemWoo, request()->empresa_id, $caixa->local_id);

                $i->produto_id = $produto->id;
                $i->save();

            } catch (\Exception $e) {
                session()->flash("flash_error", "Erro ao importar produto do WooCommerce: " . $e->getMessage());
                return redirect()->back();
            }
        }

        if (!$item->cliente) {
            session()->flash("flash_error", "Cliente não cadastrado no sistema");
            return redirect()->back();
        }

        $cliente = $item->cliente;

        $cidades = Cidade::all();
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();

        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if ($naturezas->count() == 0) {
            session()->flash("flash_warning", "Primeiro cadastre uma natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }

        $empresa = Empresa::findOrFail(request()->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $caixa->local_id);
        $numeroNfe = Nfe::lastNumero($empresa);

        $item->cliente_id = $cliente->id;
        $isPedidoWoocommerce = 1;

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->get();

        return view('nfe.create', compact(
            'item',
            'cidades',
            'transportadoras',
            'naturezas',
            'isPedidoWoocommerce',
            'numeroNfe',
            'caixa',
            'funcionarios'
        ));
    }

    private function criarOuVincularProdutoWoo($woocommerceClient, $itemWoo, $empresaId, $localId)
    {
        $produtoWooId = ((int)($itemWoo->variation_id ?? 0) > 0)
        ? (int)$itemWoo->variation_id
        : (int)$itemWoo->product_id;

        $produto = Produto::where('empresa_id', $empresaId)
        ->where('woocommerce_id', $produtoWooId)
        ->first();

        if ($produto) {
            ProdutoLocalizacao::updateOrCreate([
                'produto_id' => $produto->id,
                'localizacao_id' => $localId
            ]);

            return $produto;
        }

        if ((int)($itemWoo->variation_id ?? 0) > 0) {
            $produtoWoo = $woocommerceClient->get(
                'products/' . $itemWoo->product_id . '/variations/' . $itemWoo->variation_id
            );

            $produtoPai = $woocommerceClient->get('products/' . $itemWoo->product_id);

            $nome = $itemWoo->name ?? ($produtoPai->name ?? 'Produto sem nome');
            $slug = $produtoPai->slug ?? null;
            $link = $produtoPai->permalink ?? null;
            $type = $produtoPai->type ?? 'variation';
            $status = $produtoPai->status ?? 'publish';
            $descricao = $produtoPai->description ?? null;
            $categorias = isset($produtoPai->categories) ? json_encode($produtoPai->categories) : null;
        } else {
            $produtoWoo = $woocommerceClient->get('products/' . $itemWoo->product_id);

            $nome = $produtoWoo->name ?? 'Produto sem nome';
            $slug = $produtoWoo->slug ?? null;
            $link = $produtoWoo->permalink ?? null;
            $type = $produtoWoo->type ?? 'simple';
            $status = $produtoWoo->status ?? 'publish';
            $descricao = $produtoWoo->description ?? null;
            $categorias = isset($produtoWoo->categories) ? json_encode($produtoWoo->categories) : null;
        }

        $numeroSequencial = ((int) Produto::where('empresa_id', $empresaId)
            ->where('numero_sequencial', '>', 0)
            ->max('numero_sequencial')) + 1;

        $preco = (float)($produtoWoo->price ?? $itemWoo->price ?? 0);

        $data = [
            'woocommerce_id' => $produtoWooId,
            'nome' => $nome,
            'numero_sequencial' => $numeroSequencial,
            'valor_unitario' => $preco,
            'woocommerce_valor' => $preco,
            'valor_compra' => 0,
            'codigo_barras' => $produtoWoo->sku ?? null,
            'ncm' => '',
            'unidade' => 'UN',
            'gerenciar_estoque' => 0,
            'categoria_id' => null,
            'cest' => '',
            'cfop_estadual' => '5102',
            'cfop_outro_estado' => '6102',
            'perc_icms' => 0,
            'perc_pis' => 0,
            'perc_cofins' => 0,
            'perc_ipi' => 0,
            'perc_red_bc' => 0,
            'cst_csosn' => '102',
            'cst_pis' => '49',
            'cst_cofins' => '49',
            'cst_ipi' => '99',
            'cEnq' => '',
            'empresa_id' => $empresaId,
            'woocommerce_slug' => $slug,
            'woocommerce_link' => $link,
            'woocommerce_type' => $type,
            'woocommerce_status' => $status,
            'woocommerce_descricao' => $descricao,
            'woocommerce_stock_status' => $produtoWoo->stock_status ?? null,
            'categorias_woocommerce' => $categorias,
            'valor_prazo' => 0
        ];

        $produto = Produto::create($data);

        ProdutoLocalizacao::updateOrCreate([
            'produto_id' => $produto->id,
            'localizacao_id' => $localId
        ]);

        return $produto;
    }
}