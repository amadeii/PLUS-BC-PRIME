<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioXmlContadorConfig extends Model
{
    protected $fillable = [
        'empresa_id',
        'ativo',
        'dia_envio',
        'email_contador',
        'mensagem_email'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'dia_envio' => 'integer'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}