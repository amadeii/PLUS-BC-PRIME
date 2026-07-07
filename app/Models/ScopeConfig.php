<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScopeConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'habilitado',
        'agente_porta',
        'agente_ip',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    
}
