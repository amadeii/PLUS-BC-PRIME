<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuprimentoCaixa;
use App\Models\SangriaCaixa;
use App\Models\Nfce;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Caixa;

class CaixaController extends Controller
{
    public function salvarSuprimento(Request $request){
        try {

            DB::beginTransaction();

            $caixa = Caixa::where('usuario_id', $request->usuario_id)
            ->where('status', 1)
            ->first();

            if (!$caixa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum caixa aberto para este usuário'
                ], 400);
            }

            $suprimento = SuprimentoCaixa::create([
                'caixa_id' => $caixa->id, 
                'valor' => $request->valor, 
                'observacao' => $request->observacao ?? '', 
                'conta_empresa_id' => null, 
                'tipo_pagamento' => $request->tipo_pagamento_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Suprimento salvo com sucesso'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar suprimento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function salvarSangria(Request $request){
        try {

            DB::beginTransaction();

            $caixa = Caixa::where('usuario_id', $request->usuario_id)
            ->where('status', 1)
            ->first();

            if (!$caixa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum caixa aberto para este usuário'
                ], 400);
            }

            $suprimento = SangriaCaixa::create([
                'caixa_id' => $caixa->id, 
                'valor' => $request->valor, 
                'observacao' => $request->observacao ?? '', 
                'conta_empresa_id' => null, 
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sangria salva com sucesso'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar sangria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verificarCaixa(Request $request)
    {
        try {

            $caixa = Caixa::where('usuario_id', $request->usuario_id)
            ->where('status', 1)
            ->first();

            if (!$caixa) {
                return response()->json([
                    'caixa_aberto' => false,
                    'caixa_id' => null
                ], 200);
            }

            return response()->json([
                'caixa_aberto' => true,
                'caixa_id' => $caixa->id
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'caixa_aberto' => false,
                'caixa_id' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function vendasDoCaixa(Request $request)
    {
        try {

            $caixa = Caixa::where('usuario_id', $request->usuario_id)
            ->where('status', 1)
            ->first();


            if (!$caixa) {
                return response()->json([
                    'message' => 'Nenhum caixa aberto encontrado.'
                ], 404);
            }

            $vendas = Nfce::with('cliente')
            ->withCount('itens')
            ->where('caixa_id', $caixa->id)
            ->where('empresa_id', $caixa->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

            $lista = $vendas->map(function ($v) {
                return [
                    'id' => $v->id,
                    'numero_sequencial' => $v->numero_sequencial,
                    'cliente' => $v->cliente->razao_social ?? 'Consumidor Final',
                    'total' => (float) $v->total,
                    'total_itens' => $v->itens_count,
                    'estado' => $v->estado,
                    'created_at' => $v->created_at->format('d/m/Y H:i')
                ];
            });

            return response()->json([
                'data_caixa' => $caixa->created_at->format('d/m/Y'),
                'valor_abertura' => (float) $caixa->valor_abertura,
                'vendas' => $lista
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Erro ao buscar vendas do caixa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function venda($id)
    {
        try {

            $venda = Nfce::with([
                'cliente',
                'itens.produto',
                'itens.adicionais',
                'fatura'
            ])->findOrFail($id);

            return response()->json([
                'cliente' => optional($venda->cliente)->razao_social,
                'total' => (float) $venda->total,
                'numero_sequencial' => $venda->numero_sequencial,
                'desconto' => (float) $venda->desconto,
                'acrescimo' => (float) $venda->acrescimo,
                'estado' => $venda->estado,
                'id' => $venda->id,
                'data' => $venda->created_at->format('d/m/Y H:i:s'),

                'itens' => $venda->itens->map(function ($item) {

                    return [
                        'produto' => optional($item->produto)->nome,
                        'quantidade' => (int) $item->quantidade,
                        'valor_unitario' => (float) $item->valor_unitario,
                        'subtotal' => (float) $item->sub_total,
                        'observacao' => $item->observacao,

                        'adicionais' => $item->adicionais->map(function ($adicional) {
                            return [
                                'nome' => $adicional->adicional->nome,
                                'valor' => (float) $adicional->adicional->valor
                            ];
                        })
                    ];
                }),

                'fatura' => $venda->fatura->map(function ($fatura) {

                    $tipoPagamento = Nfce::getTipoPagamento($fatura->tipo_pagamento);
                    if($tipoPagamento == 'Pagamento Instantâneo (PIX)'){
                        $tipoPagamento = 'PIX';
                    }
                    return [
                        'tipo_pagamento' => $tipoPagamento,
                        'valor' => (float) $fatura->valor,
                        'vencimento' => optional($fatura->vencimento)->format('Y-m-d')
                    ];
                })

            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Erro ao buscar venda',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function getLastNumeroSequencial($empresa_id){
        $last = Caixa::where('empresa_id', $empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)->first();
        $numero = $last != null ? $last->numero_sequencial : 0;
        $numero++;
        return $numero;
    }

    public function abrirCaixa(Request $request){
        try {

            $data = [
                'usuario_id' => $request->usuario_id,
                'valor_abertura' => $request->valor_inicial,
                'observacao' => $request->observacao ?? '',
                'status' => 1,
                'valor_fechamento' => 0,
                'numero_sequencial' => $this->getLastNumeroSequencial($request->empresa_id)
            ];

            $caixa = Caixa::create($data);
            return response()->json([
                'caixa_aberto' => true,
                'caixa_id' => $caixa->id
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'caixa_aberto' => false,
                'caixa_id' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
