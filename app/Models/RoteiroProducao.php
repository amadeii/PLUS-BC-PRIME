<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoteiroProducao extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'produto_id',
        'nome',
        'descricao',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function itens()
    {
        return $this->hasMany(RoteiroProducaoItem::class, 'roteiro_producao_id')
            ->orderBy('sequencia');
    }

    public function operacoes()
    {
        return $this->hasMany(RoteiroProducaoItem::class, 'roteiro_producao_id');
    }
}