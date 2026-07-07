<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagemOrdemProducao extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_producao_id', 'imagem'
    ];

    public function getImgAttribute()
    {
        if($this->imagem == ""){
            return "/imgs/no-image.png";
        }
        return "/uploads/ordem_producao/$this->imagem";
    }
}
