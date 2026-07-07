<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MercadoLivreConfig;
use App\Models\Produto;
use App\Models\CategoriaMercadoLivre;
use App\Models\UnidadeMedida;
use App\Models\PadraoTributacaoProduto;
use App\Models\Empresa;
use App\Models\VariacaoMercadoLivre;
use Illuminate\Support\Facades\DB;
use App\Models\CategoriaProduto;
use App\Utils\EstoqueUtil;
use App\Utils\MercadoLivreUtil;
use App\Utils\UploadUtil;
use App\Models\ProdutoLocalizacao;

class MercadoLivreProdutoController extends Controller
{
    protected $util;
    protected $utilMercadoLivre;
    protected $uploadUtil;

    public function __construct(Request $request, EstoqueUtil $util, MercadoLivreUtil $utilMercadoLivre, UploadUtil $uploadUtil)
    {
        $this->util = $util;
        $this->utilMercadoLivre = $utilMercadoLivre;
        $this->uploadUtil = $uploadUtil;
    }

    private function requestMl($method, $url, $token, $data = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $res = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($curlError) {
            return (object)[
                'error' => true,
                'message' => $curlError,
                'http_code' => $httpCode
            ];
        }

        $retorno = json_decode($res);

        if (!$retorno) {
            return (object)[
                'error' => true,
                'message' => 'Resposta inválida do Mercado Livre',
                'raw' => $res,
                'http_code' => $httpCode
            ];
        }

        $retorno->http_code = $httpCode;

        return $retorno;
    }

    private function __validaToken()
    {
        $retorno = $this->utilMercadoLivre->refreshToken(request()->empresa_id);

        if ($retorno == 'token valido!') {
            return 1;
        }

        if ($retorno == 0) {
            session()->flash("flash_error", "Configure a integração com Mercado Livre.");
            return 0;
        }

        if (!isset($retorno->access_token)) {
            $msg = $retorno->message 
                ?? $retorno->error_description 
                ?? $retorno->error 
                ?? 'Erro ao renovar token do Mercado Livre. Reconecte a conta.';

            session()->flash("flash_error", $msg);
            return 0;
        }

        return 1;
    }

    public function index(Request $request)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $this->validaCategorias();

