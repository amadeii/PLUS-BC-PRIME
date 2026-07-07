<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApuracaoMensalPonto extends Model
{
    protected $fillable = [
        'apuracao_id',
        'data',
        'dia_semana',
        'entrada',
        'intervalo_inicio',
        'intervalo_fim',
        'saida',
        'horas_previstas',
        'horas_trabalhadas',
        'horas_extras',
        'horas_faltas',
        'atraso',
        'saida_antecipada',
        'status',
    ];

    public function apuracao()
    {
        return $this->belongsTo(ApuracaoMensal::class, 'apuracao_id');
    }
}
