<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recorrencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'descricao',
        'valor',
        'periodicidade',
        'dia_vencimento',
        'data_inicio',
        'proxima_cobranca',
        'data_fim',
        'forma_pagamento',
        'gerar_automatico',
        'enviar_whatsapp',
        'enviar_email',
        'status',
        'observacao'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_inicio' => 'date',
        'proxima_cobranca' => 'date',
        'data_fim' => 'date',
        'gerar_automatico' => 'boolean',
        'enviar_whatsapp' => 'boolean',
        'enviar_email' => 'boolean',
        'gera_nfse' => 'boolean',
        'gera_nfe' => 'boolean'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servicos()
    {
        return $this->hasMany(RecorrenciaServico::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(RecorrenciaCobranca::class);
    }

    public function cobrancasPendentes()
    {
        return $this->hasMany(RecorrenciaCobranca::class)->where('status', 'pendente');
    }

    public function cobrancasPagas()
    {
        return $this->hasMany(RecorrenciaCobranca::class)->where('status', 'pago');
    }
}