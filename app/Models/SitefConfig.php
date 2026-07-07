<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitefConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'habilitado',
        'sitef_ip',
        'store_id',
        'terminal_id',
        'agente_porta',
        'agente_ip',
    ];

    protected $casts = [
        'habilitado' => 'boolean',
        'agente_porta' => 'integer',
    ];

    /**
     * Obtém a configuração TEF da empresa (e opcionalmente por usuário)
     */
    public static function getConfig($empresa_id, $usuario_id = null)
    {
        $query = self::where('empresa_id', $empresa_id);
        
        // Se informado usuario_id, busca por usuário específico primeiro
        if ($usuario_id) {
            $configUsuario = $query->where('usuario_id', $usuario_id)->first();
            if ($configUsuario) {
                return $configUsuario;
            }
        }
        
        // Caso contrário, retorna configuração geral da empresa (usuario_id NULL)
        return $query->whereNull('usuario_id')->first();
    }

    /**
     * Verifica se TEF está habilitado para a empresa
     */
    public static function isHabilitado($empresa_id)
    {
        $config = self::getConfig($empresa_id);
        return $config && $config->habilitado;
    }

    /**
     * Retorna configuração formatada para o JavaScript
     */
    public function toJsConfig()
    {
        return [
            'habilitado' => $this->habilitado,
            'sitefIp' => $this->sitef_ip,
            'storeId' => $this->store_id,
            'terminalId' => $this->terminal_id,
            // 'agenteUrl' => 'https://' . $this->agente_ip . ':' . $this->agente_porta,
            'agenteUrl' => 'https://' . $this->agente_ip,
        ];
    }

    /**
     * Relacionamento com empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
