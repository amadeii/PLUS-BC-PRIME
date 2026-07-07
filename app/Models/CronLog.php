<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $fillable = [
        'comando',
        'origem',
        'empresa_id',
        'boleto_id',
        'status',
        'mensagem',
        'payload',
        'executado_em'
    ];

    protected $casts = [
        'payload' => 'array',
        'executado_em' => 'datetime',
    ];
}