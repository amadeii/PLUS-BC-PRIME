<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SicrediConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'x_api_key', 'codigo_beneficiario', 'cooperativa', 'posto', 'username', 'password', 'tipo_cobranca', 'especie_documento', 
        'ultimo_numero_boleto', 'access_token', 'token_expires_at', 'juros_padrao', 'multa_padrao', 'observacao_padrao', 'status'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];
}
