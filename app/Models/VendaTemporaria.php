<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaTemporaria extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id', 'cliente_id', 'tabela', 'estado', 'total', 'empresa_id', 'venda_vinculada'
    ];

    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function vendaVinculada(){
        if($this->tabela == 'pdv'){
            return $this->belongsTo(Nfce::class, 'venda_vinculada');
        }else{
            return $this->belongsTo(Nfe::class, 'venda_vinculada');
        }
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(){
        return $this->hasMany(ItemVendaTemporaria::class, 'venda_id')->with('produto');
    }

    public function itensRemovidos(){
        return $this->hasMany(ItemVendaTemporariaRemovido::class, 'venda_id');
    }

    public static function estados(){
        return [
            'em_aberto' => 'Em aberto',
            'abandonada' => 'Abandonada',
            'finalizada' => 'Finalizada'
        ];
    }
}
