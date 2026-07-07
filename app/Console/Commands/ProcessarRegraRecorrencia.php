<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecorrenciaRegraComunicacao;
use App\Models\RecorrenciaCobranca;
use App\Models\RecorrenciaComunicacaoLog;
use Carbon\Carbon;
use App\Utils\EmailUtil;
use App\Utils\WhatsAppUtil;

class ProcessarRegraRecorrencia extends Command
{
    protected $signature = 'recorrencia:processar-regra';
    protected $description = 'Processa régua de comunicação das cobranças recorrentes';
    protected $emailUtil;
    protected $whatsAppUtil;
    
    public function __construct(EmailUtil $emailUtil, WhatsAppUtil $whatsAppUtil)
    {
        parent::__construct();
        $this->emailUtil = $emailUtil;
        $this->whatsAppUtil = $whatsAppUtil;
    }

    public function handle()
    {
        $hoje = Carbon::now()->format('Y-m-d');

        $regras = RecorrenciaRegraComunicacao::where('ativo', 1)->get();

        foreach ($regras as $regra) {

            $cobrancas = RecorrenciaCobranca::where('empresa_id', $regra->empresa_id)
            ->with(['cliente', 'empresa'])
            ->whereNotIn('status', ['pago', 'cancelado'])
            ->where(function($q) use ($regra, $hoje){

                if($regra->gatilho == 'ao_gerar'){
                    $q->whereDate('created_at', $hoje);
                }

                if($regra->gatilho == 'antes_vencimento'){
                    $q->whereDate('data_vencimento', Carbon::now()->addDays($regra->dias)->format('Y-m-d'));
                }

                if($regra->gatilho == 'no_vencimento'){
                    $q->whereDate('data_vencimento', $hoje);
                }

                if($regra->gatilho == 'apos_vencimento'){
                    $q->whereDate('data_vencimento', Carbon::now()->subDays($regra->dias)->format('Y-m-d'));
                }
            })
            ->get();

            foreach($cobrancas as $cobranca){

                if($regra->email_ativo){
                    $this->enviarEmail($regra, $cobranca);
                }

                if($regra->whatsapp_ativo){
                    $this->enviarWhatsapp($regra, $cobranca);
                }
            }
        }

        $this->info('Régua processada com sucesso!');
    }

    private function enviarEmail($regra, $cobranca)
    {
        try {

            $cliente = $cobranca->cliente;

            if(!$cliente || !$cliente->email){
                return;
            }

            if(!$regra->mensagem_email){
                return;
            }

            $jaEnviado = RecorrenciaComunicacaoLog::where('recorrencia_cobranca_id', $cobranca->id)
            ->where('regra_id', $regra->id)
            ->where('canal', 'email')
            ->whereDate('data_referencia', now())
            ->exists();

            if($jaEnviado){
                return;
            }

            $assunto = $this->tratarMensagem($regra->assunto_email, $cobranca);
            $mensagem = $this->tratarMensagem($regra->mensagem_email, $cobranca);

            $retorno = $this->emailUtil->enviarCobrancaRecorrente(
                $cobranca->empresa_id,
                $cliente->email,
                $assunto,
                $mensagem
            );

            RecorrenciaComunicacaoLog::create([
                'empresa_id' => $cobranca->empresa_id,
                'recorrencia_cobranca_id' => $cobranca->id,
                'regra_id' => $regra->id,
                'canal' => 'email',
                'destino' => $cliente->email,
                'status' => isset($retorno['sucesso']) && $retorno['sucesso'] ? 'enviado' : 'erro',
                'erro' => $retorno['erro'] ?? null,
                'data_referencia' => now(),
                'enviado_em' => isset($retorno['sucesso']) && $retorno['sucesso'] ? now() : null
            ]);

        } catch (\Exception $e) {

            RecorrenciaComunicacaoLog::create([
                'empresa_id' => $cobranca->empresa_id,
                'recorrencia_cobranca_id' => $cobranca->id,
                'regra_id' => $regra->id,
                'canal' => 'email',
                'destino' => $cobranca->cliente->email ?? null,
                'status' => 'erro',
                'erro' => $e->getMessage(),
                'data_referencia' => now()
            ]);
        }
    }

    private function enviarWhatsapp($regra, $cobranca)
    {
        try {

            $cliente = $cobranca->cliente;

            if(!$cliente || !$cliente->telefone){
                return;
            }

            if(!$regra->mensagem_whatsapp){
                return;
            }

            $jaEnviado = RecorrenciaComunicacaoLog::where('recorrencia_cobranca_id', $cobranca->id)
            ->where('regra_id', $regra->id)
            ->where('canal', 'whatsapp')
            ->whereDate('data_referencia', now())
            ->exists();

            if($jaEnviado){
                return;
            }

            $mensagem = $this->tratarMensagem($regra->mensagem_whatsapp, $cobranca);

            $retorno = $this->whatsAppUtil->enviarCobrancaRecorrente(
                $cliente->telefone,
                $mensagem,
                $cobranca->empresa_id
            );

            RecorrenciaComunicacaoLog::create([
                'empresa_id' => $cobranca->empresa_id,
                'recorrencia_cobranca_id' => $cobranca->id,
                'regra_id' => $regra->id,
                'canal' => 'whatsapp',
                'destino' => $cliente->telefone,
                'status' => isset($retorno['sucesso']) && $retorno['sucesso'] ? 'enviado' : 'erro',
                'erro' => $retorno['erro'] ?? null,
                'data_referencia' => now()->format('Y-m-d'),
                'enviado_em' => isset($retorno['sucesso']) && $retorno['sucesso'] ? now() : null
            ]);

        } catch (\Exception $e) {

            RecorrenciaComunicacaoLog::create([
                'empresa_id' => $cobranca->empresa_id,
                'recorrencia_cobranca_id' => $cobranca->id,
                'regra_id' => $regra->id,
                'canal' => 'whatsapp',
                'destino' => $cobranca->cliente->telefone ?? null,
                'status' => 'erro',
                'erro' => $e->getMessage(),
                'data_referencia' => now()->format('Y-m-d'),
            ]);
        }
    }

    private function tratarMensagem($texto, $cobranca)
    {
        if(!$texto){
            return '';
        }

        return str_replace([
            '{{nome}}',
            '{{documento}}',
            '{{valor}}',
            '{{vencimento}}',
            '{{link_pagamento}}',
            '{{empresa}}'
        ], [
            $cobranca->cliente->razao_social ?? '',
            $cobranca->cliente->cpf_cnpj ?? '',
            __moeda($cobranca->valor),
            __data_pt($cobranca->data_vencimento, 0),
            $cobranca->link_pagamento ?? '',
            $cobranca->empresa->nome ?? ''
        ], $texto);
    }
}