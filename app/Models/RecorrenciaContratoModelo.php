<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecorrenciaContratoModelo extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'nome',
        'conteudo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}