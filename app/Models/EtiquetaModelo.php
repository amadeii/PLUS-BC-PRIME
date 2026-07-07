<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtiquetaModelo extends Model
{
    protected $fillable = [
        'empresa_id',
        'nome',
        'largura',
        'altura',
        'etiquetas_por_linha',
        'fonte_padrao',
        'espaco_horizontal',
        'espaco_vertical',
        'layout_json',
        'ativo',
        'mostrar_numero_codigo_barras',
        'largura_codigo_barras',
        'altura_codigo_barras',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'largura' => 'float',
        'altura' => 'float'
    ];

    public function getLayoutAttribute()
    {
        return $this->layout_json ? json_decode($this->layout_json, true) : [];
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}