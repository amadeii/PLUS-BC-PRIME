<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrancaBancaria extends Model
{
    use HasFactory;

    protected $table = 'cobranca_bancarias';

    protected $fillable = [
        'empresa_id',
        'conta_receber_id',
        'cliente_id',

        'banco',
        'status_banco',

        'nosso_numero',
        'seu_numero',
        'numero_boleto',
        'codigo_barras',
        'linha_digitavel',

        'valor',
        'valor_recebido',
        'valor_multa',
        'valor_juros',
        'valor_desconto',

        'data_emissao',
        'data_vencimento',
        'data_pagamento',
        'data_baixa',

        'url_pdf',
        'url_boleto',
        'pdf_base64',

        'payload_envio',
        'payload_retorno',
        'mensagem_erro',
        'ultima_consulta_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'valor_multa' => 'decimal:2',
        'valor_juros' => 'decimal:2',
        'valor_desconto' => 'decimal:2',

        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'data_baixa' => 'date',

        'payload_envio' => 'array',
        'payload_retorno' => 'array',
        'ultima_consulta_em' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function contaReceber()
    {
        return $this->belongsTo(ContaReceber::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

}