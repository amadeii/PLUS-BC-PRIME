<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecorrenciaCobranca extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'recorrencia_id',
        'cliente_id',
        'conta_receber_id',
        'data_vencimento',
        'valor',
        'status',
        'forma_pagamento',
        'asaas_id',
        'asaas_invoice_url',
        'pix_payload',
        'pix_qrcode',
        'pago_em',
        'cancelado_em',
        'nfe_id',
        'nfse_id',
        'observacao'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'valor' => 'decimal:2',
        'pago_em' => 'datetime',
        'cancelado_em' => 'datetime'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function recorrencia()
    {
        return $this->belongsTo(Recorrencia::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function contaReceber()
    {
        return $this->belongsTo(ContaReceber::class);
    }

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }

    public function nfse()
    {
        return $this->belongsTo(Nfse::class);
    }
}