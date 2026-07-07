<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfeDivergencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nfe_id',
        'tipo',
        'produto',
        'status',
        'valor_xml',
        'valor_compra',
        'quantidade_xml',
        'quantidade_compra',
        'vencimento_xml',
        'vencimento_compra'
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }
}
