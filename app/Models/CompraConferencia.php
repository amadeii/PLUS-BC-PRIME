<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraConferencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'compra_id', 'user_id', 'status', 'observacao', 'conferido_em'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function compra()
    {
        return $this->belongsTo(Nfe::class, 'compra_id');
    }

    public function itens()
    {
        return $this->hasMany(CompraConferenciaItem::class, 'compra_conferencia_id');
    }
}
