<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemProducao extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'usuario_id',
        'usuario_liberou_id',
        'usuario_encerrou_id',
        'observacao',
        'estado',
        'data_prevista_entrega',
        'codigo_sequencial',
        'hash_link',
        'nfe_id',
        'orcamento_id',
        'prioridade',
        'tipo_producao',
        'data_liberacao',
        'data_inicio',
        'data_finalizacao',
        'data_encerramento',
        'estrutura_ok',
        'roteiro_ok',
        'estoque_ok',
        'custos_ok',
        'quantidade_produzida',
        'quantidade_refugada',
        'quantidade_pendente',
        'percentual_progresso',
        'ultima_operacao_id',
        'custo_material',
        'custo_mao_obra',
        'custo_processo',
        'custo_total',
    ];

    protected $casts = [
        'data_prevista_entrega' => 'date',
        'data_liberacao' => 'datetime',
        'data_inicio' => 'datetime',
        'data_finalizacao' => 'datetime',
        'data_encerramento' => 'datetime',
        'estrutura_ok' => 'boolean',
        'roteiro_ok' => 'boolean',
        'estoque_ok' => 'boolean',
        'custos_ok' => 'boolean',
        'quantidade_produzida' => 'decimal:3',
        'quantidade_refugada' => 'decimal:3',
        'quantidade_pendente' => 'decimal:3',
        'percentual_progresso' => 'decimal:2',
    ];

    public function itens()
    {
        return $this->hasMany(ItemOrdemProducao::class, 'ordem_producao_id');
    }

    public static function estados()
    {
        return [
            'novo' => 'Novo',
            'liberada' => 'Liberada',
            'producao' => 'Em produção',
            'parcial' => 'Parcial',
            'finalizada' => 'Finalizada',
            'encerrada' => 'Encerrada',
            'expedicao' => 'Expedição',
            'entregue' => 'Entregue',
            'cancelada' => 'Cancelada',
        ];
    }

    public function operacoes()
    {
        return $this->hasMany(OrdemProducaoOperacao::class, 'ordem_producao_id')
        ->orderBy('sequencia');
    }

    public function materiais()
    {
        return $this->hasMany(OrdemProducaoMaterial::class, 'ordem_producao_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function usuarioLiberou()
    {
        return $this->belongsTo(User::class, 'usuario_liberou_id');
    }

    public function usuarioEncerrou()
    {
        return $this->belongsTo(User::class, 'usuario_encerrou_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function imagens()
    {
        return $this->hasMany(ImagemOrdemProducao::class);
    }

    public function apontamentos()
    {
        return $this->hasMany(OrdemProducaoApontamento::class, 'ordem_producao_id');
    }

    public static function prioridades()
    {
        return [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'urgente' => 'Urgente'
        ];
    }

    public static function tiposProducao()
    {
        return [
            'producao' => 'Produção',
            'retrabalho' => 'Retrabalho',
            'amostra' => 'Amostra'
        ];
    }

    public function getEstadoTextoAttribute()
    {
        return self::estados()[$this->estado] ?? $this->estado;
    }

    public function getPrioridadeTextoAttribute()
    {
        return self::prioridades()[$this->prioridade] ?? $this->prioridade;
    }

    public function getTipoProducaoTextoAttribute()
    {
        return self::tiposProducao()[$this->tipo_producao] ?? $this->tipo_producao;
    }

    public function getEstaLiberadaAttribute()
    {
        return !is_null($this->data_liberacao);
    }

    public function getEstaIniciadaAttribute()
    {
        return !is_null($this->data_inicio);
    }

    public function getEstaFinalizadaAttribute()
    {
        return !is_null($this->data_finalizacao);
    }

    public function getEstaEncerradaAttribute()
    {
        return !is_null($this->data_encerramento);
    }
}