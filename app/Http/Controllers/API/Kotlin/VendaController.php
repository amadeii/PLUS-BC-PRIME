<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigGeral;
use App\Models\Nfce;
use App\Models\ItemNfce;
use App\Models\ItemAdicionalNfce;
use App\Models\FaturaNfce;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\Caixa;
use App\Models\UsuarioEmissao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendaController extends Controller
{
    public function tiposPagamento(Request $request)
    {
        $tiposPagamento = Nfce::tiposPagamento();

        $config = ConfigGeral::where('empresa_id', $request->empresa_id)->first();

        if ($config && $config->tipos_pagamento_pdv) {

            $permitidos = json_decode($config->tipos_pagamento_pdv, true) ?? [];

            if (!empty($permitidos)) {
                $tiposPagamento = collect($tiposPagamento)
                ->filter(function ($nome) use ($permitidos) {
                    return in_array($nome, $permitidos);
                })
                ->toArray();
            }
        }

        $data = collect($tiposPagamento)->map(function ($nome, $codigo) {
            return [
                'codigo' => (string) $codigo,
                'nome'   => $nome == 'Pagamento Instantâneo (PIX)' ? 'PIX' : $nome
            ];
        })->values();

        return response()->json($data);
    }

    private function getLastNumero($empresa_id){
        $last = Nfce::where('empresa_id', $empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)->first();
        $numero = $last != null ? $last->numero_sequencial : 0;
        $numero++;
        return $numero;
    }

    public function salvarVenda(Request $request)
    {

        $empresaId = $request->empresa_id ?? null;
        if (!$empresaId) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não identificada na sessão.',
            ], 401);
        }


        $desconto  = (float) ($request->desconto ?? 0);
        $acrescimo = (float) ($request->acrescimo ?? 0);

        $documento = $request->filled('documento')
        ? preg_replace('/\D/', '', (string)$request->documento)
        : null;

        $dataPagamento = null;
        if ($request->filled('data_pagamento')) {
            $dp = $request->data_pagamento;
            $parts = explode('/', $dp);
            if (count($parts) === 3) {
                $dataPagamento = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }

        $valorProdutos = collect($request->itens)->sum(function ($i) {
            return (float)$i['quantidade'] * (float)$i['valor_unitario'];
        });

        $totalCalculado = $valorProdutos - $desconto + $acrescimo;

        $totalPago = collect($request->pagamentos)->sum(function ($p) {
            return (float)$p['valor'];
        });

        if (abs($totalCalculado - (float)$request->total) > 0.05) {
            return response()->json([
                'success' => false,
                'message' => 'Total divergente. Recalcule a venda.',
                'debug'   => [
                    'valor_produtos' => round($valorProdutos, 2),
                    'desconto'       => round($desconto, 2),
                    'acrescimo'      => round($acrescimo, 2),
                    'total_calc'     => round($totalCalculado, 2),
                    'total_payload'  => round((float)$request->total, 2),
                ]
            ], 422);
        }

        if ($totalPago + 0.01 < $totalCalculado) {
            return response()->json([
                'success' => false,
                'message' => 'Pagamento insuficiente para concluir.',
            ], 422);
        }


        $troco = max(0, $totalPago - $totalCalculado);

        try {
            DB::beginTransaction();

            $empresa = $config = Empresa::find($empresaId);

            $caixa = Caixa::where('usuario_id', $request->usuario_id)
            ->where('status', 1)
            ->first();

            $config = __objetoParaEmissao($config, $caixa->local_id);
            $numero_nfce = $config->numero_ultima_nfce_producao;
            if ($config->ambiente == 2) {
                $numero_nfce = $config->numero_ultima_nfce_homologacao;
            }

            $numeroSerieNfce = $config->numero_serie_nfce ? $config->numero_serie_nfce : 1;
            $configUsuarioEmissao = UsuarioEmissao::where('usuario_empresas.empresa_id', $empresaId)
            ->join('usuario_empresas', 'usuario_empresas.usuario_id', '=', 'usuario_emissaos.usuario_id')
            ->select('usuario_emissaos.*')
            ->where('usuario_emissaos.usuario_id', $request->usuario_id)
            ->first();

            if($configUsuarioEmissao != null){
                $numeroSerieNfce = $configUsuarioEmissao->numero_serie_nfce;
                $numero_nfce = $configUsuarioEmissao->numero_ultima_nfce;
            }
            if(isset($request->pagamentos)){
                $tipoPagamentoPrincipal = sizeof($request->pagamentos) > 1 ? '99' : $request->pagamentos[0]['tipo_pagamento'];
            }

            $nfce = Nfce::create([
                'natureza_id' => $empresa->natureza_id_pdv,
                'emissor_nome' => $config->nome,
                'emissor_cpf_cnpj' => $config->cpf_cnpj,
                'ambiente' => $config->ambiente,
                'numero_serie' => $numeroSerieNfce,
                'numero' => $numero_nfce + 1,

                'empresa_id' => $empresaId,
                'cliente_id' => $request->cliente_id,
                'total' => $totalCalculado,
                'desconto' => $desconto,
                'acrescimo' => $acrescimo,
                'observacao' => $request->observacao,
                'user_id' => $request->usuario_id,
                'estado' => 'novo',
                'troco' => $troco,

                'tipo_pagamento' => $tipoPagamentoPrincipal,

                'caixa_id' => $caixa->id,
                'local_id' => $caixa->local_id,
                'numero_sequencial' => $this->getLastNumero($empresaId)

            ]);

            if($request->pedido_id != null){
                $pedido = Pedido::find($request->pedido_id);
                $pedido->status = 0;
                $pedido->em_atendimento = 0;
                $pedido->nfce_id = $nfce->id;

                $mesa = $pedido->_mesa;
                if($mesa){
                    $mesa->ocupada = 0;
                    $mesa->save();
                }

                $pedido->save();
            }

            foreach ($request->itens as $i) {
                $qtd = (float)$i['quantidade'];
                $vu  = (float)$i['valor_unitario'];
                $vt  = $qtd * $vu;

                $produto = Produto::findOrFail((int)$i['produto_id']);
                $produto = __tributacaoProdutoLocalVenda($produto, $caixa->local_id);

                $itemNfce = ItemNfce::create([
                    'nfce_id' => $nfce->id,
                    'produto_id' => (int)$i['produto_id'],
                    'quantidade' => $qtd,
                    'valor_unitario'=> $vu,
                    'sub_total' => $vt,
                    'observacao' => $i['observacao'] ?? null,

                    'perc_icms' => __convert_value_bd($produto->perc_icms),
                    'perc_pis' => __convert_value_bd($produto->perc_pis),
                    'perc_cofins' => __convert_value_bd($produto->perc_cofins),
                    'perc_ipi' => __convert_value_bd($produto->perc_ipi),
                    'cst_csosn' => $produto->cst_csosn,
                    'cst_pis' => $produto->cst_pis,
                    'cst_cofins' => $produto->cst_cofins,
                    'cst_ipi' => $produto->cst_ipi,
                    'cfop' => $produto->cfop_estadual,
                    'ncm' => $produto->ncm,
                ]);

                //ItemAdicionalNfce

                if (!empty($i['adicionais'])) {
                    foreach ($i['adicionais'] as $adicionalId) {
                        ItemAdicionalNfce::create([
                            'item_nfce_id' => $itemNfce->id,
                            'adicional_id' => $adicionalId
                        ]);
                    }
                }
            }

            foreach ($request->pagamentos as $p) {
                FaturaNfce::create([
                    'nfce_id' => $nfce->id,
                    'tipo_pagamento' => $p['tipo_pagamento'],
                    'valor' => (float)$p['valor'],
                    'data_vencimento'=> $p['data_pagamento'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $p['data_pagamento'])
                    ->format('Y-m-d') : date('Y-m-d'),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'id'      => $nfce->id,
                'message' => 'Venda salva com sucesso',
                'troco'   => round($troco, 2),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar venda',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
