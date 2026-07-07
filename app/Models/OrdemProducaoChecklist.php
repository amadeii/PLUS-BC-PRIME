<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducaoChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id',
        'tipo',
        'status',
        'mensagem',
    ];

    public static function tipos()
    {
        return [
            'estrutura' => 'Estrutura',
            'roteiro' => 'Roteiro',
            'estoque' => 'Estoque',
            'custos' => 'Custos',
        ];
    }

    public static function status()
    {
        return [
            'ok' => 'OK',
            'alerta' => 'Alerta',
            'erro' => 'Erro',
        ];
    }
}