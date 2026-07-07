<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducaoApontamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id',
        'item_ordem_producao_id',
        'ordem_producao_operacao_id',
        'funcionario_id',
        'motivo_refugo_id',
        'data_inicio',
        'data_fim',
        'tempo_real_minutos',
        'quantidade_produzida',
        'quantidade_refugada',
        'observacao',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public static function status()
    {
        return [
            'aberto' => 'Aberto',
            'finalizado' => 'Finalizado',
        ];
    }

    public function ordemProducao()
    {
        return $this->belongsTo(OrdemProducao::class, 'ordem_producao_id');
    }

    public function itemOrdemProducao()
    {
        return $this->belongsTo(ItemOrdemProducao::class, 'item_ordem_producao_id');
    }

    public function ordemProducaoOperacao()
    {
        return $this->belongsTo(OrdemProducaoOperacao::class, 'ordem_producao_operacao_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function motivoRefugo()
    {
        return $this->belongsTo(MotivoRefugo::class, 'motivo_refugo_id');
    }
}