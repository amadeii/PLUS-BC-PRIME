<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuvemShopConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'client_id',
        'client_secret',
        'store_id',
        'access_token',
        'user_id_nuvemshop',
        'scope',
        'autenticado',
        'token_gerado_em',
        'ultimo_sync',
        'email',
        'cron_para_separacao'
    ];
}
