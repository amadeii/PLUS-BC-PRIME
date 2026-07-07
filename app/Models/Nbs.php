<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nbs extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo', 'descricao'
    ];

    protected $appends = [ 'info' ];

    public function getInfoAttribute()
    {
        return "[$this->codigo] $this->descricao";
    }
}
