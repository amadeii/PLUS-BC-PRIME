<?php

namespace App\Utils;

use App\Models\Produto;
use App\Models\OrdemProducao;
use App\Models\OrdemProducaoOperacao;
use App\Models\OrdemProducaoMaterial;

class OrdemProducaoUtil
{
    public static function gerarOperacoesRoteiro($ordem, $itemOrdem)
    {
        $produto = Produto::with(['roteiro.operacoes.operacao', 'roteiro.operacoes.setor'])->find($itemOrdem->produto_id);

        if(!$produto || !$produto->roteiro){
            return [
                'success' => false,
                'tipo' => 'sem_roteiro',
                'mensagem' => 'Produto sem roteiro cadastrado'
            ];
        }

        foreach($produto->roteiro->operacoes->sortBy('sequencia') as $op){
            OrdemProducaoOperacao::create([
                'ordem_producao_id' => $ordem->id,
                'item_ordem_producao_id' => $itemOrdem->id,
                'operacao_id' => $op->operacao_id,
                'setor_id' => $op->setor_id,
                'sequencia' => $op->sequencia,
                'nome_operacao' => $op->operacao->nome ?? $op->nome_operacao ?? '',
                'nome_setor' => $op->setor->nome ?? $op->nome_setor ?? '',
                'tempo_previsto_minutos' => $op->tempo_previsto_minutos ?? 0,
                'tempo_real_minutos' => 0,
                'status' => 'pendente',
            ]);
        }

        return ['success' => true];
    }

    public static function gerarMateriaisProduto($ordem, $itemOrdem, $nivel = 0, $produtoPaiId = null)
    {
        $produto = Produto::with(['composicao.ingrediente.composicao.ingrediente'])->find($itemOrdem->produto_id);

        if(!$produto || !$produto->composicao || $produto->composicao->count() == 0){
            return [
                'success' => false,
                'tipo' => 'sem_estrutura',
                'mensagem' => 'Produto sem composição cadastrada'
            ];
        }

        foreach($produto->composicao as $comp){

            $material = $comp->ingrediente;

            if(!$material){
                continue;
            }

            $quantidadePrevista = (float) $comp->quantidade * (float) $itemOrdem->quantidade;
            $custoUnitario = (float) ($material->valor_compra ?? 0);

            $estoqueAtual = method_exists($material, 'estoqueAtual') ? (float) $material->estoqueAtual() : (float) ($material->estoque_atual ?? 0);

            $statusEstoque = 'ok';

            if($estoqueAtual <= 0){
                $statusEstoque = 'sem_estoque';
            }else if($estoqueAtual < $quantidadePrevista){
                $statusEstoque = 'insuficiente';
            }

            OrdemProducaoMaterial::create([
                'ordem_producao_id' => $ordem->id,
                'item_ordem_producao_id' => $itemOrdem->id,
                'ordem_producao_operacao_id' => null,
                'produto_id' => $itemOrdem->produto_id,
                'material_id' => $material->id,
                'quantidade_prevista' => $quantidadePrevista,
                'quantidade_real' => 0,
                'quantidade_perda' => 0,
                'unidade' => $material->unidade ?? 'UN',
                'custo_unitario' => $custoUnitario,
                'custo_total_previsto' => $quantidadePrevista * $custoUnitario,
                'custo_total_real' => 0,
                'status_estoque' => $statusEstoque,
                'observacao' => null,
            ]);
        }

        return ['success' => true];
    }

    public static function validarOrdem($ordemId)
    {
        $ordem = OrdemProducao::with(['itens.produto.roteiro.operacoes', 'itens.produto.composicao.ingrediente', 'materiais.material', 'operacoes'])->find($ordemId);

        $alertas = [];
        $estruturaOk = true;
        $roteiroOk = true;
        $estoqueOk = true;
        $custosOk = true;

        foreach($ordem->itens as $item){

            $produto = $item->produto;

            if(!$produto){
                continue;
            }

            if(!$produto->roteiro){
                $roteiroOk = false;
                $alertas[] = [
                    'tipo' => 'sem_roteiro',
                    'produto' => $produto->nome,
                    'mensagem' => 'Produto sem roteiro cadastrado'
                ];
            }

            if($produto->roteiro && $produto->roteiro->operacoes){
                foreach($produto->roteiro->operacoes as $op){
                    if((float) ($op->tempo_previsto_minutos ?? 0) <= 0){
                        $roteiroOk = false;
                        $alertas[] = [
                            'tipo' => 'operacao_sem_tempo',
                            'produto' => $produto->nome,
                            'mensagem' => 'Existe operação sem tempo previsto'
                        ];
                    }
                }
            }

            if(!$produto->composicao || $produto->composicao->count() == 0){
                $estruturaOk = false;
                $alertas[] = [
                    'tipo' => 'sem_estrutura',
                    'produto' => $produto->nome,
                    'mensagem' => 'Produto sem estrutura/composição cadastrada'
                ];
            }
        }

        foreach($ordem->materiais as $m){
            if($m->status_estoque == 'sem_estoque' || $m->status_estoque == 'insuficiente'){
                $estoqueOk = false;
                $alertas[] = [
                    'tipo' => $m->status_estoque,
                    'produto' => $m->material->nome ?? 'Material',
                    'mensagem' => $m->status_estoque == 'sem_estoque' ? 'Material sem estoque' : 'Estoque insuficiente'
                ];
            }
        }

        self::recalcularCustos($ordem);

        $ordem = OrdemProducao::find($ordem->id);
        $custosOk = (float) $ordem->custo_total > 0;

        $ordem->estrutura_ok = $estruturaOk ? 1 : 0;
        $ordem->roteiro_ok = $roteiroOk ? 1 : 0;
        $ordem->estoque_ok = $estoqueOk ? 1 : 0;
        $ordem->custos_ok = $custosOk ? 1 : 0;
        $ordem->save();

        return [
            'success' => true,
            'alertas' => $alertas,
            'estrutura_ok' => $estruturaOk,
            'roteiro_ok' => $roteiroOk,
            'estoque_ok' => $estoqueOk,
            'custos_ok' => $custosOk,
            'pode_liberar' => $estruturaOk && $roteiroOk && $estoqueOk && $custosOk
        ];
    }

