<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TefLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id',
        'tef_session_id',
        'tef_terminal_id',
        'tef_store_id',
        'tef_clisitef_status',
        'tef_function_id',
        'tef_controle',
        'tef_sitef_ip',

        'tef_nsu',
        'tef_codigo_autorizacao',
        'tef_bandeira',
        'tef_adquirente',
        'tef_raw',
        'comprovantes',
        'empresa_id',
        'cancelado'
    ];

    protected $casts = [
        'comprovantes' => 'array',
    ];

    public function venda(){
        return $this->belongsTo(VendaCaixa::class, 'venda_id');
    }
}
