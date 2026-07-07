<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\NuvemShopUtil;
use App\Utils\EstoqueUtil;
use App\Models\Localizacao;
use App\Models\NuvemShopPedido;
use App\Models\NuvemShopConfig;
use App\Models\Funcionario;
use App\Models\NuvemShopItemPedido;
use App\Models\Caixa;
use App\Models\Cidade;
use App\Models\OrdemSeparacao;
use App\Models\ItemOrdemSeparacao;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\UsuarioEmpresa;
use App\Models\Empresa;
use App\Models\ItemNfe;
use App\Models\PadraoTributacaoProduto;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\Nfe;

class NuvemShopPedidoController extends Controller
{
    protected $util;
    protected $utilEstoque;

    public function __construct(NuvemShopUtil $util, EstoqueUtil $utilEstoque)
    {
        $this->util = $util;
        $this->utilEstoque = $utilEstoque;
    }

    public function index(Request $request)
    {
        $store_info = session('store_info');

        if (!$store_info) {
            return redirect()->route('nuvem-shop-auth.index');
        }

        $page = $request->page ? (int)$request->page : 1;
        $cliente = $request->cliente;
        $data_inicial = $request->start_date;
        $data_final = $request->end_date;

        $api = new \TiendaNube\API(
            $store_info['store_id'],
            $store_info['access_token'],
            'Awesome App (' . $store_info['email'] . ')'
        );

        $pedidos = [];

        try {
            $sql = "orders?page={$page}&per_page=12";

            if (!empty($cliente)) {
                $sql .= "&q=" . urlencode($cliente);
            }

            if (!empty($data_inicial)) {
                $sql .= "&created_at_min=" . \Carbon\Carbon::parse(str_replace("/", "-", $data_inicial))->format('Y-m-d');
            }

            if (!empty($data_final)) {
                $sql .= "&created_at_max=" . \Carbon\Carbon::parse(str_replace("/", "-", $data_final))->format('Y-m-d');
            }

            $response = (array)$api->get($sql);
            $pedidos = $response['body'] ?? [];

            if (!empty($pedidos)) {
                $this->storePedidos($pedidos, $request->empresa_id);
            }

        } catch (\Exception $e) {
            session()->flash("flash_warning", $e->getMessage());
        }

        return view('nuvem_shop_pedidos.index', compact('pedidos', 'page'));
    }

