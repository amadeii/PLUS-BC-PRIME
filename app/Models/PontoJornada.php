<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PontoJornadaDia;

class PontoJornada extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'descricao',
        'intervalo_minutos',
        'tolerancia_atraso',
        'hora_extra_apos_minutos',
        'ativo'
    ];

    public function dias()
    {
        return $this->hasMany(PontoJornadaDia::class, 'jornada_id');
    }
}