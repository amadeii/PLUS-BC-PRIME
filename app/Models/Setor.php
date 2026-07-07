<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nome',
        'descricao',
        'horas_dia',
        'custo_hora',
        'eficiencia',
        'centro_custo_id'
    ];

    protected $casts = [
        'horas_dia' => 'decimal:2',
        'custo_hora' => 'decimal:2',
        'eficiencia' => 'decimal:2',
    ];


    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function centroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }

    public function operacoes()
    {
        return $this->hasMany(Operacao::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    // Custo por minuto
    public function getCustoMinutoAttribute()
    {
        return $this->custo_hora / 60;
    }

    // Capacidade diária em minutos
    public function getCapacidadeMinutosAttribute()
    {
        return ($this->horas_dia * 60) * ($this->eficiencia / 100);
    }
}