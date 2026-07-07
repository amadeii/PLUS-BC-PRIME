<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecorrenciaComunicacaoLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'recorrencia_cobranca_id',
        'regra_id',
        'canal',
        'destino',
        'data_referencia',
        'status',
        'erro',
        'enviado_em',
    ];

    protected $casts = [
        'data_referencia' => 'date',
        'enviado_em' => 'datetime',
    ];

    public function cobranca()
    {
        return $this->belongsTo(RecorrenciaCobranca::class, 'recorrencia_cobranca_id');
    }

    public function regra()
    {
        return $this->belongsTo(RecorrenciaRegraComunicacao::class, 'regra_id');
    }
}