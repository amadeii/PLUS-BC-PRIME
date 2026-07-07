<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecorrenciaServico extends Model
{
    use HasFactory;

    protected $fillable = [
        'recorrencia_id',
        'servico_id',
        'quantidade',
        'valor_unitario',
        'subtotal'
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function recorrencia()
    {
        return $this->belongsTo(Recorrencia::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}