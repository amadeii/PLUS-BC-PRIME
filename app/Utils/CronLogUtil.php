<?php

namespace App\Utils;

use App\Models\CronLog;

class CronLogUtil
{
    public static function registrar($dados)
    {
        CronLog::create([
            'comando' => $dados['comando'] ?? null,
            'origem' => $dados['origem'] ?? null,
            'empresa_id' => $dados['empresa_id'] ?? null,
            'boleto_id' => $dados['boleto_id'] ?? null,
            'status' => $dados['status'] ?? 'INFO',
            'mensagem' => $dados['mensagem'] ?? null,
            'payload' => $dados['payload'] ?? null,
            'executado_em' => now(),
        ]);
    }
}