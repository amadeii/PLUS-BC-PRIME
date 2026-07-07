<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PontoAjuste extends Model
{
    use HasFactory;

    protected $fillable = [
        'ponto_registro_id',
        'usuario_id',
        'motivo',
        'justificativa',
        'antes_json',
        'depois_json',
    ];

    protected $casts = [
        'antes_json' => 'array',
        'depois_json' => 'array',
    ];

    public function registro()
    {
        return $this->belongsTo(PontoRegistro::class, 'ponto_registro_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}