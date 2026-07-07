<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComandaPesoItem extends Model
{
    protected $fillable = [
        'empresa_id',
        'produto_id',
        'ordem',
        'ativo'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class);
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class);
    }
}