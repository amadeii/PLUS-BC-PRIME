<?php

namespace App\Http\Controllers\API\APP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\Empresa;
use App\Models\PlanoEmpresa;
use App\Models\ConfiguracaoSuper;
use App\Models\Plano;
use App\Models\FinanceiroPlano;

class FinanceiroController extends Controller
{
    public function verificaPlano(Request $request)
    {
        $empresa = Empresa::find($request->empresa_id);

        if (!$empresa) {
            return response()->json([
                'expirado' => false
            ]);
        }

        $plano = PlanoEmpresa::where('empresa_id', $empresa->id)
        ->orderBy('id', 'desc')
        ->first();

        if (!$plano) {
            return response()->json([
                'expirado' => true,
                'mensagem' => 'Empresa sem plano'
            ]);
        }

        $valor = $plano->valor;
        if($plano->plano){
            $valor = $plano->plano->valor;
        }

        $expirado = now()->startOfDay()->gt(
            \Carbon\Carbon::parse($plano->data_expiracao)->startOfDay()
        );

        return response()->json([
            'expirado' => $expirado,
            'empresa_id' => $empresa->id,
            'data_expiracao' => $plano->data_expiracao,
            'valor' => $valor,
            'wpp' => env("APP_FONE"),
            'forma_pagamento' => $plano->forma_pagamento
        ]);
    }

    public function gerarPix(Request $request)
    {
        $empresa = Empresa::find($request->empresa_id);

        if (!$empresa) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Empresa não encontrada'
            ], 404);
        }

        $planoEmpresa = PlanoEmpresa::where('empresa_id', $empresa->id)
        ->orderBy('id', 'desc')
        ->first();

        if (!$planoEmpresa) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Plano não encontrado'
            ], 404);
        }

        $config = ConfiguracaoSuper::first();

        if (!$config || !$config->asaas_token) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Token Asaas não configurado'
            ], 400);
        }

        $client = new \GuzzleHttp\Client();

        $endPoint = 'https://api-sandbox.asaas.com/v3/pix/qrCodes/static';

        if ((int)$config->sandbox_boleto === 0) {
            $endPoint = 'https://api.asaas.com/v3/pix/qrCodes/static';
        }

        $valor = $planoEmpresa->valor;
        if($planoEmpresa->plano){
            $valor = $planoEmpresa->plano->valor;
        }

        try {
            $response = $client->request('POST', $endPoint, [
                'json' => [
                    'value' => (float) $valor
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'access_token' => $config->asaas_token,
                    'content-type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json([
                'status' => true,
                'id' => $data['id'],
                'empresa_id' => $empresa->id,
                'plano_id' => $planoEmpresa->plano_id,
                'valor' => $valor,
                'qr_code' => 'data:image/png;base64,' . $data['encodedImage'],
                'pix_copia_cola' => $data['payload'],
                'data_expiracao' => $planoEmpresa->data_expiracao,
            ]);

        } catch (\Exception $e) {
            $message = 'Erro ao comunicar com o Asaas';

            if (preg_match('/\{.*\}/s', $e->getMessage(), $matches)) {
                $body = json_decode($matches[0], true);

                if (isset($body['errors'][0]['description'])) {
                    $message = $body['errors'][0]['description'];
                }
            } else {
                $message = $e->getMessage();
            }

            return response()->json([
                'status' => false,
                'mensagem' => $message
            ], 400);
        }
    }

    public function statusPix(Request $request)
    {
        $plano = Plano::findOrFail($request->plano_id);
        $empresa = Empresa::findOrFail($request->empresa_id);

        $config = ConfiguracaoSuper::first();

        $client = new \GuzzleHttp\Client();

        $endPoint = 'https://api-sandbox.asaas.com/v3/pix/transactions';

        if ((int)$config->sandbox_boleto === 0) {
            $endPoint = 'https://api.asaas.com/v3/pix/transactions';
        }

        try {
            $response = $client->request('GET', $endPoint, [
                'headers' => [
                    'accept' => 'application/json',
                    'access_token' => $config->asaas_token,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['data'])) {
                foreach ($data['data'] as $d) {
                    if (($d['conciliationIdentifier'] ?? null) == $request->id) {

                        $this->setarLicencaAsaas($plano, $empresa);

                        return response()->json([
                            'status' => true,
                            'pago' => true,
                            'mensagem' => 'Pagamento aprovado'
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'pago' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Erro ao consultar pagamento'
            ], 400);
        }
    }

    private function setarLicencaAsaas($plano, $empresa)
    {
        $exp = date('Y-m-d', strtotime("+$plano->intervalo_dias days"));

        $planoEmpresa = PlanoEmpresa::create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
            'data_expiracao' => $exp,
            'valor' => $plano->valor,
            'forma_pagamento' => 'pix'
        ]);

        FinanceiroPlano::create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
            'valor' => $plano->valor,
            'tipo_pagamento' => 'PIX',
            'status_pagamento' => 'recebido',
            'plano_empresa_id' => $planoEmpresa->id
        ]);
    }
}
