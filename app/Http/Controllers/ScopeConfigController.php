<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScopeConfig;
use App\Models\SitefConfig;

class ScopeConfigController extends Controller
{

    public function index(Request $request)
    {
        $usuario_id = \Auth::user()->id;

        $config = ScopeConfig::where('empresa_id', $request->empresa_id)
        ->where('usuario_id', $usuario_id)
        ->first();

        return view('config_scope.index')
        ->with('config', $config)
        ->with('usuario_id', $usuario_id);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'habilitado' => 'required|boolean',
                'agente_ip' => 'nullable|string|max:50',
                'agente_porta' => 'nullable|integer|min:1|max:65535',
                'usuario_id' => 'nullable|integer',
            ]);

            // Garante empresa_id e usuario_id
            $data['empresa_id'] = $request->empresa_id;
            if (empty($data['usuario_id'])) {
                $data['usuario_id'] = get_id_user();
            }

            // Valores padrão se não informados
            if (empty($data['agente_ip'])) {
                $data['agente_ip'] = '127.0.0.1';
            }
            if (empty($data['agente_porta'])) {
                $data['agente_porta'] = 8000;
            }

            // Atualiza ou cria configuração
            $config = ScopeConfig::updateOrCreate(
                ['empresa_id' => $request->empresa_id],
                $data
            );

            if($data['habilitado']){
                SitefConfig::where('empresa_id', $request->empresa_id)
                ->update(['habilitado' => 0]);
            }

            session()->flash('flash_success', 'Configuração TEF salva com sucesso!');
            return redirect()->back();

        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao salvar configuração: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
