<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducaoHistorico extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id',
        'usuario_id',
        'acao',
        'status_anterior',
        'status_novo',
        'observacao',
    ];

    public static function acoes()
    {
        return [
            'criacao' => 'Criação',
            'liberacao' => 'Liberação',
            'inicio' => 'Início Produção',
            'apontamento' => 'Apontamento',
            'finalizacao' => 'Finalização',
            'encerramento' => 'Encerramento',
            'reabertura' => 'Reabertura',
        ];
    }

    public function ordemProducao()
    {
        return $this->belongsTo(OrdemProducao::class, 'ordem_producao_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}