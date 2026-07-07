<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuvemShopExecucao extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'config_id',
        'pedidos_processados',
        'pedidos_novos',
        'pedidos_atualizados',
        'ordens_separacao_criadas',
        'ordens_separacao_erro',
        'status',
        'mensagem',
        'iniciado_em',
        'finalizado_em'
    ];

    protected $casts = [
        'iniciado_em' => 'datetime',
        'finalizado_em' => 'datetime',
    ];
}
