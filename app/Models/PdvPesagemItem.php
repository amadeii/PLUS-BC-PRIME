<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvPesagemItem extends Model
{
    protected $fillable = [
        'empresa_id',
        'produto_id',
        'status',
        'ordem',
        'valor',
        'sem_peso'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}