<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\RotinaFabricacao;
use App\Models\ProdutoComposicao;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use App\Models\OrdemProducao;
use Carbon\Carbon;

class ProdutoRotinaController extends Controller
{
    public function projecaoCusto($id)
    {

        $item = RotinaFabricacao::with([
            'produto.composicao.ingrediente.categoria',
            'operacoes.operacao.setor.centroCusto',
            'operacoes.setor.centroCusto'
        ])->findOrFail($id);

        $produto = $item->produto;

        return view('produtos.rotina.projecao_custo', compact('item', 'produto'));
    }

    public function simularCusto(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|numeric|min:1',
            'markup' => 'nullable|numeric|min:0'
        ]);

        $item = RotinaFabricacao::with([
            'produto.composicao.ingrediente.categoria',
            'operacoes.operacao.setor.centroCusto',
            'operacoes.setor.centroCusto'
        ])->findOrFail($id);

        $quantidade = (float) $request->quantidade;
        $markup = (float) ($request->markup ?? 30);


        $materiais = [];
        $totalMateriais = 0;

        if ($item->produto && $item->produto->composicao) {
            foreach ($item->produto->composicao as $comp) {
                if (!$comp->ingrediente) {
                    continue;
                }

                $qtdBase = (float) $comp->quantidade;
                $qtdTotal = $qtdBase * $quantidade;

                $custoUnitario = (float) ($comp->ingrediente->valor_compra ?? 0);
                $totalItem = $qtdTotal * $custoUnitario;

                $materiais[] = [
                    'codigo' => $comp->ingrediente->numero_sequencial ?? '--',
                    'descricao' => $comp->ingrediente->nome,
                    'quantidade_base' => $qtdBase,
                    'quantidade_total' => $qtdTotal,
                    'unidade' => $comp->ingrediente->unidade ?? 'un',
                    'categoria' => $comp->ingrediente->categoria->nome ?? 'Sem categoria',
                    'custo_unitario' => $custoUnitario,
                    'total' => $totalItem,
                ];

                $totalMateriais += $totalItem;
            }
        }

        $processos = [];
        $subtotalProcesso = 0;
        $prazoDias = 0;

    // processo da rotina principal
        [$processosPrincipais, $custoProcessoPrincipal, $prazoPrincipal] = $this->calcularProcessoRotina($item, $quantidade);

        $processos = array_merge($processos, $processosPrincipais);
        $subtotalProcesso += $custoProcessoPrincipal;
        $prazoDias += $prazoPrincipal;

    // processo das rotinas filhas existentes na composição
        $visitados = [$item->id];

        if ($item->produto && $item->produto->composicao) {
            foreach ($item->produto->composicao as $comp) {
                if (!$comp->ingrediente) {
                    continue;
                }

                $rotinaFilha = RotinaFabricacao::with([
                    'produto.composicao.ingrediente.categoria',
                    'operacoes.operacao.setor.centroCusto',
                    'operacoes.setor.centroCusto'
                ])->where('produto_id', $comp->ingrediente->id)->first();

                if (!$rotinaFilha) {
                    continue;
                }

            // quantidade total que precisa fabricar da rotina filha
                $qtdFilha = (float) $comp->quantidade * $quantidade;

                [$processosFilhos, $custoProcessoFilho, $prazoFilho] = $this->calcularProcessoRotinaRecursivo(
                    $rotinaFilha,
                    $qtdFilha,
                    $visitados
                );

                foreach ($processosFilhos as &$proc) {
                    $proc['origem'] = $comp->ingrediente->nome;
                }

                $processos = array_merge($processos, $processosFilhos);
                $subtotalProcesso += $custoProcessoFilho;
                $prazoDias += $prazoFilho;
            }
        }

        $custoTotal = $totalMateriais + $subtotalProcesso;
        $custoUnitario = $quantidade > 0 ? ($custoTotal / $quantidade) : 0;
        $precoSugerido = $custoUnitario * (1 + ($markup / 100));

        $processosParaPrazo = [];
        $visitadosPrazo = [];

        $this->montarProcessosPrazoRecursivo($item, $quantidade, $processosParaPrazo, $visitadosPrazo);

        $capacidade = $this->calcularCapacidadeSetoresPorProcessos($item, $processosParaPrazo);

        $diasEstimados = max(1, (int) ceil($capacidade['dias_total']));
        $dataEntrega = now()->addWeekdays($diasEstimados)->format('d/m/Y');

        return response()->json([
            'success' => true,
            'quantidade' => $quantidade,
            'markup' => $markup,

            'materiais' => $materiais,
            'total_materiais' => $totalMateriais,

            'processos' => $processos,
            'subtotal_processo' => $subtotalProcesso,

            'custo_total' => $custoTotal,
            'custo_unitario' => $custoUnitario,
            'preco_sugerido' => $precoSugerido,

            'prazo_dias' => $diasEstimados,
            'data_entrega' => $dataEntrega,

            'capacidade_setores' => $capacidade['setores'],
            'dias_fila_setores' => $capacidade['dias_fila'],
            'dias_producao_pedido' => $capacidade['dias_pedido'],
            'gargalo_setor' => $capacidade['gargalo_setor'],
            'total_minutos_pedido' => $capacidade['total_minutos_pedido'] ?? 0,
        ]);
    }

    private function montarProcessosPrazoRecursivo($rotina, float $quantidade, array &$processos, array &$visitados = [], int $nivel = 0): void
    {
        if (!$rotina || in_array($rotina->id, $visitados)) {
            return;
        }

        $visitados[] = $rotina->id;

        foreach ($rotina->operacoes as $op) {
            $setor = $op->setor ?: optional($op->operacao)->setor;

            if (!$setor) {
                continue;
            }

            $tempoUnitario = (float) ($op->tempo_minutos ?? 0);
            $setup = (float) ($op->setup_minutos ?? 0);
            $tempoTotal = ($tempoUnitario * $quantidade) + $setup;

            $processos[] = [
                'rotina_id' => $rotina->id,
                'produto_id' => $rotina->produto_id,
                'produto' => optional($rotina->produto)->nome ?? '-',
                'operacao' => optional($op->operacao)->nome ?? 'Operação',
                'setor_id' => $setor->id,
                'setor' => $setor->nome,
                'horas_dia' => (float) ($setor->horas_dia ?? 8),
                'eficiencia' => (float) ($setor->eficiencia ?? 100),
                'tempo_total_min' => $tempoTotal,
                'nivel' => $nivel,
            ];
        }

        if ($rotina->produto && $rotina->produto->composicao) {
            foreach ($rotina->produto->composicao as $comp) {
                if (!$comp->ingrediente) {
                    continue;
                }

                $rotinaFilha = RotinaFabricacao::with([
                    'produto.composicao.ingrediente.categoria',
                    'operacoes.operacao.setor.centroCusto',
                    'operacoes.setor.centroCusto'
                ])->where('produto_id', $comp->ingrediente->id)->first();

                if (!$rotinaFilha) {
                    continue;
                }

                $qtdFilha = (float) $comp->quantidade * $quantidade;

                $this->montarProcessosPrazoRecursivo(
                    $rotinaFilha,
                    $qtdFilha,
                    $processos,
                    $visitados,
                    $nivel + 1
                );
            }
        }

        array_pop($visitados);
    }

    private function calcularCapacidadeSetoresPorProcessos($rotina, array $processos): array
    {
        $setores = [];

        foreach ($processos as $proc) {
            $setorId = $proc['setor_id'] ?? null;

            if (!$setorId) {
                continue;
            }

            if (!isset($setores[$setorId])) {
                $horasDia = (float) ($proc['horas_dia'] ?? 8);
                $eficiencia = (float) ($proc['eficiencia'] ?? 100);

                if ($horasDia <= 0) {
                    $horasDia = 8;
                }

                if ($eficiencia <= 0) {
                    $eficiencia = 100;
                }

                $capacidadeDiaMinutos = ($horasDia * 60) * ($eficiencia / 100);

                if ($capacidadeDiaMinutos <= 0) {
                    $capacidadeDiaMinutos = 480;
                }

                $setores[$setorId] = [
                    'setor_id' => $setorId,
                    'setor' => $proc['setor'] ?? '-',
                    'horas_dia' => $horasDia,
                    'eficiencia' => $eficiencia,
                    'capacidade_dia_horas' => $capacidadeDiaMinutos / 60,
                    'capacidade_dia_minutos' => $capacidadeDiaMinutos,
                    'minutos_fila' => 0,
                    'minutos_pedido' => 0,
                    'dias_fila' => 0,
                    'dias_pedido' => 0,
                    'dias_total' => 0,
                    'data_prevista' => null,
                ];
            }

            $setores[$setorId]['minutos_pedido'] += (float) ($proc['tempo_total_min'] ?? 0);
        }

        $produtoIds = collect($processos)->pluck('produto_id')->filter()->unique()->values()->toArray();

        if (!empty($produtoIds)) {
            $ordensEmAndamento = OrdemProducao::where('empresa_id', $rotina->empresa_id)
            ->whereIn('estado', ['novo', 'producao'])
            ->whereHas('itens', function($q) use ($produtoIds) {
                $q->whereIn('produto_id', $produtoIds);
            })
            ->with([
                'itens' => function($q) use ($produtoIds) {
                    $q->whereIn('produto_id', $produtoIds);
                },
                'itens.produto.rotinaFabricacao.produto.composicao.ingrediente.categoria',
                'itens.produto.rotinaFabricacao.operacoes.setor',
                'itens.produto.rotinaFabricacao.operacoes.operacao.setor'
            ])
            ->get();

            foreach ($ordensEmAndamento as $ordem) {
                foreach ($ordem->itens as $itemOp) {
                    $rotinaItem = optional($itemOp->produto)->rotinaFabricacao;

                    if (!$rotinaItem) {
                        continue;
                    }

                    $quantidadeItem = (float) (
                        $itemOp->quantidade_a_produzir
                        ?? $itemOp->quantidade_produzir
                        ?? $itemOp->qtd_produzir
                        ?? $itemOp->quantidade
                        ?? 1
                    );

                    $processosFila = [];
                    $visitadosFila = [];

                    $this->montarProcessosPrazoRecursivo(
                        $rotinaItem,
                        $quantidadeItem,
                        $processosFila,
                        $visitadosFila
                    );

                    foreach ($processosFila as $procFila) {
                        $setorFilaId = $procFila['setor_id'] ?? null;

                        if (!$setorFilaId || !isset($setores[$setorFilaId])) {
                            continue;
                        }

                        $setores[$setorFilaId]['minutos_fila'] += (float) ($procFila['tempo_total_min'] ?? 0);
                    }
                }
            }
        }

        foreach ($setores as &$dados) {
            $capacidadeDiaMinutos = (float) ($dados['capacidade_dia_minutos'] ?? 480);

            if ($capacidadeDiaMinutos <= 0) {
                $capacidadeDiaMinutos = 480;
            }

            $dados['dias_fila'] = $dados['minutos_fila'] > 0 ? (int) ceil($dados['minutos_fila'] / $capacidadeDiaMinutos) : 0;
            $dados['dias_pedido'] = $dados['minutos_pedido'] > 0 ? (int) ceil($dados['minutos_pedido'] / $capacidadeDiaMinutos) : 0;
            $dados['dias_total'] = $dados['dias_fila'] + $dados['dias_pedido'];
            $dados['data_prevista'] = now()->addWeekdays(max(1, $dados['dias_total']))->format('d/m/Y');
        }

        unset($dados);

        $setores = collect($setores)->values();

        $totalMinutosPedido = collect($processos)->sum('tempo_total_min');
        $totalMinutosFila = $setores->sum('minutos_fila');

        $gargalo = $setores->sortByDesc('dias_total')->first();

        return [
            'setores' => $setores->toArray(),
            'dias_fila' => $gargalo['dias_fila'] ?? 0,
            'dias_pedido' => $gargalo['dias_pedido'] ?? 1,
            'dias_total' => max(1, $gargalo['dias_total'] ?? 1),
            'gargalo_setor' => $gargalo['setor'] ?? '-',
            'total_minutos_pedido' => $totalMinutosPedido,
            'total_minutos_fila' => $totalMinutosFila,
        ];
    }

    private function calcularCapacidadeSetores($rotina, float $quantidade): array
    {
        $setores = [];
        $produtoId = $rotina->produto_id;

        foreach ($rotina->operacoes as $op) {
            $setor = $op->setor ?: optional($op->operacao)->setor;

            if (!$setor) {
                continue;
            }

            $setorId = $setor->id;

            $tempoUnitario = (float) ($op->tempo_minutos ?? 0);
            $setup = (float) ($op->setup_minutos ?? 0);
            $tempoPedido = ($tempoUnitario * $quantidade) + $setup;

            if (!isset($setores[$setorId])) {
                $setores[$setorId] = [
                    'setor_id' => $setorId,
                    'setor' => $setor->nome,
                    'horas_dia' => (float) ($setor->horas_dia ?? 8),
                    'eficiencia' => (float) ($setor->eficiencia ?? 100),
                    'capacidade_dia_horas' => 0,
                    'capacidade_dia_minutos' => 0,
                    'minutos_fila' => 0,
                    'minutos_pedido' => 0,
                    'dias_fila' => 0,
                    'dias_pedido' => 0,
                    'dias_total' => 0,
                    'data_prevista' => null,
                ];
            }

            $setores[$setorId]['minutos_pedido'] += $tempoPedido;
        }

        $ordensEmAndamento = OrdemProducao::where('empresa_id', $rotina->empresa_id)
        ->whereIn('estado', ['novo', 'producao'])
        ->whereHas('itens', function($q) use ($produtoId) {
            $q->where('produto_id', $produtoId);
        })
        ->with([
            'itens' => function($q) use ($produtoId) {
                $q->where('produto_id', $produtoId);
            },
            'itens.produto.rotinaFabricacao.operacoes.setor',
            'itens.produto.rotinaFabricacao.operacoes.operacao.setor'
        ])
        ->get();

        foreach ($setores as $setorId => &$dados) {

            foreach ($ordensEmAndamento as $ordem) {
                foreach ($ordem->itens as $item) {
                    $produtoOp = $item->produto ?? null;

                    if (!$produtoOp) {
                        continue;
                    }

                    $rotinaItem = $produtoOp->rotinaFabricacao ?? null;

                    if (!$rotinaItem) {
                        continue;
                    }

                    $quantidadeItem = (float) ($item->quantidade ?? 1);

                    foreach ($rotinaItem->operacoes as $op) {
                        $setorOp = $op->setor ?: optional($op->operacao)->setor;

                        if (!$setorOp || $setorOp->id != $setorId) {
                            continue;
                        }

                        $tempoUnitario = (float) ($op->tempo_minutos ?? 0);
                        $setup = (float) ($op->setup_minutos ?? 0);

                        $dados['minutos_fila'] += ($tempoUnitario * $quantidadeItem) + $setup;
                    }
                }
            }

            $horasDia = (float) ($dados['horas_dia'] ?? 8);
            $eficiencia = (float) ($dados['eficiencia'] ?? 100);

            if ($horasDia <= 0) {
                $horasDia = 8;
            }

            if ($eficiencia <= 0) {
                $eficiencia = 100;
            }

            $capacidadeDiaHoras = $horasDia * ($eficiencia / 100);
            $capacidadeDiaMinutos = $capacidadeDiaHoras * 60;

            if ($capacidadeDiaMinutos <= 0) {
                $capacidadeDiaMinutos = 480;
            }

            $dados['capacidade_dia_horas'] = $capacidadeDiaHoras;
            $dados['capacidade_dia_minutos'] = $capacidadeDiaMinutos;

            $dados['dias_fila'] = $dados['minutos_fila'] > 0 ? (int) ceil($dados['minutos_fila'] / $capacidadeDiaMinutos) : 0;
            $dados['dias_pedido'] = $dados['minutos_pedido'] > 0 ? (int) ceil($dados['minutos_pedido'] / $capacidadeDiaMinutos) : 0;
            $dados['dias_total'] = $dados['dias_fila'] + $dados['dias_pedido'];
            $dados['data_prevista'] = now()->addWeekdays($dados['dias_total'])->format('d/m/Y');
        }

        unset($dados);

        $setores = collect($setores)->values();

        $gargalo = $setores->sortByDesc('dias_total')->first();

        return [
            'setores' => $setores->toArray(),
            'dias_fila' => $gargalo['dias_fila'] ?? 0,
            'dias_pedido' => $gargalo['dias_pedido'] ?? 0,
            'dias_total' => $gargalo['dias_total'] ?? 1,
            'gargalo_setor' => $gargalo['setor'] ?? '-',
        ];
    }
    
    private function calcularProcessoRotina($rotina, float $quantidade): array
    {
        $processos = [];
        $subtotalProcesso = 0;
        $prazoDias = 0;

        foreach ($rotina->operacoes as $op) {
            $tempoUnitario = (float) ($op->tempo_minutos ?? 0);
            $setup = (float) ($op->setup_minutos ?? 0);

            $tempoTotal = ($tempoUnitario * $quantidade) + $setup;

            $setor = $op->setor ?: optional($op->operacao)->setor;
            $custoHora = (float) ($setor->custo_hora ?? 0);
            $horasDia = (float) ($setor->horas_dia ?? 8);
            $eficiencia = (float) ($setor->eficiencia ?? 100);

            $custoTotal = ($tempoTotal / 60) * $custoHora;
            $subtotalProcesso += $custoTotal;

            $capacidadeDiaMin = ($horasDia * 60) * ($eficiencia / 100);
            if ($capacidadeDiaMin > 0) {
                $prazoDias += ($tempoTotal / $capacidadeDiaMin);
            }

            $processos[] = [
                'operacao' => optional($op->operacao)->nome ?? 'Operação',
                'tempo_total_min' => $tempoTotal,
                'custo_hora' => $custoHora,
                'setor' => $setor->nome ?? '-',
                'centro_custo' => optional($setor->centroCusto)->nome ?? '-',
                'total' => $custoTotal,
                'origem' => optional($rotina->produto)->nome ?? 'Rotina principal',
            ];
        }

        return [$processos, $subtotalProcesso, $prazoDias];
    }


    private function calcularProcessoRotinaRecursivo($rotina, float $quantidade, array &$visitados = []): array
    {
        if (in_array($rotina->id, $visitados)) {
            return [[], 0, 0];
        }

        $visitados[] = $rotina->id;

        [$processos, $subtotalProcesso, $prazoDias] = $this->calcularProcessoRotina($rotina, $quantidade);

        if ($rotina->produto && $rotina->produto->composicao) {
            foreach ($rotina->produto->composicao as $comp) {
                if (!$comp->ingrediente) {
                    continue;
                }

                $rotinaFilha = RotinaFabricacao::with([
                    'produto.composicao.ingrediente.categoria',
                    'operacoes.operacao.setor.centroCusto',
                    'operacoes.setor.centroCusto'
                ])->where('produto_id', $comp->ingrediente->id)->first();

                if (!$rotinaFilha) {
                    continue;
                }

                $qtdFilha = (float) $comp->quantidade * $quantidade;

                [$procFilhos, $custoFilho, $prazoFilho] = $this->calcularProcessoRotinaRecursivo(
                    $rotinaFilha,
                    $qtdFilha,
                    $visitados
                );

                $processos = array_merge($processos, $procFilhos);
                $subtotalProcesso += $custoFilho;
                $prazoDias += $prazoFilho;
            }
        }

        return [$processos, $subtotalProcesso, $prazoDias];
    }

    public function simulacaoPdf(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|numeric|min:1',
            'markup' => 'nullable|numeric|min:0'
        ]);

        $item = RotinaFabricacao::with('produto')->findOrFail($id);

        $quantidade = (float)$request->quantidade;

        $materiais = [];
        $visitados = [];

        if ($item->produto) {
            $this->montarMateriaisRecursivo(
                $item->produto->id,
                $materiais,
                $quantidade,
                0,
                $visitados
            );
        }

        $totalMateriais = collect($materiais)->where('nivel', 0)->sum('total');

        $jsonResponse = $this->simularCusto($request, $id);
        $dados = $jsonResponse->getData(true);

        $dados['materiais'] = $materiais;
        $dados['total_materiais'] = $totalMateriais;
        $dados['qtd_materiais'] = count(collect($materiais)->where('nivel', 0));

        $html = view('produtos.rotina.simulacao_pdf', [
            'item' => $item,
            'produto' => $item->produto,
            'dados' => $dados
        ])->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $fileName = 'simulacao-custo-' . preg_replace(
            '/[^A-Za-z0-9\-]/',
            '-',
            $item->produto->nome ?? 'produto'
        ) . '.pdf';

        return response()->stream(
            function () use ($dompdf) {
                echo $dompdf->output();
            },
            200,
            [
                "Content-Type" => "application/pdf",
                "Content-Disposition" => "inline; filename=\"$fileName\""
            ]
        );
    }

    public function printInstrucao(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|numeric|min:1',
        ]);

        $item = RotinaFabricacao::with([
            'produto',
            'produto.composicao.ingrediente.categoria',
            'operacoes.operacao.setor.centroCusto',
            'operacoes.setor.centroCusto'
        ])->findOrFail($id);

        $quantidade = (float)$request->quantidade;

        $materiais = [];
        $visitados = [];

        if ($item->produto) {
            $this->montarMateriaisRecursivo(
                $item->produto->id,
                $materiais,
                $quantidade,
                0,
                $visitados
            );
        }

        $checklist = [];
        if (!empty($item->checklist_texto)) {
            $checklist = preg_split('/\r\n|\r|\n/', $item->checklist_texto);
            $checklist = array_values(array_filter(array_map('trim', $checklist)));
        }

        $instrucoesEspeciais = [];
        if (!empty($item->instrucoes_especiais)) {
            $instrucoesEspeciais = preg_split('/\r\n|\r|\n/', $item->instrucoes_especiais);
            $instrucoesEspeciais = array_values(array_filter(array_map('trim', $instrucoesEspeciais)));
        }

        $assinaturas = [];
        if (!empty($item->assinaturas)) {
            $assinaturas = json_decode($item->assinaturas, true);

            if (!is_array($assinaturas)) {
                $linhas = preg_split('/\r\n|\r|\n/', $item->assinaturas);
                $linhas = array_values(array_filter(array_map('trim', $linhas)));

                foreach ($linhas as $linha) {
                    $assinaturas[] = [
                        'funcao' => $linha,
                        'responsavel' => ''
                    ];
                }
            }
        }

        $html = view('produtos.rotina.instrucao_pdf', [
            'item' => $item,
            'produto' => $item->produto,
            'quantidade' => $quantidade,
            'materiais' => $materiais,
            'checklist' => $checklist,
            'instrucoesEspeciais' => $instrucoesEspeciais,
            'assinaturas' => $assinaturas
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'instrucao-trabalho-' . preg_replace(
            '/[^A-Za-z0-9\-]/',
            '-',
            $item->produto->nome ?? 'produto'
        ) . '.pdf';

        return response()->stream(
            function () use ($dompdf) {
                echo $dompdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$fileName\""
            ]
        );
    }

    private function montarMateriaisRecursivo($produtoId, array &$materiais, $multiplicador = 1, $nivel = 0, array &$visitados = [])
    {
        if (in_array($produtoId, $visitados)) {
            return;
        }

        $visitados[] = $produtoId;

        $composicoes = ProdutoComposicao::with(['ingrediente.categoria'])
        ->where('produto_id', $produtoId)
        ->get();

        foreach ($composicoes as $comp) {
            if (!$comp->ingrediente) {
                continue;
            }

            $ingrediente = $comp->ingrediente;

            $quantidadeBase = (float)($comp->quantidade ?? 0);
            $quantidadeTotal = $quantidadeBase * (float)$multiplicador;
            $custoUnitario = (float)($ingrediente->valor_compra ?? 0);
            $totalItem = $quantidadeTotal * $custoUnitario;

            $materiais[] = [
                'codigo' => $ingrediente->numero_sequencial ?? '--',
                'descricao' => $ingrediente->nome ?? '',
                'quantidade_base' => $quantidadeBase,
                'quantidade_total' => $quantidadeTotal,
                'unidade' => $ingrediente->unidade ?? 'un',
                'categoria' => optional($ingrediente->categoria)->nome ?? 'Sem categoria',
                'custo_unitario' => $custoUnitario,
                'total' => $totalItem,
                'nivel' => $nivel,
            ];

            $temFilhos = ProdutoComposicao::where('produto_id', $ingrediente->id)->exists();

            if ($temFilhos) {
                $this->montarMateriaisRecursivo(
                    $ingrediente->id,
                    $materiais,
                    $quantidadeTotal,
                    $nivel + 1,
                    $visitados
                );
            }
        }

        array_pop($visitados);
    }
}