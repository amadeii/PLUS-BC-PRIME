<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducaoCusto extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id',
        'item_ordem_producao_id',
        'tipo',
        'descricao',
        'valor_previsto',
        'valor_real',
        'observacao',
    ];

    public static function tipos()
    {
        return [
            'material' => 'Material',
            'mao_obra' => 'Mão de obra',
            'processo' => 'Processo',
            'terceiro' => 'Terceiro',
            'outros' => 'Outros',
        ];
    }
}