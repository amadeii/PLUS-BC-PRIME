<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Models\SangriaCaixa;
use App\Models\Localizacao;
use App\Models\FaturaNfce;
use App\Models\FaturaNfe;
use App\Models\ItemContaEmpresa;
use App\Models\SuprimentoCaixa;
use App\Models\User;
use App\Models\Nfce;
use App\Models\Nfe;
use App\Models\ItemServicoNfce;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Troca;
use App\Models\OrdemServico;
use App\Utils\ContaEmpresaUtil;
use Illuminate\Support\Facades\DB;

class CaixaController extends Controller
{

    protected $util;
    public function __construct(ContaEmpresaUtil $util){
        $this->util = $util;
    }

    public function verificarAberto(Request $request){
        $caixa = Caixa::where('usuario_id', $request->usuario_id)
        ->where('status', 1)->first();

        return response()->json([
            'caixa_id' => $caixa ? $caixa->id : null
        ]);
    }

    private function getLastNumeroSequencial($empresa_id){
        $last = Caixa::where('empresa_id', $empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)->first();
        $numero = $last != null ? $last->numero_sequencial : 0;
        $numero++;
        return $numero;
    }

    public function abrir(Request $request)
    {
        try {

            $usuario = User::find($request->usuario_id);
            $local = Localizacao::where('empresa_id', $usuario->empresa->empresa_id)->first();
            $data = [
                'usuario_id' => $usuario->id,
                'valor_abertura' => __convert_value_bd($request->valor_abertura),
                'observacao' => $request->observacao ?? '',
                'status' => 1,
                'local_id' => $local->id,
                'empresa_id' => $request->empresa_id,
                'valor_fechamento' => 0,
                'numero_sequencial' => $this->getLastNumeroSequencial($usuario->empresa->empresa_id)
            ];
            $item = Caixa::create($data);

            return response()->json([
                'sucesso' => true,
                'caixa_id' => $item->id,
                'message' => 'Caixa aberto com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'message' => 'Erro ao abrir caixa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saldoDinheiro($caixaId)
    {
        $caixa = Caixa::findOrFail($caixaId);
        $empresaId = $caixa->empresa_id;
        $sangrias = SangriaCaixa::where('caixa_id', $caixa->id)->sum('valor');

        $somaPdv = FaturaNfce::where('tipo_pagamento', '01')
        ->whereHas('nfce', function ($q) use ($empresaId, $caixa) {
            $q->where('empresa_id', $empresaId)
            ->where('caixa_id', $caixa->id);
        })
        ->sum('valor');

        $somaVendas = FaturaNfe::where('tipo_pagamento', '01')
        ->whereHas('nfe', function ($q) use ($empresaId, $caixa) {
            $q->where('empresa_id', $empresaId)
            ->where('caixa_id', $caixa->id);
        })
        ->sum('valor');

        $valor = $caixa->valor_abertura - $sangrias + $somaPdv + $somaVendas;

        return response()->json([
            'valor' => (float) ($valor ?? 0)
        ]);
    }

    public function sangria(Request $request)
    {
        try {

            if (!$request->valor || __convert_value_bd($request->valor) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe um valor maior que zero'
                ], 422);
            }

            $valor = __convert_value_bd($request->valor);
            $caixa = Caixa::findOrFail($request->caixa_id);

            $sangria = SangriaCaixa::create([
                'caixa_id' => $request->caixa_id,
                'valor' => $valor,
                'observacao' => $request->observacao ?? '',
                'conta_empresa_id' => $request->conta_empresa_sangria_id ?? null
            ]);

            if ($request->conta_empresa_sangria_id) {

                $data = [
                    'conta_id' => $caixa->conta_empresa_id,
                    'descricao' => 'Sangria de caixa',
                    'tipo_pagamento' => '01',
                    'valor' => $valor,
                    'caixa_id' => $caixa->id,
                    'tipo' => 'saida'
                ];
                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);

                $data = [
                    'conta_id' => $request->conta_empresa_sangria_id,
                    'descricao' => 'Sangria de caixa',
                    'tipo_pagamento' => '01',
                    'valor' => $valor,
                    'caixa_id' => $caixa->id,
                    'tipo' => 'entrada'
                ];
                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sangria realizada com sucesso!',
                'sangria_id' => $sangria->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Valor da sangria ultrapassa o valor disponível!'
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Algo deu errado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function suprimento(Request $request)
    {
        try {
            if (!$request->valor || __convert_value_bd($request->valor) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe um valor maior que zero'
                ], 422);
            }

            $valor = __convert_value_bd($request->valor);

            $suprimento = SuprimentoCaixa::create([
                'caixa_id' => $request->caixa_id,
                'valor' => $valor,
                'observacao' => $request->observacao ?? '',
                'tipo_pagamento' => $request->tipo_pagamento ?? '01',
                'conta_empresa_id' => $request->conta_empresa_suprimento_id ?? null
            ]);

            if ($request->conta_empresa_suprimento_id) {
                $caixa = Caixa::findOrFail($request->caixa_id);

                $data = [
                    'conta_id' => $caixa->conta_empresa_id,
                    'descricao' => 'Suprimento de caixa',
                    'tipo_pagamento' => $request->tipo_pagamento ?? '01',
                    'valor' => $valor,
                    'caixa_id' => $caixa->id,
                    'tipo' => 'entrada'
                ];
                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);

                $data = [
                    'conta_id' => $request->conta_empresa_suprimento_id,
                    'descricao' => 'Suprimento de caixa',
                    'tipo_pagamento' => $request->tipo_pagamento ?? '01',
                    'valor' => $valor,
                    'caixa_id' => $caixa->id,
                    'tipo' => 'saida'
                ];
                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);
            }

            return response()->json([
                'success' => true,
                'message' => 'Suprimento realizado com sucesso!',
                'suprimento_id' => $suprimento->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Algo deu errado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verCaixa($id, Request $request)
    {
        try {
            $item = Caixa::with('usuario')->findOrFail($id);

            if ((int)$item->empresa_id !== (int)$request->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Caixa não pertence à empresa informada'
                ], 403);
            }

            $nfce = Nfce::with(['cliente', 'fatura'])
            ->where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->where('estado', '!=', 'cancelado')
            ->get();

            $nfe = Nfe::with(['cliente', 'fatura'])
            ->where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->where('tpNF', 1)
            ->where('orcamento', 0)
            ->where('finNFe', 1)
            ->where('estado', '!=', 'cancelado')
            ->get();

            $contasReceber = ContaReceber::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->where('status', 1)
            ->get();

            $contasPagar = ContaPagar::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->where('status', 1)
            ->get();

            $trocas = Troca::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->get();

            $trocasPagasPorCliente = Troca::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->whereColumn('valor_troca', '>', 'valor_original')
            ->selectRaw('SUM(ABS(valor_troca - valor_original)) as total')
            ->value('total');

            $trocasPagasAoCliente = Troca::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->whereColumn('valor_troca', '<', 'valor_original')
            ->selectRaw('SUM(ABS(valor_original - valor_troca)) as total')
            ->value('total');

            $ordens = OrdemServico::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->get();

            $compras = Nfe::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->where('tpNF', 0)
            ->where('orcamento', 0)
            ->where('finNFe', 1)
            ->get();

            $nfce->map(function ($d) {
                $d->tipo = 'PDV';
                return $d;
            });

            $nfe->map(function ($d) {
                $d->tipo = 'Pedido';
                return $d;
            });

            $vendas = $nfce->concat($nfe)->sortByDesc('created_at')->values();
            $somaTiposPagamento = $this->somaTiposPagamento($vendas);

            $somaServicos = ItemServicoNfce::join('nfces', 'nfces.id', '=', 'item_servico_nfces.nfce_id')
            ->where('nfces.empresa_id', $request->empresa_id)
            ->where('nfces.caixa_id', $item->id)
            ->sum('sub_total')
            + OrdemServico::where('empresa_id', $request->empresa_id)
            ->where('caixa_id', $item->id)
            ->sum('valor');

            $suprimentos = SuprimentoCaixa::where('caixa_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->get();

            $sangrias = SangriaCaixa::where('caixa_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->get();

            $somaVendas = (float) $vendas->sum('total');
            $somaCompras = (float) $compras->sum('total');
            $somaContasReceber = (float) $contasReceber->sum('valor_recebido');
            $somaOs = (float) $ordens->sum('valor');
            $somaContasPagar = (float) $contasPagar->sum('valor_pago');
            $somaSuprimentos = (float) $suprimentos->sum('valor');
            $somaSangrias = (float) $sangrias->sum('valor');

            $somaPendentesCrediario = FaturaNfe::join('nves', 'nves.id', '=', 'fatura_nves.nfe_id')
            ->where('nves.empresa_id', $request->empresa_id)
            ->where('nves.caixa_id', $item->id)
            ->where('fatura_nves.tipo_pagamento', '06')
            ->sum('fatura_nves.valor');

            $somaPendentesCrediario += FaturaNfce::join('nfces', 'nfces.id', '=', 'fatura_nfces.nfce_id')
            ->where('nfces.empresa_id', $request->empresa_id)
            ->where('nfces.caixa_id', $item->id)
            ->where('fatura_nfces.tipo_pagamento', '06')
            ->sum('fatura_nfces.valor');

            $somaPendentBoleto = FaturaNfe::join('nves', 'nves.id', '=', 'fatura_nves.nfe_id')
            ->where('nves.empresa_id', $request->empresa_id)
            ->where('nves.caixa_id', $item->id)
            ->where('fatura_nves.tipo_pagamento', '15')
            ->sum('fatura_nves.valor');

            $somaPendentBoleto += FaturaNfce::join('nfces', 'nfces.id', '=', 'fatura_nfces.nfce_id')
            ->where('nfces.empresa_id', $request->empresa_id)
            ->where('nfces.caixa_id', $item->id)
            ->where('fatura_nfces.tipo_pagamento', '15')
            ->sum('fatura_nfces.valor');

            $somaPendentesCreditoLoja = FaturaNfe::join('nves', 'nves.id', '=', 'fatura_nves.nfe_id')
            ->where('nves.empresa_id', $request->empresa_id)
            ->where('nves.caixa_id', $item->id)
            ->where('fatura_nves.tipo_pagamento', '05')
            ->sum('fatura_nves.valor');

            $somaPendentesCreditoLoja += FaturaNfce::join('nfces', 'nfces.id', '=', 'fatura_nfces.nfce_id')
            ->where('nfces.empresa_id', $request->empresa_id)
            ->where('nfces.caixa_id', $item->id)
            ->where('fatura_nfces.tipo_pagamento', '05')
            ->sum('fatura_nfces.valor');

            $saldoAtual = $item->valor_abertura
            + $somaTiposPagamento['01']
            + $somaSuprimentos
            - $somaSangrias;

            $formasPagamento = [];
            foreach ($somaTiposPagamento as $codigo => $valor) {
                if ((float) $valor <= 0) {
                    continue;
                }

                $label = Nfce::tiposPagamento()[$codigo] ?? $codigo;
                $formasPagamento[$label] = (float) $valor;
            }

            $vendasFormatadas = $vendas->map(function ($v) {
                $pagamentos = [];

                if ($v->fatura) {
                    foreach ($v->fatura as $f) {
                        $codigo = $f->tipo_pagamento ?? null;
                        $label = \App\Models\Nfce::tiposPagamento()[$codigo] ?? 'Não identificado';

                        $pagamentos[] = [
                            'codigo' => $codigo,
                            'label' => $label,
                            'valor' => (float) ($f->valor ?? 0),
                        ];
                    }
                }

                return [
                    'id' => $v->id,
                    'tipo' => $v->tipo ?? '--',
                    'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                    'cliente' => $v->cliente->razao_social
                    ?? $v->cliente_nome
                    ?? $v->cliente->nome
                    ?? 'Consumidor final',
                    'total' => (float) $v->total,
                    'pagamentos' => $pagamentos,
                ];
            })->values();

            $suprimentosFormatados = $suprimentos->map(function ($s) {
                return [
                    'id' => $s->id,
                    'data' => \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i'),
                    'observacao' => $s->observacao ?: '--',
                    'valor' => (float) $s->valor,
                ];
            })->values();

            $sangriasFormatadas = $sangrias->map(function ($s) {
                return [
                    'id' => $s->id,
                    'data' => \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i'),
                    'observacao' => $s->observacao ?: '--',
                    'valor' => (float) $s->valor,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'caixa' => [
                    'id' => $item->id,
                    'numero' => 'Caixa #' . ($item->numero_sequencial ?: $item->id),
                    'operador' => $item->usuario->name ?? '--',
                    'data_abertura' => \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i'),
                    'status' => $item->status == 1 ? 'Aberto' : 'Fechado',
                    'observacao' => $item->observacao ?: '--',
                ],
                'resumo' => [
                    'valor_abertura' => (float) $item->valor_abertura,
                    'soma_vendas' => $somaVendas,
                    'soma_compras' => $somaCompras,
                    'soma_contas_receber' => $somaContasReceber,
                    'soma_contas_pagar' => $somaContasPagar,
                    'soma_os' => $somaOs ?: (float) $somaServicos,
                    'soma_suprimentos' => $somaSuprimentos,
                    'soma_sangrias' => $somaSangrias,
                    'soma_pendentes_crediario' => (float) $somaPendentesCrediario,
                    'soma_pendentes_boleto' => (float) $somaPendentBoleto,
                    'soma_pendentes_credito_loja' => (float) $somaPendentesCreditoLoja,
                    'trocas_pagas_por_cliente' => (float) ($trocasPagasPorCliente ?? 0),
                    'trocas_pagas_ao_cliente' => (float) ($trocasPagasAoCliente ?? 0),
                    'saldo_atual' => (float) $saldoAtual,
                ],
                'formas_pagamento' => $formasPagamento,
                'vendas' => $vendasFormatadas,
                'suprimentos' => $suprimentosFormatados,
                'sangrias' => $sangriasFormatadas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do caixa: ' . $e->getMessage()
            ], 500);
        }
    }

    private function somaTiposPagamento($vendas)
    {
        $tipos = $this->preparaTipos();

        foreach ($vendas as $v) {
            if ($v->estado == 'cancelado') continue;

            $trocoAplicado = false;

            if ($v->fatura && count($v->fatura) > 0) {
                foreach ($v->fatura as $f) {
                    $tipo = trim($f->tipo_pagamento);
                    if (!isset($tipos[$tipo])) {
                        continue; 
                    }
                    $valor = $f->valor;
                    if ($tipo == '01' && !$trocoAplicado) {

                        $valor -= $v->troco;
                        if ($valor < 0) {
                            $valor = 0;
                        }
                        $trocoAplicado = true;
                    }
                    $tipos[$tipo] += $valor;
                }
            }
        }
        return $tipos;
    }

    private function preparaTipos()
    {
        $temp = [];
        foreach (Nfce::tiposPagamento() as $key => $tp) {
            $temp[$key] = 0;
        }
        return $temp;
    }

    public function fechar(Request $request)
    {

        $item = Caixa::with('usuario')->findOrFail($request->caixa_id);

        try {
            DB::beginTransaction();

            $item->status = 0;
            $item->valor_fechamento = __convert_value_bd($request->valor_fechamento);

        // compatibilidade com campos antigos
            $item->valor_dinheiro = 0;
            $item->valor_cheque = 0;
            $item->valor_outros = 0;

            $observacaoAtual = trim((string) $item->observacao);
            $novaObservacao = trim((string) ($request->observacao ?? ''));
            $item->observacao = trim($observacaoAtual . ' ' . $novaObservacao);

            $item->data_fechamento = now();
            $item->save();

        // remove e recria as formas
            $item->formasPagamento()->delete();

            if ($request->has('pagamentos') && is_array($request->pagamentos)) {
                foreach ($request->pagamentos as $pagamento) {
                    $nome = $pagamento['nome'] ?? null;
                    $codigo = $pagamento['codigo'] ?? null;
                    $valorInformado = $pagamento['valor'] ?? 0;

                    $valor = $valorInformado ? __convert_value_bd($valorInformado) : 0;

                    if (!$nome || (float)$valor <= 0) {
                        continue;
                    }

                    $item->formasPagamento()->create([
                        'nome' => $nome,
                        'valor' => $valor,
                    ]);

                // compatibilidade com colunas antigas
                    if ($codigo === '01') {
                        $item->valor_dinheiro = (float) $valor;
                    } elseif ($codigo === '02') {
                        $item->valor_cheque = (float) $valor;
                    } else {
                        $item->valor_outros += (float) $valor;
                    }
                }
            }

            $item->save();

            $descricaoLog = $item->usuario->name .
            " | CAIXA FECHADO - abertura: " . __data_pt($item->created_at) .
            " - fechamento: " . __data_pt($item->data_fechamento);

            __createLog($request->empresa_id, 'Caixa', 'editar', $descricaoLog);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caixa fechado com sucesso!',
                'caixa_id' => $item->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            __createLog($request->empresa_id, 'Caixa', 'erro', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível fechar o caixa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
