<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PontoFuncionario extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'jornada_id',
        'funcionario_id',
        'data_inicio',
        'data_fim'
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function jornada()
    {
        return $this->belongsTo(PontoJornada::class, 'jornada_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}