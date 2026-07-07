<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoteiroProducaoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'roteiro_producao_id',
        'operacao_id',
        'setor_id',
        'sequencia',
        'nome_operacao',
        'nome_setor',
        'tempo_previsto_minutos',
        'observacao'
    ];

    public function roteiro()
    {
        return $this->belongsTo(RoteiroProducao::class, 'roteiro_producao_id');
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