<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoRefugo extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nome',
        'ativo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}