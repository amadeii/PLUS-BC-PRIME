<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVendaTemporaria extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id', 'quantidade', 'valor', 'produto_id'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
