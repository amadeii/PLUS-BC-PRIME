<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PontoConfiguracao extends Model
{
    use HasFactory;

    protected $table = 'ponto_configuracaos';

    protected $fillable = [
        'empresa_id',
        'latitude',
        'longitude',
        'raio_permitido',
        'permitir_fora_area',
        'exigir_observacao_fora_area',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'raio_permitido' => 'integer',
        'permitir_fora_area' => 'boolean',
        'exigir_observacao_fora_area' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function temGeolocalizacao()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function exigeGeolocalizacao()
    {
        return $this->temGeolocalizacao();
    }

    public function dentroDoRaio($lat, $lng)
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return true;
        }

        $earthRadius = 6371000;

        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->latitude)) *
            cos(deg2rad($lat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distancia = $earthRadius * $c;

        return $distancia <= $this->raio_permitido;
    }
}