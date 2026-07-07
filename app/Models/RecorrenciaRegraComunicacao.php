<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecorrenciaRegraComunicacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'nome',
        'email_ativo',
        'whatsapp_ativo',
        'gatilho',
        'dias',
        'assunto_email',
        'mensagem_email',
        'mensagem_whatsapp',
        'ativo',
    ];

    protected $casts = [
        'email_ativo' => 'boolean',
        'whatsapp_ativo' => 'boolean',
        'ativo' => 'boolean',
    ];
}