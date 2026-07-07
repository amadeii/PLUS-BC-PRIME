<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoFinalizacaoParcialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_finalizacao_parcial_id',
        'pedido_id',
        'item_pedido_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'sub_total',
    ];

    public function finalizacao()
    {
        return $this->belongsTo(PedidoFinalizacaoParcial::class, 'pedido_finalizacao_parcial_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function itemPedido()
    {
        return $this->belongsTo(ItemPedido::class, 'item_pedido_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}