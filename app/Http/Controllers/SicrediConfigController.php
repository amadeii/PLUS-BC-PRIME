<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SicrediConfig;
use App\Utils\SicrediUtil;

class SicrediConfigController extends Controller
{
    public function index(Request $request)
    {
        $item = SicrediConfig::where('empresa_id', $request->empresa_id)->first();
        return view('sicredi_config.index', compact('item'));
    }

    public function store(Request $request)
    {
        $empresaId = $request->empresa_id;

        $config = SicrediConfig::where('empresa_id', $empresaId)->first();

        $data = [
            'empresa_id' => $request->empresa_id,
            'x_api_key' => $request->x_api_key,
            'codigo_beneficiario' => $request->codigo_beneficiario,
            'cooperativa' => $request->cooperativa,
            'posto' => $request->posto,
            'password' => $request->password,
            'username' => $request->username,
            'tipo_cobranca' => $request->tipo_cobranca,
            'especie_documento' => $request->especie_documento,
            'ultimo_numero_boleto' => $request->ultimo_numero_boleto,
            'observacao_padrao' => $request->observacao_padrao,
            'juros_padrao' => $request->juros_padrao,
            'multa_padrao' => $request->multa_padrao,
            'status' => $request->status,
        ];

        try {
            if ($config) {
                $config->update($data);
            } else {
                $config = SicrediConfig::create($data);
            }

            $tokenData = SicrediUtil::gerarToken($empresaId);

            $config->update([
                'access_token' => $tokenData['access_token'],
                'token_expires_at' => $tokenData['token_expires_at'],
            ]);

            return redirect()->back()->with(
                'flash_success',
                'Configuração Sicredi salva e token validado com sucesso!'
            );
        } catch (\Exception $e) {
            return redirect()->back()
            ->withInput()
            ->with('flash_error', $e->getMessage());
        }
    }
}
