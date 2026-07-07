<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsaasConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'token', 'sandbox', 'ultimo_numero_boleto', 'juros_padrao', 'multa_padrao', 'observacao_padrao',
        'status'
    ];
}
