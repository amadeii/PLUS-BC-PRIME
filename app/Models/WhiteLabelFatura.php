<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhiteLabelFatura extends Model
{
    protected $fillable = [
        'contador_id',
        'plano_white_label_id',
        'competencia',
        'vencimento',
        'valor_mensal',
        'valor_por_empresa',
        'total_empresas',
        'valor_total_empresas',
        'valor_total',
        'dias_carencia',
        'data_limite_bloqueio',
        'status',
        'data_pagamento',
        'valor_pago',
        'forma_pagamento',
        'referencia_pagamento',
        'observacao'
    ];

    public function contador()
    {
        return $this->belongsTo(Empresa::class, 'contador_id');
    }

    public function plano()
    {
        return $this->belongsTo(PlanoWhiteLabel::class, 'plano_white_label_id');
    }
}