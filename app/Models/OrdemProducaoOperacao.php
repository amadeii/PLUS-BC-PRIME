<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducaoOperacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id',
        'item_ordem_producao_id',
        'operacao_id',
        'setor_id',
        'sequencia',
        'nome_operacao',
        'nome_setor',
        'tempo_previsto_minutos',
        'tempo_real_minutos',
        'quantidade_prevista',
        'quantidade_produzida',
        'quantidade_refugada',
        'status',
        'data_inicio',
        'data_fim',
        'observacao',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public static function status()
    {
        return [
            'pendente' => 'Pendente',
            'em_producao' => 'Em Produção',
            'parcial' => 'Parcial',
            'finalizada' => 'Finalizada',
        ];
    }

    public function ordemProducao()
    {
        return $this->belongsTo(OrdemProducao::class, 'ordem_producao_id');
    }

    public function apontamentos()
    {
        return $this->hasMany(OrdemProducaoApontamento::class, 'ordem_producao_operacao_id');
    }
    
    public function itemOrdemProducao()
    {
        return $this->belongsTo(ItemOrdemProducao::class, 'item_ordem_producao_id');
    }

    public function operacao()
    {
        return $this->belongsTo(Operacao::class, 'operacao_id');
    }

    public function setor()
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }
}