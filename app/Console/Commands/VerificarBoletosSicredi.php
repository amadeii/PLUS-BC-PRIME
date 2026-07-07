<?php

namespace App\Console\Commands;

use App\Models\CobrancaBancaria;
use App\Models\SicrediConfig;
use App\Utils\SicrediUtil;
use App\Utils\CronLogUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarBoletosSicredi extends Command
{
    protected $signature = 'boletos:verificar-sicredi';
    protected $description = 'Verifica boletos no Sicredi';

    public function handle()
    {
        $agora = now();
        $umaHoraAtras = $agora->copy()->subHour();
        $seisHorasAtras = $agora->copy()->subHours(6);

        $configs = SicrediConfig::where('status', 1)->get();

        foreach ($configs as $c) {

            CronLogUtil::registrar([
                'comando' => 'boletos:verificar-sicredi',
                'origem' => 'sicredi',
                'empresa_id' => $c->empresa_id,
                'status' => 'INFO',
                'mensagem' => 'Início execução da empresa'
            ]);

            $boletos = CobrancaBancaria::query()
                ->where('banco', 'sicredi')
                ->where('empresa_id', $c->empresa_id)
                ->whereIn('status_banco', ['PENDENTE', 'GERADO', 'REGISTRADO', 'VENCIDO', 'EM CARTEIRA'])
                ->where(function ($q) use ($umaHoraAtras, $seisHorasAtras) {
                    $q->whereNull('ultima_consulta_em')
                        ->orWhere(function ($sub) use ($umaHoraAtras) {
                            $sub->whereIn('status_banco', ['PENDENTE', 'GERADO', 'REGISTRADO', 'EM CARTEIRA'])
                                ->where('ultima_consulta_em', '<=', $umaHoraAtras);
                        })
                        ->orWhere(function ($sub) use ($seisHorasAtras) {
                            $sub->where('status_banco', 'VENCIDO')
                                ->where('ultima_consulta_em', '<=', $seisHorasAtras);
                        });
                })
                ->limit(100)
                ->get();

            if ($boletos->isEmpty()) {
                CronLogUtil::registrar([
                    'comando' => 'boletos:verificar-sicredi',
                    'origem' => 'sicredi',
                    'empresa_id' => $c->empresa_id,
                    'status' => 'INFO',
                    'mensagem' => 'Nenhum boleto pendente para consulta nesta empresa'
                ]);

                continue;
            }

            foreach ($boletos as $boleto) {
                try {
                    $retorno = SicrediUtil::consultarBoleto(
                        $boleto->nosso_numero,
                        $boleto->empresa_id
                    );

                    $statusBanco = null;
                    $novoStatus = null;
                    $dadosBoleto = null;

                    DB::transaction(function () use ($boleto, $retorno, &$statusBanco, &$novoStatus, &$dadosBoleto) {
                        $dadosBoleto = $retorno['boletos'][0] ?? $retorno;

                        $statusBanco = $dadosBoleto['situacao'] ?? $boleto->status_banco;
                        $novoStatus = $this->mapearStatus($statusBanco);

                        $dados = [
                            'status_banco' => $statusBanco,
                            'payload_retorno' => $retorno,
                            'ultima_consulta_em' => now(),
                            'tentativas_consulta' => ($boleto->tentativas_consulta ?? 0) + 1,
                            'mensagem_erro' => null,
                        ];

                        if ($novoStatus !== $boleto->status) {
                            $dados['status'] = $novoStatus;

                            if ($novoStatus === 'PAGO') {
                                $dados['data_pagamento'] = $dadosBoleto['dataLiquidacao'] ?? now();
                                $dados['valor_recebido'] = $dadosBoleto['valorLiquidado'] ?? $boleto->valor;
                            }
                        }

                        $boleto->update($dados);

                        if ($novoStatus === 'PAGO' && $boleto->getOriginal('status') !== 'PAGO') {
                            $this->baixarFinanceiro($boleto->fresh());
                        }
                    });

                    CronLogUtil::registrar([
                        'comando' => 'boletos:verificar-sicredi',
                        'origem' => 'sicredi',
                        'empresa_id' => $boleto->empresa_id,
                        'boleto_id' => $boleto->id,
                        'status' => 'SUCESSO',
                        'mensagem' => "Status atualizado para {$statusBanco}",
                        'payload' => $retorno
                    ]);
                } catch (\Throwable $e) {
                    $boleto->update([
                        'ultima_consulta_em' => now(),
                        'tentativas_consulta' => ($boleto->tentativas_consulta ?? 0) + 1,
                        'mensagem_erro' => $e->getMessage(),
                    ]);

                    CronLogUtil::registrar([
                        'comando' => 'boletos:verificar-sicredi',
                        'origem' => 'sicredi',
                        'empresa_id' => $boleto->empresa_id,
                        'boleto_id' => $boleto->id,
                        'status' => 'ERRO',
                        'mensagem' => $e->getMessage(),
                    ]);

                    Log::error('Erro consultar boleto Sicredi', [
                        'empresa_id' => $boleto->empresa_id,
                        'boleto_id' => $boleto->id,
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            CronLogUtil::registrar([
                'comando' => 'boletos:verificar-sicredi',
                'origem' => 'sicredi',
                'empresa_id' => $c->empresa_id,
                'status' => 'INFO',
                'mensagem' => 'Fim execução da empresa'
            ]);
        }

        return self::SUCCESS;
    }

    private function mapearStatus($statusBanco)
    {
        return match ($statusBanco) {
            'LIQUIDADO' => 'PAGO',
            'BAIXADO' => 'BAIXADO',
            'CANCELADO' => 'CANCELADO',
            default => 'PENDENTE',
        };
    }

    private function baixarFinanceiro($boleto)
    {
        $conta = $boleto->contaReceber;

        if (!$conta || $conta->status == 'PAGO' || $conta->status == 1) {
            return;
        }

        $conta->update([
            'status' => 1,
            'data_recebimento' => $boleto->data_pagamento,
            'valor_recebido' => $boleto->valor_recebido ?? $boleto->valor
        ]);
    }
}