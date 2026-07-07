<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoWhiteLabel extends Model
{

    protected $fillable = [
        'nome',
        'valor_mensal',
        'valor_por_empresa',
        'limite_empresas',
        'ativo'
    ];
}