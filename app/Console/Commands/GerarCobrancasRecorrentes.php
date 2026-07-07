<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recorrencia;
use App\Models\RecorrenciaCobranca;
use Carbon\Carbon;

class GerarCobrancasRecorrentes extends Command
{
    protected $signature = 'recorrencias:gerar-cobrancas';

    protected $description = 'Gera cobranças recorrentes automaticamente no primeiro dia do mês';

    public function handle()
    {
        $inicioMes = now()->startOfMonth()->toDateString();
        $fimMes = now()->endOfMonth()->toDateString();

        $recorrencias = Recorrencia::with('cliente')
        ->where('status', 'ativa')
        ->where('gerar_automatico', 1)
        ->whereNotNull('proxima_cobranca')
        ->whereBetween('proxima_cobranca', [$inicioMes, $fimMes])
        ->where(function($q){
            $q->whereNull('data_fim')
            ->orWhereDate('data_fim', '>=', now()->toDateString());
        })
        ->get();

        foreach($recorrencias as $recorrencia){

            $jaExisteNoMes = RecorrenciaCobranca::where('recorrencia_id', $recorrencia->id)
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->exists();

            if($jaExisteNoMes){
                $this->warn("Cobrança já existe neste mês para recorrência #{$recorrencia->id}");
                continue;
            }

            $cobranca = RecorrenciaCobranca::create([
                'empresa_id' => $recorrencia->empresa_id,
                'recorrencia_id' => $recorrencia->id,
                'cliente_id' => $recorrencia->cliente_id,
                'data_vencimento' => $recorrencia->proxima_cobranca,
                'valor' => $recorrencia->valor,
                'forma_pagamento' => $recorrencia->forma_pagamento,
                'status' => 'pendente',
                'observacao' => $recorrencia->observacao,
            ]);

            if($recorrencia->enviar_email && optional($recorrencia->cliente)->email){
            // Mail::to($recorrencia->cliente->email)->send(new RecorrenciaCobrancaMail($cobranca));
                $this->info("E-mail marcado para envio da cobrança #{$cobranca->id}");
            }

            $recorrencia->proxima_cobranca = $this->proximaData($recorrencia);
            $recorrencia->save();

            $this->info("Cobrança #{$cobranca->id} gerada para recorrência #{$recorrencia->id}");
        }

        return Command::SUCCESS;
    }

    private function proximaData($recorrencia)
    {
        $data = Carbon::parse($recorrencia->proxima_cobranca);

        if($recorrencia->periodicidade == 'mensal'){
            return $data->addMonth();
        }

        if($recorrencia->periodicidade == 'bimestral'){
            return $data->addMonths(2);
        }

        if($recorrencia->periodicidade == 'trimestral'){
            return $data->addMonths(3);
        }

        if($recorrencia->periodicidade == 'semestral'){
            return $data->addMonths(6);
        }

        if($recorrencia->periodicidade == 'anual'){
            return $data->addYear();
        }

        return $data->addMonth();
    }
}