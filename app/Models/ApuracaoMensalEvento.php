<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApuracaoMensalEvento extends Model
{
    use HasFactory;

    protected $fillable = [
        'apuracao_id',
        'evento_id',
        'valor',

        'valor_base',
        'valor_calculado',
        'quantidade_referencia',
        'tipo_referencia',
        'calculado_automaticamente',

        'metodo',
        'condicao',
        'nome',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_base' => 'decimal:2',
        'valor_calculado' => 'decimal:2',
        'quantidade_referencia' => 'decimal:2',
        'calculado_automaticamente' => 'boolean',
    ];

    public function evento()
    {
        return $this->belongsTo(EventoSalario::class, 'evento_id');
    }

    public function apuracao()
    {
        return $this->belongsTo(ApuracaoMensal::class, 'apuracao_id');
    }
}