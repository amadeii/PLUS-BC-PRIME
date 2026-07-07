<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsaasConfig;

class AsaasConfigController extends Controller
{
    public function index(Request $request)
    {
        $item = AsaasConfig::where('empresa_id', $request->empresa_id)->first();
        return view('asaas_config.index', compact('item'));
    }

    public function store(Request $request)
    {
        $empresaId = $request->empresa_id;

        $config = AsaasConfig::where('empresa_id', $empresaId)->first();

        $data = $request->all();

        try {
            if ($config) {
                $config->update($data);
            } else {
                $config = AsaasConfig::create($data);
            }

            return redirect()->back()->with(
                'flash_success',
                'Configuração Asaas salva com sucesso!'
            );

        } catch (\Exception $e) {
            return redirect()->back()
            ->withInput()
            ->with('flash_error', $e->getMessage());
        }
    }
}
