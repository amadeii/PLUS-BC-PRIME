<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaRegistro extends Model
{
    protected $table = 'auditoria_registros';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'entidade',
        'registro_id',
        'acao',
        'antes_json',
        'depois_json',
        'alteracoes_json',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'antes_json' => 'array',
        'depois_json' => 'array',
        'alteracoes_json' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }
}