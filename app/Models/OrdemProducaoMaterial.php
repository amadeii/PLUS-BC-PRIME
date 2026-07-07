<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducaoMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id',
        'item_ordem_producao_id',
        'ordem_producao_operacao_id',
        'produto_id',
        'material_id',
        'quantidade_prevista',
        'quantidade_real',
        'quantidade_perda',
        'unidade',
        'custo_unitario',
        'custo_total_previsto',
        'custo_total_real',
        'status_estoque',
        'observacao',
    ];

    public static function statusEstoque()
    {
        return [
            'ok' => 'OK',
            'insuficiente' => 'Insuficiente',
            'sem_estoque' => 'Sem estoque',
        ];
    }

    public function material()
    {
        return $this->belongsTo(Produto::class, 'material_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function ordemProducao()
    {
        return $this->belongsTo(OrdemProducao::class, 'ordem_producao_id');
    }

    public function itemOrdemProducao()
    {
        return $this->belongsTo(ItemOrdemProducao::class, 'item_ordem_producao_id');
    }

    public function operacao()
    {
        return $this->belongsTo(OrdemProducaoOperacao::class, 'ordem_producao_operacao_id');
    }
}