    public static function recalcularCustos($ordem)
    {
        $ordem = OrdemProducao::with(['materiais', 'operacoes'])->find($ordem->id);

        $custoMaterial = $ordem->materiais->sum(function($m){
            return (float) ($m->custo_total_previsto ?? 0);
        });

        $custoMaoObra = 0;

        $custoProcesso = $ordem->operacoes->sum(function($op){
            return (float) ($op->custo_processo ?? 0);
        });

        $custoTotal = $custoMaterial + $custoMaoObra + $custoProcesso;

        $ordem->custo_material = $custoMaterial;
        $ordem->custo_mao_obra = $custoMaoObra;
        $ordem->custo_processo = $custoProcesso;
        $ordem->custo_total = $custoTotal;
        $ordem->custos_ok = $custoTotal > 0 ? 1 : 0;
        $ordem->save();

        return $ordem;
    }

    public static function atualizarQuantidadePendente($ordem)
    {
        $ordem = OrdemProducao::with('itens')->find($ordem->id);

        $total = $ordem->itens->sum(function($item){
            return (float) $item->quantidade;
        });

        $pendente = $total - (float) $ordem->quantidade_produzida - (float) $ordem->quantidade_refugada;

        if($pendente < 0){
            $pendente = 0;
        }

        $percentual = $total > 0 ? (((float) $ordem->quantidade_produzida / $total) * 100) : 0;

        $ordem->quantidade_pendente = $pendente;
        $ordem->percentual_progresso = $percentual > 100 ? 100 : $percentual;
        $ordem->save();

        return $ordem;
    }

    public static function atualizarStatusAutomatico($ordem)
    {
        $ordem = OrdemProducao::with(['itens'])->find($ordem->id);

        if($ordem->data_encerramento){
            $ordem->estado = 'encerrada';

        }else if($ordem->data_finalizacao || (float) $ordem->percentual_progresso >= 100){
            $ordem->estado = 'finalizada';

        }else if((float) $ordem->percentual_progresso > 0){
            $ordem->estado = 'parcial';

        }else if($ordem->data_liberacao){
            $ordem->estado = 'liberada';

        }else{
            $ordem->estado = 'novo';
        }

        $ordem->save();

        return $ordem;
    }

    public static function simularCustosProduto($produtoId, $quantidade = 1)
    {
        $produto = Produto::with(['composicao.ingrediente', 'roteiro.operacoes'])->find($produtoId);

        $custoMaterial = 0;
        $custoProcesso = 0;
        $alertas = [];

        if(!$produto){
            return [
                'success' => false,
                'message' => 'Produto não encontrado'
            ];
        }

        if(!$produto->composicao || $produto->composicao->count() == 0){
            $alertas[] = [
                'tipo' => 'sem_estrutura',
                'mensagem' => 'Produto sem estrutura cadastrada'
            ];
        }else{
            foreach($produto->composicao as $comp){
                $material = $comp->ingrediente;

                if(!$material){
                    continue;
                }

                $qtd = (float) $comp->quantidade * (float) $quantidade;
                $custoUnitario = (float) ($material->valor_compra ?? 0);

                $custoMaterial += $qtd * $custoUnitario;
            }
        }

        if(!$produto->roteiro){
            $alertas[] = [
                'tipo' => 'sem_roteiro',
                'mensagem' => 'Produto sem roteiro cadastrado'
            ];
        }else{
            foreach($produto->roteiro->operacoes as $op){
                $custoProcesso += (float) ($op->custo_processo ?? 0);
            }
        }

        $total = $custoMaterial + $custoProcesso;

        return [
            'success' => true,
            'produto' => $produto->nome,
            'quantidade' => $quantidade,
            'custo_material' => $custoMaterial,
            'custo_mao_obra' => 0,
            'custo_processo' => $custoProcesso,
            'custo_total' => $total,
            'alertas' => $alertas
        ];
    }
}