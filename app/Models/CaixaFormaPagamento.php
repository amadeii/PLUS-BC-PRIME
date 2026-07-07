<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaixaFormaPagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'caixa_id',
        'nome',
        'valor',
    ];

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
}
