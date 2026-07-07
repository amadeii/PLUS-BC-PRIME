<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoPagamentoPadrao extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',

        'nome_pagador',
        'documento_pagador',
        'valor_transporte',
        'indicador_pagamento',

        'tipo_componente',
        'valor_componente',
        'descricao',

        'valor_parcela',
        'vencimento',

        'codigo_banco',
        'codigo_agencia',
        'cnpj_iof',
    ];
}
