<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoFinalizacaoParcial extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'pedido_id',
        'nfce_id',
        'valor_pago',
        'saldo_antes',
        'saldo_depois',
        'cpf_nota',
        'observacao',
        'status'
    ];

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }

    public function nfce(){
        return $this->belongsTo(Nfce::class);
    }

    public function itens()
    {
        return $this->hasMany(PedidoFinalizacaoParcialItem::class, 'pedido_finalizacao_parcial_id');
    }

}