<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RotinaFabricacaoOperacao extends Model
{
    protected $table = 'rotina_fabricacao_operacaos';

    protected $fillable = [
        'rotina_fabricacao_id',
        'sequencia',
        'operacao_id',
        'setor_id',
        'centro_custo_id',
        'descricao',
        'tempo_minutos',
        'setup_minutos',
    ];

    public function rotinaFabricacao()
    {
        return $this->belongsTo(RotinaFabricacao::class, 'rotina_fabricacao_id');
    }

    public function operacao()
    {
        return $this->belongsTo(Operacao::class, 'operacao_id');
    }

    public function setor()
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }

    public function centroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }
}