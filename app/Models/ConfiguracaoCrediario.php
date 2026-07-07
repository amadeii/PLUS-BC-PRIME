<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoCrediario extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'valor_minimo',
        'valor_maximo',
        'maximo_parcelas',
        'parcelas_sem_juros',
        'juros_percentual',
        'primeiro_vencimento_dias',
        'intervalo_parcelas_dias',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}