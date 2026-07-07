<?php

namespace Database\Seeders;

use App\Models\PontoRegistro;
use App\Models\Funcionario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PontoRegistroSeeder extends Seeder
{
    public function run(): void
    {
        $funcionarios = Funcionario::whereNotNull('empresa_id')->get();

        foreach ($funcionarios as $funcionario) {

            for ($i = 0; $i < 40; $i++) {

                $data = now()->subDays($i);

                if ($data->isWeekend()) {
                    continue;
                }

                // $this->criarRegistro($funcionario, $data->copy()->setTime(8, rand(0, 10)), 'entrada');
                // $this->criarRegistro($funcionario, $data->copy()->setTime(12, 0), 'intervalo_inicio');
                // $this->criarRegistro($funcionario, $data->copy()->setTime(13, 0), 'intervalo_fim');
                // $this->criarRegistro($funcionario, $data->copy()->setTime(18, rand(0, 10)), 'saida');
            }
        }
    }

    private function criarRegistro($funcionario, Carbon $dataHora, string $tipo): void
    {
        PontoRegistro::create([
            'empresa_id' => $funcionario->empresa_id,
            'funcionario_id' => $funcionario->id,
            'data_hora' => $dataHora,
            'tipo' => $tipo,
            'ip' => '127.0.0.1',
            'device_id' => 'seed_device_' . $funcionario->id,
            'latitude' => -24.240892,
            'longitude' => -49.705356,
            'status' => 'valido',
            'hash_integridade' => hash('sha256', $funcionario->id . $dataHora . $tipo . Str::random(10)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}