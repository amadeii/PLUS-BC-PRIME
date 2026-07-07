<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operacao extends Model
{
    use HasFactory;

    protected $table = 'operacaos';

    protected $fillable = [
        'empresa_id',
        'setor_id',
        'nome',
        'descricao',
        'tempo_padrao',
        'codigo'
    ];


    // Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Setor
    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    // Operações vinculadas à rotina de fabricação
    public function rotinas()
    {
        return $this->hasMany(ProdutoRotinaOperacao::class, 'operacao_id');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    // Tempo em horas
    public function getTempoHorasAttribute()
    {
        return $this->tempo_padrao / 60;
    }

    // Custo da operação baseado no custo hora do setor
    public function getCustoOperacaoAttribute()
    {
        if (!$this->setor) {
            return 0;
        }

        return ($this->tempo_padrao / 60) * $this->setor->custo_hora;
    }
}