        $data = Produto::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return $q->where('nome', 'LIKE', "%$request->nome%");
            })
            ->where('mercado_livre_id', '!=', null)
            ->paginate(__itensPagina());

        return view('mercado_livre_produtos.index', compact('data'));
    }

    private function validaCategorias()
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return false;
        }

        $categorias = CategoriaMercadoLivre::count();

        if ($categorias == 0) {
            $config = MercadoLivreConfig::where('empresa_id', request()->empresa_id)->first();

            if (!$config) {
                return false;
            }

            $retorno = $this->requestMl(
                'GET',
                "https://api.mercadolibre.com/sites/MLB/categories/all",
                $config->access_token
            );

            if (isset($retorno->error) || !is_array($retorno)) {
                session()->flash("flash_error", $retorno->message ?? 'Erro ao buscar categorias do Mercado Livre.');
                return false;
            }

            foreach ($retorno as $r) {
                CategoriaMercadoLivre::updateOrCreate(
                    ['_id' => $r->id],
                    ['nome' => $r->name]
                );
            }
        }

        return true;
    }

    public function edit($id)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $item = Produto::findOrFail($id);

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', request()->empresa_id)->first();

        if (!$configMercadoLivre) {
            session()->flash("flash_error", "Configuração do Mercado Livre não encontrada.");
            return redirect()->route('mercado-livre-config.index');
        }

        $prodML = $this->requestMl(
            'GET',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id",
            $configMercadoLivre->access_token
        );

        if (isset($prodML->error) || isset($prodML->message) && !isset($prodML->id)) {
            session()->flash("flash_error", $prodML->message ?? 'Erro ao buscar produto no Mercado Livre.');
            return redirect()->back();
        }

        $descricaoML = $this->requestMl(
            'GET',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id/description",
            $configMercadoLivre->access_token
        );

        return view('mercado_livre_produtos.edit', compact('item', 'prodML', 'descricaoML'));
    }

    public function update(Request $request, $id)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $item = Produto::findOrFail($id);

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', $request->empresa_id)->first();

        if (!$configMercadoLivre) {
            session()->flash("flash_error", "Configuração do Mercado Livre não encontrada.");
            return redirect()->back();
        }

        $prod = $this->requestMl(
            'GET',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id",
            $configMercadoLivre->access_token
        );

        if (!isset($prod->id)) {
            session()->flash("flash_error", $prod->message ?? 'Erro ao consultar produto no Mercado Livre.');
            return redirect()->back();
        }

        $dataMercadoLivre = [
            'title' => $item->nome,
            'currency_id' => 'BRL',
            'video_id' => $request->mercado_livre_youtube,
        ];

        $variations = $prod->variations ?? [];

        if (count($variations) > 0 && $request->variacao_id) {
            for ($i = 0; $i < sizeof($request->variacao_id); $i++) {
                $dataMercadoLivre['variations'][$i]['price'] = __convert_value_bd($request->variacao_valor[$i]);
                $dataMercadoLivre['variations'][$i]['available_quantity'] = __convert_value_bd($request->variacao_quantidade[$i]);
                $dataMercadoLivre['variations'][$i]['id'] = $request->variacao_id[$i];
            }
        } else {
            $dataMercadoLivre['price'] = __convert_value_bd($request->mercado_livre_valor);
        }

        $retorno = $this->requestMl(
            'PUT',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id",
            $configMercadoLivre->access_token,
            $dataMercadoLivre
        );

        if (($retorno->http_code ?? null) >= 400 || isset($retorno->error)) {
            $msg = $this->trataErros($retorno);
            session()->flash("flash_error", $msg);
            return redirect()->back();
        }

        if ($request->mercado_livre_descricao) {
            $retornoDesc = $this->requestMl(
                'POST',
                "https://api.mercadolibre.com/items/$item->mercado_livre_id/description",
                $configMercadoLivre->access_token,
                ['plain_text' => $request->mercado_livre_descricao]
            );

            if (($retornoDesc->http_code ?? null) >= 400 || isset($retornoDesc->error)) {
                session()->flash("flash_error", $retornoDesc->message ?? 'Produto atualizado, mas erro ao atualizar descrição.');
                return redirect()->back();
            }
        }

        if (count($variations) > 0 && $request->variacao_id) {
            for ($i = 0; $i < sizeof($request->variacao_id); $i++) {
                $variacao = VariacaoMercadoLivre::where('_id', $request->variacao_id[$i])->first();

                if ($variacao) {
                    $variacao->valor = __convert_value_bd($request->variacao_valor[$i]);
                    $variacao->quantidade = __convert_value_bd($request->variacao_quantidade[$i]);
                    $variacao->valor_nome = $request->variacao_valor_nome[$i];
                    $variacao->nome = $request->variacao_nome[$i];
                    $variacao->save();
                }
            }
        }

        session()->flash("flash_success", "Produto atualizado!");
        return redirect()->route('mercado-livre-produtos.index');
    }

    private function trataErros($retorno)
    {
        $msg = "";

        if (isset($retorno->cause) && is_array($retorno->cause)) {
            foreach ($retorno->cause as $c) {
                $msg .= ($c->message ?? '') . " ";
            }
        }

        if (!$msg) {
            $msg = $retorno->message 
                ?? $retorno->error_description 
                ?? $retorno->error 
                ?? 'Erro na comunicação com Mercado Livre.';
        }

        return trim($msg);
    }

    public function produtosNew(Request $request)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $config = MercadoLivreConfig::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            session()->flash("flash_error", "Configuração do Mercado Livre não encontrada.");
            return redirect()->route('mercado-livre-config.index');
        }

        $retorno = $this->requestMl(
            'GET',
            "https://api.mercadolibre.com/users/$config->user_id/items/search/?offset=0",
            $config->access_token
        );

        if (!isset($retorno->results)) {
            session()->flash("flash_error", $retorno->message ?? 'Erro ao buscar anúncios do Mercado Livre.');
            return redirect()->route('mercado-livre-config.index');
        }

        $results = $retorno->results;
        $produtosIsert = [];

        foreach ($results as $rcode) {
            $retornoProduto = $this->requestMl(
                'GET',
                "https://api.mercadolibre.com/items/$rcode",
                $config->access_token
            );

            if (!isset($retornoProduto->id)) {
                continue;
            }

            $res = $this->validaProdutoCadastrado($retornoProduto, $request->empresa_id);

            if (is_array($res)) {
                $produtosIsert[] = $res;
            }
        }

        if (sizeof($produtosIsert) > 0) {
            $empresa = Empresa::findOrFail(request()->empresa_id);

            $listaCTSCSOSN = Produto::listaCSOSN();

            if ($empresa->tributacao == 'Regime Normal') {
                $listaCTSCSOSN = Produto::listaCST();
            }

            $padraoTributacao = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)
                ->where('padrao', 1)
                ->first();

            $padroes = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->get();

            $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)
                ->where('status', 1)
                ->where('categoria_id', null)
                ->get();

            $unidades = UnidadeMedida::where('empresa_id', request()->empresa_id)
                ->where('status', 1)
                ->get();

            return view('mercado_livre_produtos.create_produtos',
                compact('produtosIsert', 'padraoTributacao', 'listaCTSCSOSN', 'padroes', 'categorias', 'unidades')
            );
        }

        return redirect()->route('mercado-livre-produtos.index');
    }

    private function validaProdutoCadastrado($mlProduto, $empresa_id)
    {
        if (!isset($mlProduto->id)) {
            return true;
        }

        $produto = Produto::where('empresa_id', $empresa_id)
            ->where('mercado_livre_id', $mlProduto->id)
            ->first();

        if ($produto != null) {
            $this->atualizaProduto($mlProduto, $produto);
            return true;
        }

        $dataProduto = [
            'empresa_id' => $empresa_id,
            'nome' => $mlProduto->title ?? '',
            'valor_venda' => $mlProduto->price ?? 0,
            'mercado_livre_id' => $mlProduto->id,
            'mercado_livre_valor' => $mlProduto->price ?? 0,
            'mercado_livre_link' => $mlProduto->permalink ?? '',
            'estoque' => $mlProduto->available_quantity ?? 0,
            'status' => $mlProduto->status ?? '',
            'mercado_livre_categoria' => $mlProduto->category_id ?? ''
        ];

        $variations = $mlProduto->variations ?? [];

        if (count($variations) > 0) {
            $variacoes = [];

            foreach ($variations as $v) {
                $combination = $v->attribute_combinations[0] ?? null;

                $dataVariacao = [
                    '_id' => $v->id ?? null,
                    'quantidade' => $v->available_quantity ?? 0,
                    'valor' => $v->price ?? 0,
                    'nome' => $combination->name ?? '',
                    'valor_nome' => $combination->value_name ?? ''
                ];

                array_push($variacoes, $dataVariacao);
            }

            $dataProduto['variacoes'] = $variacoes;
        }

        return $dataProduto;
    }

    private function atualizaProduto($mlProduto, $produto)
    {
        $produto->mercado_livre_status = $mlProduto->status ?? $produto->mercado_livre_status;
        $produto->mercado_livre_valor = $mlProduto->price ?? $produto->mercado_livre_valor;
        $produto->nome = $mlProduto->title ?? $produto->nome;
        $produto->save();
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $contInserts = 0;

            try {
                for ($i = 0; $i < sizeof($request->mercado_livre_id); $i++) {
                    $last = Produto::where('empresa_id', $request->empresa_id)
                        ->orderBy('numero_sequencial', 'desc')
                        ->where('numero_sequencial', '>', 0)
                        ->first();

                    $numeroSequencial = $last != null ? $last->numero_sequencial : 0;
                    $numeroSequencial++;

                    $data = [
                        'numero_sequencial' => $numeroSequencial,
                        'mercado_livre_id' => $request->mercado_livre_id[$i],
                        'nome' => $request->nome[$i],
                        'valor_unitario' => __convert_value_bd($request->valor_venda[$i]),
                        'mercado_livre_valor' => __convert_value_bd($request->mercado_livre_valor[$i]),
                        'valor_compra' => $request->valor_compra[$i] ? __convert_value_bd($request->valor_compra[$i]) : 0,
                        'codigo_barras' => $request->codigo_barras[$i],
                        'ncm' => $request->ncm[$i],
                        'unidade' => $request->unidade[$i],
                        'gerenciar_estoque' => $request->gerenciar_estoque[$i],
                        'categoria_id' => $request->categoria_id[$i],
                        'cest' => $request->cest[$i],
                        'cfop_estadual' => $request->cfop_estadual[$i],
                        'cfop_outro_estado' => $request->cfop_outro_estado[$i],
                        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
                        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
                        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
                        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),
                        'perc_red_bc' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'cEnq' => $request->cEnq[$i],
                        'empresa_id' => $request->empresa_id,
                        'mercado_livre_status' => $request->mercado_livre_status[$i],
                        'mercado_livre_categoria' => $request->mercado_livre_categoria[$i],
                        'valor_prazo' => 0
                    ];

                    $produto = Produto::create($data);

                    ProdutoLocalizacao::updateOrCreate([
                        'produto_id' => $produto->id,
                        'localizacao_id' => $request->local_id
                    ]);

                    if ($request->mercado_livre_id_row) {
                        for ($j = 0; $j < sizeof($request->mercado_livre_id_row); $j++) {
                            if ($request->mercado_livre_id[$i] == $request->mercado_livre_id_row[$j]) {
                                $dataVariacao = [
                                    'produto_id' => $produto->id,
                                    '_id' => $request->variacao_id[$j],
                                    'quantidade' => __convert_value_bd($request->variacao_quantidade[$j]),
                                    'valor' => __convert_value_bd($request->variacao_valor[$j]),
                                    'nome' => $request->variacao_nome[$j],
                                    'valor_nome' => $request->variacao_valor_nome[$j]
                                ];

                                VariacaoMercadoLivre::create($dataVariacao);
                            }
                        }
                    }

                    if ($request->estoque[$i]) {
                        $this->util->incrementaEstoque($produto->id, $request->estoque[$i], null);
                    }

                    $contInserts++;
                }

                session()->flash("flash_success", "Total de produtos inseridos: $contInserts");
            } catch (\Exception $e) {
                session()->flash("flash_error", $e->getMessage());
            }
        });

        return redirect()->route('mercado-livre-produtos.index');
    }

    public function galery($id)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $item = Produto::findOrFail($id);

        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)->first();

        if (!$config) {
            session()->flash("flash_error", "Configuração do Mercado Livre não encontrada.");
            return redirect()->back();
        }

        $retorno = $this->requestMl(
            'GET',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id",
            $config->access_token
        );

        if (!isset($retorno->id)) {
            session()->flash("flash_error", $retorno->message ?? 'Erro ao buscar galeria do Mercado Livre.');
            return redirect()->back();
        }

        return view('mercado_livre_produtos.galery', compact('item', 'retorno'));
    }

    public function galeryStore(Request $request)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $item = Produto::findOrFail($request->produto_id);

        if ($request->hasFile('image')) {
            $file_name = $this->uploadUtil->uploadImage($request, '/temp-ml');
        } else {
            session()->flash("flash_error", "Selecione uma imagem!");
            return redirect()->back();
        }

        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)->first();

        if (!$config) {
            session()->flash("flash_error", "Configuração do Mercado Livre não encontrada.");
            return redirect()->back();
        }

        $urlImage = rtrim($config->url, '/') . "/uploads/temp-ml/$file_name";

        $dataMercadoLivre = [];
        $cont = 0;

        foreach (($request->picture ?? []) as $picture) {
            $dataMercadoLivre['pictures'][$cont]['source'] = $picture;
            $cont++;
        }

        $dataMercadoLivre['pictures'][$cont]['source'] = $urlImage;

        $retorno = $this->requestMl(
            'PUT',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id",
            $config->access_token,
            $dataMercadoLivre
        );

        if (isset($retorno->id)) {
            $files = glob('uploads/temp-ml/*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            session()->flash("flash_success", "Imagem adicionada!");
        } else {
            session()->flash("flash_error", $retorno->message ?? 'Erro ao adicionar imagem.');
        }

        return redirect()->back();
    }

    public function galeryDelete(Request $request)
    {
        $token = $this->__validaToken();

        if ($token == 0) {
            return redirect()->route('mercado-livre-config.index');
        }

        $item = Produto::findOrFail($request->produto_id);

        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)->first();

        if (!$config) {
            session()->flash("flash_error", "Configuração do Mercado Livre não encontrada.");
            return redirect()->back();
        }

        $dataMercadoLivre = [];

        $cont = 0;

        foreach (($request->picture ?? []) as $picture) {
            $dataMercadoLivre['pictures'][$cont]['source'] = $picture;
            $cont++;
        }

        $retorno = $this->requestMl(
            'PUT',
            "https://api.mercadolibre.com/items/$item->mercado_livre_id",
            $config->access_token,
            $dataMercadoLivre
        );

        if (isset($retorno->id)) {
            session()->flash("flash_success", "Imagem removida!");
        } else {
            session()->flash("flash_error", $retorno->message ?? 'Erro ao remover imagem.');
        }

        return redirect()->back();
    }
}