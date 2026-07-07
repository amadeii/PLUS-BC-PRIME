<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraConferenciaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_conferencia_id', 'item_compra_id', 'qtd_xml', 'qtd_conferida', 'diferenca', 'observacao'
    ];

    public function conferencia()
    {
        return $this->belongsTo(CompraConferencia::class, 'compra_conferencia_id');
    }
}