    public function storePedidos($pedidos, $empresa_id)
    {
        foreach ($pedidos as $p) {

            $pedido = NuvemShopPedido::where('empresa_id', $empresa_id)
            ->where('pedido_id', $p->id)
            ->first();

            $novoStatus = $p->payment_status ?? '';

            if ($pedido) {

                $statusAnterior = $pedido->status_pagamento;

                $this->atualizaCliente($p, $empresa_id);
                $this->atualizaPedido($p, $empresa_id);

                $pedido->refresh();

                $config = NuvemShopConfig::where('empresa_id', $empresa_id)->first();

                $jaTemVenda = !empty($pedido->venda_id) || !empty($pedido->nfe_id);

                if (
                    $config &&
                    $config->cron_para_separacao &&
                    $statusAnterior != 'paid' &&
                    $novoStatus == 'paid' &&
                    !$jaTemVenda
                ) {
                    try {
                        $this->criaOrdemSeparacao($pedido->id);
                    } catch (\Exception $e) {
                        \Log::error('Erro ao criar ordem de separação Nuvemshop após pagamento', [
                            'erro' => $e->getMessage(),
                            'linha' => $e->getLine(),
                            'pedido_id' => $pedido->id,
                            'pedido_nuvemshop_id' => $p->id ?? null,
                            'empresa_id' => $empresa_id,
                            'status_anterior' => $statusAnterior,
                            'novo_status' => $novoStatus,
                        ]);
                    }
                }

                continue;
            }

            $customer = $p->customer ?? null;
            $shipping = $p->shipping_address ?? null;
            $defaultAddress = $customer->default_address ?? null;

            $data = [
                'pedido_id' => $p->id,
                'rua' => $shipping->address ?? $p->billing_address ?? $defaultAddress->address ?? '',
                'numero' => $shipping->number ?? $p->billing_number ?? $defaultAddress->number ?? 0,
                'bairro' => $shipping->locality ?? $p->billing_locality ?? $defaultAddress->locality ?? '',
                'cidade' => $shipping->city ?? $p->billing_city ?? $defaultAddress->city ?? '',
                'cep' => $shipping->zipcode ?? $p->billing_zipcode ?? $defaultAddress->zipcode ?? '',

                'total' => isset($p->total) ? __convert_value_bd($p->total) : 0,
                'valor_frete' => isset($p->shipping_cost_customer) ? __convert_value_bd($p->shipping_cost_customer) : 0,
                'subtotal' => isset($p->subtotal) ? __convert_value_bd($p->subtotal) : 0,
                'desconto' => isset($p->discount) ? __convert_value_bd($p->discount) : 0,

                'cliente_id' => $customer->id ?? null,
                'observacao' => $p->shipping_option ?? $p->note ?? '',
                'nome' => $customer->name ?? $p->contact_name ?? $p->billing_name ?? 'Cliente Nuvemshop',

                'email' => $customer->email ?? $p->contact_email ?? '',
                'documento' => preg_replace('/\D/', '', $customer->identification ?? $p->contact_identification ?? ''),
                'empresa_id' => $empresa_id,

                'numero_nfe' => 0,
                'status_envio' => $p->shipping_status ?? '',
                'gateway' => $p->gateway ?? '',
                'status_pagamento' => $novoStatus,
                'data' => $p->created_at ?? now(),
                'log_pedido' => json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];

            $this->storeCliente($p, $empresa_id);

            $pedido = NuvemShopPedido::create($data);

            foreach (($p->products ?? []) as $prod) {

                $produto = $this->validaProduto($prod, $empresa_id);

                NuvemShopItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $prod->quantity ?? 0,
                    'valor_unitario' => $prod->price ?? 0,
                    'sub_total' => ($prod->quantity ?? 0) * ($prod->price ?? 0),
                    'nome' => $prod->name ?? '',
                ]);
            }

            $config = NuvemShopConfig::where('empresa_id', $empresa_id)->first();

