<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApuracaoMensal extends Model
{
    use HasFactory;

    protected $table = 'apuracao_mensals';

    protected $fillable = [
        'funcionario_id',
        'mes',
        'ano',
        'valor_final',
        'forma_pagamento',
        'observacao',
        'conta_pagar_id',

        // RESUMO DO PONTO
        'horas_previstas',
        'horas_trabalhadas',
        'horas_extras',
        'horas_faltas',
        'horas_atrasos',
        'horas_saida_antecipada',
        'saldo_horas',
        'saldo_minutos',

        'faltas',
        'dias_com_ponto',
        'dias_com_extra',
        'dias_incompletos',
    ];

    protected $casts = [
        'valor_final' => 'decimal:2',
        'ano' => 'integer',
        'conta_pagar_id' => 'integer',

        'saldo_minutos' => 'integer',
        'faltas' => 'integer',
        'dias_com_ponto' => 'integer',
        'dias_com_extra' => 'integer',
        'dias_incompletos' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function eventos()
    {
        return $this->hasMany(ApuracaoMensalEvento::class, 'apuracao_id');
    }

    public function pontos()
    {
        return $this->hasMany(ApuracaoMensalPonto::class, 'apuracao_id');
    }

    public function contaPagar()
    {
        return $this->belongsTo(ContaPagar::class, 'conta_pagar_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getSaldoFormatadoAttribute()
    {
        if ($this->saldo_minutos > 0) {
            return '+' . $this->saldo_horas;
        }

        if ($this->saldo_minutos < 0) {
            return '-' . $this->saldo_horas;
        }

        return '00:00';
    }

    public function getValorFormatadoAttribute()
    {
        return number_format($this->valor_final, 2, ',', '.');
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC
    |--------------------------------------------------------------------------
    */

    public static function tiposPagamento()
    {
        return [
            'Dinheiro' => 'Dinheiro',
            'Cheque' => 'Cheque',
            'Boleto' => 'Boleto',
            'Depósito Bancário' => 'Depósito Bancário',
            'Pix' => 'Pix',
            'Outros' => 'Outros'
        ];
    }

    public static function mesesApuracao()
    {
        return [
            'janeiro' => 'Janeiro',
            'fevereiro' => 'Fevereiro',
            'março' => 'Março',
            'abril' => 'Abril',
            'maio' => 'Maio',
            'junho' => 'Junho',
            'julho' => 'Julho',
            'agosto' => 'Agosto',
            'setembro' => 'Setembro',
            'outubro' => 'Outubro',
            'novembro' => 'Novembro',
            'dezembro' => 'Dezembro',
        ];
    }

    public static function anosApuracao()
    {
        $anos = [];
        $a = date('Y');

        for ($i = $a; $i < $a + 20; $i++) {
            $anos[] = $i;
        }

        return $anos;
    }
}