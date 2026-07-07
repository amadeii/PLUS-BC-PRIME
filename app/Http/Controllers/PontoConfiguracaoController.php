<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PontoConfiguracao;
use App\Models\ConfiguracaoSuper;

class PontoConfiguracaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ponto_configuracao_view', ['only' => ['index']]);
        $this->middleware('permission:ponto_configuracao_create', ['only' => ['store']]);
    }

    public function index(Request $request)
    {
        $config = PontoConfiguracao::firstOrCreate(
            ['empresa_id' => $request->empresa_id],
            $this->defaultConfig($request->empresa_id)
        );
        $configSuper = ConfiguracaoSuper::first();
        $tokenMaps = null;
        if($configSuper){
            $tokenMaps = $configSuper->token_maps;
        }
        return view('ponto_configuracao.index', compact('config', 'tokenMaps'));
    }

    public function store(Request $request)
    {

        try{
            PontoConfiguracao::updateOrCreate(
                ['empresa_id' => $request->empresa_id],
                [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'raio_permitido' => $request->raio_permitido,
                    'permitir_fora_area' => $request->permitir_fora_area,
                    'exigir_observacao_fora_area' => $request->exigir_observacao_fora_area,
                ]
            );

            return redirect()->route('ponto-configuracao.index', ['empresa_id' => $request->empresa_id])
            ->with('flash_success', 'Configurações salvas com sucesso!');
        }catch(\Exception $e){

        }
    }

    private function defaultConfig($empresaId)
    {
        return [
            'empresa_id' => $empresaId,
            'latitude' => null,
            'longitude' => null,
            'raio_permitido' => 100,
            'permitir_fora_area' => false,
            'exigir_observacao_fora_area' => true,
        ];
    }
}