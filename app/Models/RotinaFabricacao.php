<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RotinaFabricacao extends Model
{
    protected $table = 'rotina_fabricacaos';

    protected $fillable = [
        'empresa_id',
        'produto_id',
        'imagem',
        'user_id',
        'lote_minimo',
        'instrucoes_especiais',
        'checklist_texto',
        'assinaturas',
    ];

    public function getImgAttribute()
    {
        if($this->imagem == ""){
            return "/imgs/no-image.png";
        }
        return "/uploads/rotina_fabricacao/$this->imagem";
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operacoes()
    {
        return $this->hasMany(RotinaFabricacaoOperacao::class, 'rotina_fabricacao_id');
    }
}