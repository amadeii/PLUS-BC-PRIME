<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoComposicao;
use App\Models\OrdemProducao;
use App\Models\ItemOrdemProducao;
use App\Models\Nfe;
use App\Models\ItemNfe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Dompdf\Dompdf;

class ProgramacaoProducaoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id;

        if ($request->has('recalcular')) {
            session([
                'programacao_producao_ultimo_recalculo' => now()->toDateTimeString(),
                'programacao_producao_incluir_semi' => $request->boolean('incluir_semi')
            ]);
        }

        $incluirSemi = session('programacao_producao_incluir_semi', true);

        $ultimoRecalculo = session('programacao_producao_ultimo_recalculo');
        $ultimoRecalculo = $ultimoRecalculo ? \Carbon\Carbon::parse($ultimoRecalculo) : null;

        $produtos = $this->produtosSugestao($request, $empresaId, $incluirSemi);
        $pedidos = $this->pedidosProducao($request, $empresaId);
        $materiais = $this->necessidadeMateriais($produtos, $empresaId);
        $ordens = $this->ordensFabricacao($empresaId);

        return view('programacao_producao.index', compact(
            'produtos',
            'pedidos',
            'materiais',
            'ordens',
            'ultimoRecalculo',
            'incluirSemi'
        ));
    }

    public function atualizar(Request $request)
    {
        return redirect()
        ->route('programacao-producao.index', $request->all())
        ->with('success', 'Programação atualizada com sucesso!');
    }

    private function produtosSugestao(Request $request, $empresaId, bool $incluirSemi = true)
    {
        $pedidos = $this->pedidosProducao($request, $empresaId)
        ->filter(function ($pedido) {
            // return $pedido['status_producao'] == 'Pendente';
        });

        $produtos = collect();

        foreach ($pedidos as $pedido) {
            foreach ($pedido['itens'] as $item) {

                $produto = Produto::with('categoria')->find($item['produto_id']);

                if (!$produto) {
                    continue;
                }

                $produtos->push(
                    $this->montarProdutoProgramado(
                        $produto,
                        (float) $item['quantidade'],
                        $empresaId,
                        $pedido
                    )
                );

                if ($incluirSemi) {

                    $visitados = [];

                    $this->adicionarSemiElaboradosSugestao(
                        $produto->id,
                        (float) $item['quantidade'],
                        $empresaId,
                        $produtos,
                        $visitados,
                        $pedido
                    );
                }
            }
        }

        return $produtos
        ->filter()
        ->groupBy('produto_id')
        ->map(function ($grupo) {
            $primeiro = $grupo->first();

            $demanda = $grupo->sum('demanda');
            $estoque = (float) ($primeiro['estoque'] ?? 0);
            $emProducao = (float) ($primeiro['em_producao'] ?? 0);

            $sugestao = max($demanda - $estoque - $emProducao, 0);

            $primeiro['demanda'] = $demanda;
            $primeiro['sugestao'] = $sugestao;
            $primeiro['qtd_produzir'] = $sugestao;
            $primeiro['status'] = $sugestao > 0 ? 'Produzir' : 'OK';

            return $primeiro;
        })
        ->values();
    }

    private function montarProdutoProgramado($produto, float $demanda, $empresaId, $pedido = null): array
    {
        $estoque = $this->estoqueProduto($produto);

        $emProducao = ItemOrdemProducao::where('produto_id', $produto->id)
        ->whereHas('ordemProducao', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId)
            ->whereIn('estado', ['novo', 'producao', 'expedicao']);
        })
        ->sum('quantidade');

        $emProducao = (float) $emProducao;

        $sugestao = max($demanda - $estoque - $emProducao, 0);

        return [
            'produto_id' => $produto->id,
            'codigo' => $produto->numero_sequencial ?? $produto->id,
            'descricao' => $produto->nome,
            'tipo' => $produto->categoria->nome ?? $produto->tipo ?? 'Acabado',
            'estoque' => $estoque,
            'demanda' => $demanda,
            'em_producao' => $emProducao,
            'sugestao' => $sugestao,
            'qtd_produzir' => $sugestao,
            'status' => $sugestao > 0 ? 'Produzir' : 'OK',
            'cliente_id' => $pedido['cliente_id'] ?? null,
            'numero_pedido' => $pedido['pedido'] ?? null,
        ];
    }

    private function adicionarSemiElaboradosSugestao($produtoId, float $quantidade, $empresaId, &$produtos, array &$visitados = [], $pedido = null)
    {
        if (in_array($produtoId, $visitados)) {
            return;
        }

        $visitados[] = $produtoId;

        $composicoes = ProdutoComposicao::with('ingrediente.categoria')
        ->where('produto_id', $produtoId)
        ->get();

        foreach ($composicoes as $comp) {
            if (!$comp->ingrediente) {
                continue;
            }

            $ingrediente = $comp->ingrediente;

            $temComposicao = ProdutoComposicao::where('produto_id', $ingrediente->id)->exists();

            if (!$temComposicao) {
                continue;
            }

            $qtdNecessaria = (float) $comp->quantidade * $quantidade;

            $produtos->push(
                $this->montarProdutoProgramado(
                    $ingrediente,
                    $qtdNecessaria,
                    $empresaId,
                    $pedido
                )
            );

            $this->adicionarSemiElaboradosSugestao(
                $ingrediente->id,
                $qtdNecessaria,
                $empresaId,
                $produtos,
                $visitados,
                $pedido
            );
        }

        array_pop($visitados);
    }

    private function pedidosProducao(Request $request, $empresaId)
    {
        $dataInicial = now()->subDays(60)->startOfDay();

        return Nfe::query()
        ->where('empresa_id', $empresaId)
        ->where('tpNF', 1)
        ->where('orcamento', 0)
        ->where('created_at', '>=', $dataInicial)
        ->whereIn('estado', ['novo', 'aprovado'])
        ->where(function ($q) {
            $q->whereNull('chave')
            ->orWhere('chave', '');
        })
        ->whereHas('itens.produto', function ($q) {
            $q->where('tipo_producao', 1);
        })
        ->with([
            'cliente',
            'itens' => function ($q) {
                $q->whereHas('produto', function ($p) {
                    $p->where('tipo_producao', 1);
                });
            },
            'itens.produto.categoria'
        ])
        ->latest()
        ->limit(30)
        ->get()
        ->map(function ($pedido) {

            $produtoIds = $pedido->itens->pluck('produto_id')->toArray();
            $qtde = (float) $pedido->itens->sum('quantidade');
            $numeroPedido = $pedido->numero_sequencial ?? $pedido->id;

            $emProducao = ItemOrdemProducao::whereIn('produto_id', $produtoIds)
            ->where('numero_pedido', $numeroPedido)
            ->sum('quantidade');

            $produzido = ItemOrdemProducao::whereIn('produto_id', $produtoIds)
            ->where('numero_pedido', $numeroPedido)
            ->where('status', 1)
            ->sum('quantidade');

            $emProducao = (float) $emProducao;
            $produzido = (float) $produzido;

            $percentual = $qtde > 0 ? (($produzido / $qtde) * 100) : 0;

            $dataEntrega = $pedido->data_entrega ?? null;
            $atrasado = $dataEntrega && now()->startOfDay()->gt(\Carbon\Carbon::parse($dataEntrega)->startOfDay());

            if ($produzido >= $qtde && $qtde > 0) {
                $statusProducao = 'Finalizado';
            } elseif ($emProducao > 0) {
                $statusProducao = 'Andamento';
            } else {
                $statusProducao = 'Pendente';
            }

            $statusPrazo = $atrasado ? 'Atrasado' : 'Normal';

            return [
                'id' => $pedido->id,
                'pedido' => $numeroPedido,
                'cliente_id' => $pedido->cliente_id ?? null,
                'cliente' => $pedido->cliente->razao_social ?? $pedido->cliente->nome ?? '-',
                'qtde' => $qtde,
                'em_producao' => $emProducao,
                'produzido' => $produzido,
                'percentual' => min($percentual, 100),
                'data_emissao' => $pedido->created_at,
                'data_entrega' => $dataEntrega,
                'status_producao' => $statusProducao,
                'estado_fatura' => $pedido->estado_fatura,
                'status_prazo' => $statusPrazo,
                'itens' => $pedido->itens->map(function ($item) use ($pedido, $numeroPedido) {
                    return [
                        'pedido_id' => $pedido->id,
                        'numero_pedido' => $numeroPedido,
                        'cliente_id' => $pedido->cliente_id ?? null,
                        'produto_id' => $item->produto_id,
                        'produto' => $item->produto->nome ?? '-',
                        'categoria' => $item->produto->categoria->nome ?? '-',
                        'quantidade' => (float) $item->quantidade,
                    ];
                })->values(),
            ];
        });
    }

    private function necessidadeMateriais($produtos, $empresaId)
    {
        $materiais = [];

        foreach ($produtos as $produtoProgramado) {
            $produtoId = $produtoProgramado['produto_id'];

            $quantidade = (float) ($produtoProgramado['qtd_produzir'] ?? 0);

            if ($quantidade <= 0) {
                $quantidade = (float) ($produtoProgramado['demanda'] ?? 0);
            }

            if ($quantidade <= 0) {
                continue;
            }

            $this->explodirMateriais(
                $produtoId,
                $quantidade,
                $materiais
            );
        }

        return collect($materiais)
        // ->groupBy('produto_id')
        ->groupBy(function ($item) {
            return $item['produto_id'].'-'.$item['composicao_pai_id'];
        })
        ->map(function ($grupo) {
            $primeiro = $grupo->first();

            $necessidade = $grupo->sum('necessidade');
            $estoque = (float) ($primeiro['estoque'] ?? 0);
            $falta = max($necessidade - $estoque, 0);

            return [
                'produto_id' => $primeiro['produto_id'],
                'codigo' => $primeiro['codigo'],
                'descricao' => $primeiro['descricao'],
                'tipo' => $primeiro['tipo'],
                'necessidade' => $necessidade,
                'unidade' => $primeiro['unidade'],
                'estoque' => $estoque,
                'custo_medio' => $primeiro['custo_medio'],
                'falta' => $falta,
                'situacao' => $falta > 0 ? 'FALTA' : 'OK',

                'composicao_pai' => $primeiro['composicao_pai'] ?? '-',
                'composicao_pai_id' => $primeiro['composicao_pai_id'] ?? null,
            ];
        })
        ->values();
    }

    private function explodirMateriais($produtoId, $quantidade, array &$materiais, array &$visitados = [])
    {
        if (in_array($produtoId, $visitados)) {
            return;
        }

        $visitados[] = $produtoId;

        $produtoPai = Produto::find($produtoId);

        $composicoes = ProdutoComposicao::with('ingrediente.categoria')
        ->where('produto_id', $produtoId)
        ->get();

        foreach ($composicoes as $comp) {

            if (!$comp->ingrediente) {
                continue;
            }

            $ingrediente = $comp->ingrediente;

            $qtdNecessaria = (float) $comp->quantidade * (float) $quantidade;

            $materiais[] = [
                'produto_id' => $ingrediente->id,
                'codigo' => $ingrediente->numero_sequencial ?? $ingrediente->id,
                'descricao' => $ingrediente->nome,
                'tipo' => $ingrediente->categoria->nome ?? 'Material',

                'composicao_pai' => $comp->produto->nome ?? '-',
                'composicao_pai_id' => $comp->produto->numero_sequencial ?? null,

                'necessidade' => $qtdNecessaria,
                'unidade' => $ingrediente->unidade ?? 'un',
                'estoque' => $this->estoqueProduto($ingrediente),
                'custo_medio' => (float) ($ingrediente->valor_compra ?? 0),
            ];

            $temFilhos = ProdutoComposicao::where('produto_id', $ingrediente->id)->exists();

            if ($temFilhos) {
                $this->explodirMateriais(
                    $ingrediente->id,
                    $qtdNecessaria,
                    $materiais,
                    $visitados
                );
            }
        }

        array_pop($visitados);
    }

    private function ordensFabricacao($empresaId)
    {
        return OrdemProducao::with([
            'itens.produto'
        ])
        ->where('empresa_id', $empresaId)
        ->whereIn('estado', ['novo', 'producao', 'expedicao'])
        ->latest()
        ->limit(20)
        ->get()
        ->map(function ($ordem) {

            return [
                'id' => $ordem->id,
                'codigo' => $ordem->codigo_sequencial,
                'estado' => $ordem->estado,
                'data_abertura' => $ordem->created_at,

                'itens' => $ordem->itens->map(function ($item) {

                    return [
                        'produto' => $item->produto->nome ?? '-',
                        'produto_id' => $item->produto_id,

                        'pedido_pai' => $item->numero_pedido,

                        'quantidade_programada' => (float) $item->quantidade,

                        'status' => $item->status == 1
                        ? 'Finalizado'
                        : 'Produção',
                    ];

                })->values()
            ];
        });
    }

    private function criarOrdemProgramacao($empresaId, $observacao = null)
    {
        return OrdemProducao::create([
            'empresa_id' => $empresaId,
            'funcionario_id' => null,
            'usuario_id' => auth()->id(),
            'observacao' => $observacao ?? 'OF gerada pela Programação de Produção',
            'estado' => 'novo',
            'data_prevista_entrega' => now()->addDays(7),
            'codigo_sequencial' => $this->proximoCodigoOf($empresaId),
            'hash_link' => Str::random(30),
        ]);
    }

    private function gerarOfSemiElaboradosSeparado($empresaId, $produtoId, $quantidade, $clienteId = null, $numeroPedido = null, array &$visitados = [])
    {
        if (in_array($produtoId, $visitados)) {
            return;
        }

        $visitados[] = $produtoId;

        $composicoes = ProdutoComposicao::with('ingrediente')
        ->where('produto_id', $produtoId)
        ->get();

        foreach ($composicoes as $comp) {

            if (!$comp->ingrediente) {
                continue;
            }

            $ingrediente = $comp->ingrediente;

            $temComposicao = ProdutoComposicao::where('produto_id', $ingrediente->id)->exists();

            if (!$temComposicao) {
                continue;
            }

            $qtdNecessaria = (float) $comp->quantidade * (float) $quantidade;

            $ordemSemi = $this->criarOrdemProgramacao($empresaId, 'OF de semi-elaborado gerada automaticamente pela programação');

            ItemOrdemProducao::create([
                'ordem_producao_id' => $ordemSemi->id,
                'item_producao_id' => null,
                'produto_id' => $ingrediente->id,
                'quantidade' => $qtdNecessaria,
                'cliente_id' => $clienteId,
                'status' => 0,
                'observacao' => 'Semi-elaborado gerado automaticamente',
                'numero_pedido' => $numeroPedido,
            ]);

            $this->gerarOfSemiElaboradosSeparado(
                $empresaId,
                $ingrediente->id,
                $qtdNecessaria,
                $clienteId,
                $numeroPedido,
                $visitados
            );
        }

        array_pop($visitados);
    }

    public function gerarOf(Request $request)
    {
        $request->validate([
            'produtos' => 'required|array',
            'produtos.*.produto_id' => 'required|integer',
            'produtos.*.quantidade' => 'required|numeric|min:0.001',
            'produtos.*.cliente_id' => 'nullable|integer',
            'produtos.*.numero_pedido' => 'nullable|string',
            'incluir_semi_elaborados' => 'nullable|boolean',
        ]);

        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id;

        DB::transaction(function () use ($request, $empresaId) {

            foreach ($request->produtos as $produto) {

                $quantidade = (float) ($produto['quantidade'] ?? 0);

                if ($quantidade <= 0) {
                    continue;
                }

                $ordem = $this->criarOrdemProgramacao($empresaId, 'OF gerada pela Programação de Produção');

                ItemOrdemProducao::create([
                    'ordem_producao_id' => $ordem->id,
                    'item_producao_id' => null,
                    'produto_id' => $produto['produto_id'],
                    'quantidade' => $quantidade,
                    'cliente_id' => $produto['cliente_id'] ?? null,
                    'status' => 0,
                    'observacao' => 'Gerado pela programação',
                    'numero_pedido' => $produto['numero_pedido'] ?? null,
                ]);

                if ($request->boolean('incluir_semi_elaborados')) {
                    $this->gerarOfSemiElaboradosSeparado(
                        $empresaId,
                        $produto['produto_id'],
                        $quantidade,
                        $produto['cliente_id'] ?? null,
                        $produto['numero_pedido'] ?? null
                    );
                }
            }
        });

        return redirect()
        ->route('programacao-producao.index')
        ->with('flash_success', 'Ordens de fabricação geradas com sucesso!');
    }

    private function gerarOfSemiElaborados($ordem, $produtoId, $quantidade, $clienteId = null, $numeroPedido = null, array &$visitados = [])
    {
        if (in_array($produtoId, $visitados)) {
            return;
        }

        $visitados[] = $produtoId;

        $composicoes = ProdutoComposicao::with('ingrediente')
        ->where('produto_id', $produtoId)
        ->get();

        foreach ($composicoes as $comp) {
            if (!$comp->ingrediente) {
                continue;
            }

            $ingrediente = $comp->ingrediente;

            $temComposicao = ProdutoComposicao::where('produto_id', $ingrediente->id)->exists();

            if (!$temComposicao) {
                continue;
            }

            $qtdNecessaria = (float) $comp->quantidade * (float) $quantidade;

            ItemOrdemProducao::create([
                'ordem_producao_id' => $ordem->id,
                'item_producao_id' => null,
                'produto_id' => $ingrediente->id,
                'quantidade' => $qtdNecessaria,
                'cliente_id' => $clienteId,
                'status' => 0,
                'observacao' => 'Semi-elaborado gerado automaticamente',
                'numero_pedido' => $numeroPedido,
            ]);

            $this->gerarOfSemiElaborados(
                $ordem,
                $ingrediente->id,
                $qtdNecessaria,
                $clienteId,
                $numeroPedido,
                $visitados
            );
        }

        array_pop($visitados);
    }

    private function proximoCodigoOf($empresaId)
    {
        return ((int) OrdemProducao::where('empresa_id', $empresaId)->max('codigo_sequencial')) + 1;
    }

    private function estoqueProduto($produto)
    {
        if (method_exists($produto, 'estoqueAtual')) {
            return (float) ($produto->estoqueAtual() ?? 0);
        }

        return (float) ($produto->estoque?->quantidade ?? 0);
    }

    public function pdfPedidos(Request $request)
    {
        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id;

        $pedidos = $this->pedidosProducao($request, $empresaId);

        $p = view('programacao_producao.pdf.pedidos', compact('pedidos'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper('A4', 'landscape');
        $domPdf->render();

        return $domPdf->stream('programacao-pedidos.pdf', ["Attachment" => false]);
    }

    public function pdfProdutos(Request $request)
    {
        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id;

        $incluirSemi = session('programacao_producao_incluir_semi', true);

        $produtos = $this->produtosSugestao($request, $empresaId, $incluirSemi);

        $p = view('programacao_producao.pdf.produtos', compact('produtos'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper('A4', 'landscape');
        $domPdf->render();

        return $domPdf->stream('produtos-a-produzir.pdf', ["Attachment" => false]);
    }

    public function pdfOrdens(Request $request)
    {
        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id;

        $ordens = $this->ordensFabricacao($empresaId);

        $p = view('programacao_producao.pdf.ordens', compact('ordens'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper('A4', 'landscape');
        $domPdf->render();

        return $domPdf->stream('ordens-producao.pdf', ["Attachment" => false]);
    }

    public function pdfMateriais(Request $request)
    {
        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id;

        $incluirSemi = session('programacao_producao_incluir_semi', true);

        $produtos = $this->produtosSugestao($request, $empresaId, $incluirSemi);
        $materiais = $this->necessidadeMateriais($produtos, $empresaId);

        $p = view('programacao_producao.pdf.materiais', compact('materiais'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper('A4', 'landscape');
        $domPdf->render();

        return $domPdf->stream('necessidade-materiais.pdf', ["Attachment" => false]);
    }
}