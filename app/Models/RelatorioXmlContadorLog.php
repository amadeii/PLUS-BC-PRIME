<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioXmlContadorLog extends Model
{
    protected $fillable = [
        'empresa_id',
        'email_contador',
        'competencia',
        'total_nfe_aprovada',
        'total_nfe_cancelada',
        'total_nfce_aprovada',
        'total_nfce_cancelada',
        'arquivo_zip_nfe',
        'arquivo_zip_nfce',
        'arquivo_pdf',
        'status',
        'mensagem',
        'enviado_em'
    ];

    protected $casts = [
        'competencia' => 'date',
        'enviado_em' => 'datetime',
        'total_nfe_aprovada' => 'integer',
        'total_nfe_cancelada' => 'integer',
        'total_nfce_aprovada' => 'integer',
        'total_nfce_cancelada' => 'integer'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status == 'sucesso' ? 'Sucesso' : 'Erro';
    }

    public function getStatusClassAttribute()
    {
        return $this->status == 'sucesso' ? 'success' : 'danger';
    }
}