            if ($config && $config->cron_para_separacao && $novoStatus == 'paid' && empty($pedido->venda_id) && empty($pedido->nfe_id)) {
                try {
                    $this->criaOrdemSeparacao($pedido->id);
                } catch (\Exception $e) {
                    \Log::error('Erro ao criar ordem de separação Nuvemshop', [
                        'erro' => $e->getMessage(),
                        'linha' => $e->getLine(),
                        'pedido_id' => $pedido->id,
                        'pedido_nuvemshop_id' => $p->id ?? null,
                        'empresa_id' => $empresa_id,
                    ]);
                }
            }
        }
    }

    private function criaOrdemSeparacao($pedido_id)
    {
        $pedido = NuvemShopPedido::findOrFail($pedido_id);

        if ($pedido->venda_id || $pedido->nfe_id) {
            return;
        }

        $natureza = NaturezaOperacao::where('empresa_id', $pedido->empresa_id)->first();
        $localizacao = Localizacao::where('empresa_id', $pedido->empresa_id)->first();
        $config = Empresa::findOrFail($pedido->empresa_id);
        $usuarioEmpresa = UsuarioEmpresa::where('empresa_id', $pedido->empresa_id)->first();
        $funcionario = Funcionario::where('empresa_id', $pedido->empresa_id)->first();
        $caixa = Caixa::where('empresa_id', $pedido->empresa_id)->where('status', 1)->first();
        $cliente = Cliente::where('empresa_id', $pedido->empresa_id)->where('nuvem_shop_id', $pedido->cliente_id)->first();

        if (!$cliente) {
            throw new \Exception('Cliente não encontrado para o pedido NuvemShop ' . $pedido->pedido_id);
        }

        if (!$natureza) {
            throw new \Exception('Natureza de operação não encontrada para empresa ' . $pedido->empresa_id);
        }

        if (!$localizacao) {
            throw new \Exception('Localização não encontrada para empresa ' . $pedido->empresa_id);
        }

        if (!$usuarioEmpresa) {
            throw new \Exception('Usuário da empresa não encontrado para empresa ' . $pedido->empresa_id);
        }

        if (!$funcionario) {
            throw new \Exception('Funcionário não encontrado para empresa ' . $pedido->empresa_id);
        }

        $nfe = Nfe::create([
            'empresa_id' => $pedido->empresa_id,
            'cliente_id' => $cliente->id,
            'total' => $pedido->total,
            'valor_produtos' => $pedido->subtotal ?? 0,
            'valor_frete' => $pedido->valor_frete ?? 0,
            'desconto' => $pedido->desconto ?? 0,
            'estado' => 'novo',
            'observacao' => $pedido->observacao,
            'natureza_id' => $natureza->id,
            'local_id' => $localizacao->id,
            'emissor_nome' => $config->nome,
            'emissor_cpf_cnpj' => $config->cpf_cnpj,
            'ambiente' => $config->ambiente,
            'numero_serie' => $config->numero_serie_nfe ? $config->numero_serie_nfe : 0,
            'numero_sequencial' => $this->getLastNumeroNfe($pedido->empresa_id),
            'caixa_id' => $caixa ? $caixa->id : null,
            'user_id' => $usuarioEmpresa->usuario_id,
            'tpNF' => 1,
            'orcamento' => 1
        ]);

        foreach ($pedido->itens as $it) {

            $product = Produto::findOrFail($it->produto_id);
            $cfop = $product->cfop_estadual;

            if ($cliente->cidade && $config->cidade && $cliente->cidade->uf != $config->cidade->uf) {
                $cfop = $product->cfop_outro_estado;
            }

            ItemNfe::create([
                'nfe_id' => $nfe->id,
                'produto_id' => $product->id,
                'quantidade' => $it->quantidade,
                'valor_unitario' => $it->valor_unitario,
                'sub_total' => $it->sub_total,
                'perc_icms' => $product->perc_icms,
                'perc_pis' => $product->perc_pis,
                'perc_cofins' => $product->perc_cofins,
                'perc_ipi' => $product->perc_ipi,
                'cst_csosn' => $product->cst_csosn,
                'cst_pis' => $product->cst_pis,
                'cst_cofins' => $product->cst_cofins,
                'cst_ipi' => $product->cst_ipi,
                'perc_red_bc' => $product->perc_red_bc,
                'cfop' => $cfop,
                'ncm' => $product->ncm,
                'codigo_beneficio_fiscal' => $product->codigo_beneficio_fiscal,
                'variacao_id' => null,
            ]);
        }

        $nfe->refresh();

        $ordemSeparacao = OrdemSeparacao::create([
            'nfe_id' => $nfe->id,
            'cliente_id' => $nfe->cliente_id,
            'numero_sequencial' => $this->getLastNumeroOrdemSeparacao($nfe->empresa_id),
            'status' => 'em_separacao',
            'funcionario_id' => $funcionario->id,
            'empresa_id' => $nfe->empresa_id,
            'observacao' => $nfe->observacao ?? '',
            'prioridade' => 'normal',
            'usuario_id_inicia' => $nfe->user_id
        ]);

        foreach ($nfe->itens as $i) {
            ItemOrdemSeparacao::create([
                'ordem_id' => $ordemSeparacao->id,
                'produto_id' => $i->produto_id,
                'quantidade' => $i->quantidade,
                'status' => 'pendente',
                'observacao_item' => ''
            ]);
        }

        $pedido->update([
            'venda_id' => $nfe->id,
            'nfe_id' => $nfe->id
        ]);
    }

    private function getLastNumeroOrdemSeparacao($empresa_id)
    {
        $last = OrdemSeparacao::where('empresa_id', $empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)
        ->first();

        return $last ? $last->numero_sequencial + 1 : 1;
    }

    private function getLastNumeroNfe($empresa_id)
    {
        $last = Nfe::where('empresa_id', $empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)
        ->first();

        return $last ? $last->numero_sequencial + 1 : 1;
    }

    private function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;

        for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    private function storeCliente($pedido, $empresa_id)
    {
        $customer = $pedido->customer ?? null;
        $shipping = $pedido->shipping_address ?? null;
        $defaultAddress = $customer->default_address ?? null;

        $nome = $customer->name ?? $pedido->contact_name ?? $pedido->billing_name ?? ($shipping->name ?? null) ?? 'Cliente Nuvemshop';
        $email = $customer->email ?? $pedido->contact_email ?? '';
        $doc = $customer->identification ?? $pedido->contact_identification ?? '';
        $doc = $this->documentoValido($doc);

        $telefone = $customer->phone ?? ($shipping->phone ?? null) ?? $pedido->billing_phone ?? $pedido->contact_phone ?? '';
        $telefone = preg_replace('/\D/', '', $telefone);

        if (substr($telefone, 0, 2) == '55') {
            $telefone = substr($telefone, 2);
        }

        $rua = $shipping->address ?? $pedido->billing_address ?? $defaultAddress->address ?? '';
        $numero = $shipping->number ?? $pedido->billing_number ?? $defaultAddress->number ?? '';
        $bairro = $shipping->locality ?? $pedido->billing_locality ?? $defaultAddress->locality ?? '';
        $cep = $shipping->zipcode ?? $pedido->billing_zipcode ?? $defaultAddress->zipcode ?? '';
        $cidadeNome = $shipping->city ?? $pedido->billing_city ?? $defaultAddress->city ?? '';

        $cidade = null;

        if (!empty($cidadeNome)) {
            $cidade = Cidade::where('nome', $cidadeNome)->first();

            if (!$cidade) {
                $cidade = Cidade::whereRaw('LOWER(nome) = ?', [mb_strtolower($cidadeNome)])->first();
            }
        }

        $client_id = $customer->id ?? null;
        $cliente = null;

        if ($client_id) {
            $cliente = Cliente::where('empresa_id', $empresa_id)
            ->where('nuvem_shop_id', $client_id)
            ->first();
        }

        if (!$cliente && $doc) {
            $docNumerico = preg_replace('/\D/', '', $doc);

            $cliente = Cliente::where('empresa_id', $empresa_id)
            ->where(function ($q) use ($doc, $docNumerico) {
                $q->where('cpf_cnpj', $doc)
                ->orWhereRaw("REGEXP_REPLACE(cpf_cnpj, '[^0-9]', '') = ?", [$docNumerico]);
            })
            ->first();
        }

        if (!$cliente && $email) {
            $cliente = Cliente::where('empresa_id', $empresa_id)
            ->where('email', $email)
            ->first();
        }

        $dataCliente = [
            'razao_social' => $nome,
            'nome_fantasia' => $nome,
            'bairro' => $bairro,
            'numero' => $numero,
            'rua' => $rua,
            'telefone' => $telefone,
            'celular' => $telefone,
            'email' => $email,
            'cep' => preg_replace('/\D/', '', $cep),
            'ie_rg' => '',
            'empresa_id' => $empresa_id,
            'nuvem_shop_id' => $client_id,
            'cidade_id' => $cidade?->id,
            'consumidor_final' => 1,
            'contribuinte' => 0,
        ];

        if (!empty($doc)) {
            $dataCliente['cpf_cnpj'] = $doc;
        }

        if ($cliente) {
            $cliente->update($dataCliente);
            return $cliente;
        }

        if (empty($doc)) {
            $dataCliente['cpf_cnpj'] = '';
        }

        return Cliente::create($dataCliente);
    }

    private function documentoValido($doc)
    {
        $doc = preg_replace('/\D/', '', $doc ?? '');

        if (!in_array(strlen($doc), [11, 14])) {
            return '';
        }

        if (preg_match('/^(\d)\1+$/', $doc)) {
            return '';
        }

        if (strlen($doc) == 11) {
            return $this->mask($doc, '###.###.###-##');
        }

        return $this->mask($doc, '##.###.###/####-##');
    }

    private function validaProduto($prod, $empresa_id)
    {
        $produto = Produto::where('nuvem_shop_id', $prod->product_id)->first();

        if ($produto != null) {
            return $produto;
        }

        $tributacao = PadraoTributacaoProduto::where('empresa_id', $empresa_id)
        ->where('padrao', 1)
        ->first();

        if ($tributacao == null) {
            $tributacao = PadraoTributacaoProduto::where('empresa_id', $empresa_id)->first();
        }

        $last = Produto::where('empresa_id', $empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)
        ->first();

        $numeroSequencial = $last != null ? $last->numero_sequencial + 1 : 1;

        return Produto::create([
            'nome' => $prod->name,
            'valor_unitario' => $prod->price,
            'valor_compra' => 0,
            'numero_sequencial' => $numeroSequencial,
            'ncm' => $tributacao ? $tributacao->ncm : '',
            'cst_csosn' => $tributacao ? $tributacao->cst_csosn : '',
            'cst_pis' => $tributacao ? $tributacao->cst_pis : '',
            'cst_cofins' => $tributacao ? $tributacao->cst_cofins : '',
            'cst_ipi' => $tributacao ? $tributacao->cst_ipi : '',
            'perc_red_bc' => $tributacao ? $tributacao->perc_red_bc : '',
            'cEnq' => $tributacao ? $tributacao->cEnq : '999',
            'pST' => $tributacao ? $tributacao->pST : '',
            'cfop_estadual' => $tributacao ? $tributacao->cfop_estadual : '',
            'cfop_outro_estado' => $tributacao ? $tributacao->cfop_outro_estado : '',
            'cest' => $tributacao ? $tributacao->cest : '',
            'codigo_beneficio_fiscal' => $tributacao ? $tributacao->codigo_beneficio_fiscal : '',
            'cfop_entrada_estadual' => $tributacao ? $tributacao->cfop_entrada_estadual : '',
            'cfop_entrada_outro_estado' => $tributacao ? $tributacao->cfop_entrada_outro_estado : '',
            'codigo_barras' => 'SEM GTIN',
            'largura' => $prod->width,
            'comprimento' => $prod->depth,
            'altura' => $prod->height,
            'peso' => $prod->weight,
            'empresa_id' => $empresa_id,
            'nuvem_shop_id' => $prod->product_id,
            'valor_prazo' => 0
        ]);
    }

    private function atualizaCliente($pedido, $empresa_id)
    {
        $customer = $pedido->customer ?? null;

        if (!$customer) {
            return null;
        }

        $cliente = Cliente::where('empresa_id', $empresa_id)
        ->where('nuvem_shop_id', $customer->id)
        ->first();

        if (!$cliente) {
            return $this->storeCliente($pedido, $empresa_id);
        }

        try {
            $doc = $customer->identification ?? $pedido->contact_identification ?? '';
            $doc = $this->documentoValido($doc);

            $cliente->razao_social = $customer->name ?? $pedido->contact_name ?? $cliente->razao_social;
            $cliente->nome_fantasia = $customer->name ?? $pedido->contact_name ?? $cliente->nome_fantasia;

            if (!empty($doc)) {
                $cliente->cpf_cnpj = $doc;
            }

            if (isset($pedido->shipping_address)) {
                $address = $pedido->shipping_address;

                $telefone = $address->phone ?? $customer->billing_phone ?? $customer->phone ?? $pedido->contact_phone ?? '';
                $telefone = preg_replace('/\D/', '', $telefone);

                if (substr($telefone, 0, 2) == '55') {
                    $telefone = substr($telefone, 2);
                }

                $cidade = null;

                if (!empty($address->city)) {
                    $cidade = Cidade::where('nome', $address->city)->first();

                    if (!$cidade) {
                        $cidade = Cidade::whereRaw('LOWER(nome) = ?', [mb_strtolower($address->city)])->first();
                    }
                }

                $cliente->telefone = $telefone;
                $cliente->celular = $telefone;
                $cliente->cep = preg_replace('/\D/', '', $address->zipcode ?? '');
                $cliente->bairro = $address->locality ?? '';
                $cliente->numero = $address->number ?? '';
                $cliente->rua = $address->address ?? '';
                $cliente->cidade_id = $cidade ? $cidade->id : $cliente->cidade_id;
            }

            $cliente->save();

            return $cliente;

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar cliente Nuvemshop', [
                'empresa_id' => $empresa_id,
                'customer_id' => $customer->id ?? null,
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
            ]);

            return null;
        }
    }

    private function atualizaPedido($p, $empresa_id)
    {
        $pedido = NuvemShopPedido::where('empresa_id', $empresa_id)
        ->where('pedido_id', $p->id)
        ->first();

        if (!$pedido) {
            return;
        }

        $pedido->valor_frete = isset($p->shipping_cost_customer) ? __convert_value_bd($p->shipping_cost_customer) : $pedido->valor_frete;
        $pedido->desconto = isset($p->discount) ? __convert_value_bd($p->discount) : $pedido->desconto;
        $pedido->subtotal = isset($p->subtotal) ? __convert_value_bd($p->subtotal) : $pedido->subtotal;
        $pedido->total = isset($p->total) ? __convert_value_bd($p->total) : $pedido->total;
        $pedido->subtotal = $p->subtotal ?? $pedido->subtotal;
        $pedido->desconto = $p->discount ?? $pedido->desconto;
        $pedido->status_envio = $p->shipping_status ?? $pedido->status_envio;
        $pedido->status_pagamento = $p->payment_status ?? $pedido->status_pagamento;
        $pedido->gateway = $p->gateway ?? $pedido->gateway;
        $pedido->log_pedido = json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $pedido->save();
    }

    public function show($id)
    {
        $item = NuvemShopPedido::where('pedido_id', $id)->first();
        return view('nuvem_shop_pedidos.show', compact('item'));
    }

    public function gerarNfe($id)
    {
        $item = NuvemShopPedido::findOrFail($id);

        if (!$item->cliente) {
            session()->flash("flash_error", "Cliente não cadastrado no sistema");
            return redirect()->back();
        }

        $cliente = $item->cliente;
        $cidades = Cidade::all();
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();

        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();

        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }

        $empresa = Empresa::findOrFail(request()->empresa_id);

        $caixa = __isCaixaAberto();
        $empresa = __objetoParaEmissao($empresa, $caixa->local_id);
        $numeroNfe = Nfe::lastNumero($empresa);

        $item->cliente_id = $cliente->id;

        $isPedidoNuvemShop = 1;

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->get();

        $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)
        ->where('padrao', 1)
        ->first();

        return view('nfe.create', compact('item', 'cidades', 'transportadoras', 'naturezas', 'isPedidoNuvemShop', 'numeroNfe', 'caixa', 'funcionarios', 'naturezaPadrao'));
    }

    public function destroy($id)
    {
        $item = NuvemShopPedido::where('pedido_id', $id)->first();

        try {
            $item->itens()->delete();
            $item->delete();

            session()->flash("flash_success", "Pedido removido!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->back();
    }
}