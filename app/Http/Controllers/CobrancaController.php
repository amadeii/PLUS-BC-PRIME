<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CobrancaBancaria;
use App\Models\CronLog;
use Illuminate\Http\Request;
use App\Utils\SicrediUtil;
use App\Utils\AsaasUtil;

class CobrancaController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = request()->empresa_id ?? session('empresa_id');

        $query = CobrancaBancaria::with(['cliente', 'contaReceber'])
        ->where('empresa_id', $empresa_id);

        $cliente = null;

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
            $cliente = Cliente::find($request->cliente_id);
        }

        if ($request->filled('banco')) {
            $query->where('banco', $request->banco);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('data_vencimento', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('data_vencimento', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            if ($request->status == 'pago') {
                $query->whereNotNull('data_pagamento');
            } elseif ($request->status == 'pendente') {
                $query->whereNull('data_pagamento')
                ->where(function($q){
                    $q->whereNull('data_vencimento')
                    ->orWhereDate('data_vencimento', '>=', now()->toDateString());
                });
            } elseif ($request->status == 'vencido') {
                $query->whereNull('data_pagamento')
                ->whereDate('data_vencimento', '<', now()->toDateString());
            } elseif ($request->status == 'erro') {
                $query->whereNotNull('mensagem_erro');
            }
        }

        $data = $query->orderByDesc('id')->paginate(20)->appends($request->all());
        $contasBancarias = __getBancosAtivos($request->empresa_id);
        return view('cobrancas.index', compact('data', 'cliente', 'contasBancarias'));
    }

    public function show($id)
    {
        $item = CobrancaBancaria::findOrFail($id);
        return view('cobrancas.show', compact('item'));

    }

    public function verBoleto($id)
    {
        $cobranca = CobrancaBancaria::findOrFail($id);
        switch (strtolower($cobranca->banco)) {
            case 'sicredi':
            $pdf = SicrediUtil::imprimirBoleto(
                $cobranca->linha_digitavel,
                $cobranca->empresa_id
            );
            break;

            case 'asaas':
            $res = AsaasUtil::consultarBoleto(
                $cobranca->nosso_numero,
                $cobranca->empresa_id
            );

            $url = $res['bankSlipUrl'] ?? null;

            if (!$url) {
                throw new \Exception('Boleto Asaas não possui URL de impressão.');
            }

            $response = \Illuminate\Support\Facades\Http::get($url);

            if (!$response->successful()) {
                throw new \Exception('Erro ao baixar PDF do boleto Asaas.');
            }

            $pdf = $response->body();
            break;

            default:
            throw new \Exception('Banco não suportado: ' . $cobranca->banco);
        }

        return response($pdf)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="boleto_'.$cobranca->id.'.pdf"');
    }

    public function consultarStatus($id)
    {
        try {
            $cobranca = CobrancaBancaria::findOrFail($id);

            $status = null;
            $dataPagamento = null;
            $valorRecebido = null;
            $retorno = [];

            switch (strtolower($cobranca->banco)) {
                case 'sicredi':
                $retorno = SicrediUtil::consultarBoleto(
                    $cobranca->nosso_numero,
                    $cobranca->empresa_id
                );

                $status = $retorno['situacao'] ?? $retorno['status'] ?? null;
                $dataPagamento = $retorno['dataLiquidacao'] ?? $retorno['dataPagamento'] ?? null;
                $valorRecebido = $retorno['valorLiquidado'] ?? $retorno['valorPago'] ?? null;
                break;

                case 'asaas':
                $retorno = AsaasUtil::consultarBoleto(
                    $cobranca->nosso_numero,
                    $cobranca->empresa_id
                );

                $statusAsaas = $retorno['status'] ?? null;

                $status = match ($statusAsaas) {
                    'PENDING' => 'PENDENTE',
                    'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'PAGO',
                    'OVERDUE' => 'VENCIDO',
                    'CANCELLED' => 'CANCELADO',
                    'REFUNDED' => 'ESTORNADO',
                    default => $statusAsaas,
                };

                $dataPagamento = $retorno['clientPaymentDate']
                ?? $retorno['paymentDate']
                ?? null;

                $valorRecebido = $retorno['value'] ?? null;

                $cobranca->update([
                    'status' => $status,
                    'status_banco' => $statusAsaas,
                    'data_pagamento' => $dataPagamento,
                    'valor_recebido' => $valorRecebido,
                    'ultima_consulta_em' => now(),
                    'retorno' => $retorno,
                ]);

                if ($status === 'PAGO') {
                    $this->baixarFinanceiro($cobranca);
                }

                break;

                default:
                throw new \Exception('Banco não suportado');
            }

            $cobranca->status_banco = $status;
            $cobranca->payload_retorno = json_encode($retorno);

            if (!empty($dataPagamento)) {
                $cobranca->data_pagamento = $dataPagamento;
            }

            if (!empty($valorRecebido) && in_array($status, ['PAGO'])) {
                $cobranca->valor_recebido = $valorRecebido;
            }

            $cobranca->ultima_consulta_em = now();

            $cobranca->save();

            return response()->json([
                'success' => true,
                'status' => $status,
                'retorno' => $retorno
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    private function baixarFinanceiro($boleto)
    {
        $conta = $boleto->contaReceber;

        if (!$conta) {
            return;
        }

        if ($conta->status == 'PAGO' || $conta->status == 1) {
            return;
        }

        $conta->update([
            'status' => 1,
            'data_recebimento' => $boleto->data_pagamento ?? now()->toDateString(),
            'valor_recebido' => $boleto->valor_recebido ?? $boleto->valor,
        ]);
    }

    public function baixarTitulo($id)
    {
        try {
            $item = CobrancaBancaria::findOrFail($id);

            if (!$item->nosso_numero) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Cobrança sem nosso número.'
                ], 422);
            }

            if (!empty($item->data_pagamento)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Título já está pago, não pode ser baixado.'
                ], 422);
            }

            if (
                $item->status === 'BAIXADO' ||
                in_array($item->status_banco, [
                    'BAIXADO',
                    'BAIXADO POR SOLICITACAO',
                    'CANCELADO'
                ])
            ) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Título já foi baixado/cancelado.'
                ], 422);
            }

            $retorno = $this->baixarTituloNoBanco($item);

            $item->update([
                'status_banco' => $this->mapearStatusBaixaBanco($item->banco),
                'ultima_consulta_em' => now(),
                'payload_retorno' => $retorno,
                'mensagem_erro' => null,
            ]);

            return response()->json([
                'success' => true,
                'msg' => 'Título baixado com sucesso.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    private function baixarTituloNoBanco(CobrancaBancaria $item)
    {
        $banco = strtolower(trim($item->banco ?? ''));

        return match ($banco) {
            'sicredi' => SicrediUtil::baixarBoleto(
                $item->nosso_numero,
                $item->empresa_id
            ),

            'asaas' => AsaasUtil::baixarBoleto(
                $item->nosso_numero,
                $item->empresa_id
            ),

            default => throw new \Exception("Banco não suportado para baixa: {$item->banco}")
        };
    }

    private function mapearStatusBaixaBanco(?string $banco): string
    {
        $banco = strtolower(trim($banco ?? ''));

        return match ($banco) {
            'sicredi' => 'BAIXADO POR SOLICITACAO',
            default => 'BAIXADO'
        };
    }

    public function logs(Request $request)
    {
        $data = \App\Models\CronLog::where('empresa_id', $request->empresa_id)

        ->when(!empty($request->status), function ($q) use ($request) {
            return $q->where('status', $request->status);
        })

        ->when(!empty($request->origem), function ($q) use ($request) {
            return $q->where('origem', $request->origem);
        })

        ->when(!empty($request->start_date), function ($q) use ($request) {
            return $q->whereDate('executado_em', '>=', $request->start_date);
        })

        ->when(!empty($request->end_date), function ($q) use ($request) {
            return $q->whereDate('executado_em', '<=', $request->end_date);
        })

        ->orderBy('executado_em', 'desc')
        ->paginate(__itensPagina());

        $contasBancarias = __getBancosAtivos($request->empresa_id);

        return view('cobrancas.logs', compact('data', 'contasBancarias'));
    }

}