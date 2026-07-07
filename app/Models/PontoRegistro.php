<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PontoRegistro extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'data_hora',
        'tipo',
        'ip',
        'device_id',
        'latitude',
        'longitude',
        'status',
        'hash_integridade',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function ajustes()
    {
        return $this->hasMany(PontoAjuste::class, 'ponto_registro_id');
    }
}