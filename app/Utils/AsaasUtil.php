<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AsaasConfig;
use Exception;

class AsaasUtil
{
    private static function getConfig($empresaId)
    {
        $config = AsaasConfig::where('empresa_id', $empresaId)->first();

        if (!$config || !$config->token) {
            throw new Exception("Configuração Asaas não encontrada para empresa {$empresaId}");
        }

        return $config;
    }

    private static function getBaseUrl($empresaId)
    {
        $config = self::getConfig($empresaId);

        return $config->sandbox == 1
            ? 'https://api-sandbox.asaas.com/v3'
            : 'https://api.asaas.com/v3';
    }

    private static function getHeaders($empresaId)
    {
        $config = self::getConfig($empresaId);

        return [
            'accept' => 'application/json',
            'access_token' => $config->token,
            'Content-Type' => 'application/json',
        ];
    }

    public static function gerarBoleto(array $payload, $empresaId)
    {
        $url = self::getBaseUrl($empresaId) . '/payments';

        $response = Http::withHeaders(self::getHeaders($empresaId))
            ->post($url, $payload);

        Log::info('Gerar boleto Asaas', [
            'empresa_id' => $empresaId,
            'payload' => $payload,
            'status_http' => $response->status(),
            'response' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao gerar boleto: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    public static function consultarBoleto($paymentId, $empresaId)
    {
        if (empty($paymentId)) {
            throw new Exception('ID do boleto não informado.');
        }

        $url = self::getBaseUrl($empresaId) . "/payments/{$paymentId}";

        $response = Http::withHeaders(self::getHeaders($empresaId))->get($url);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao consultar boleto: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    public static function baixarBoleto($paymentId, $empresaId)
    {
        if (empty($paymentId)) {
            throw new Exception('ID do boleto não informado para baixa.');
        }

        $url = self::getBaseUrl($empresaId) . "/payments/{$paymentId}";

        $response = Http::withHeaders(self::getHeaders($empresaId))
            ->delete($url);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao baixar boleto Asaas: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    public static function consultarCliente($customerId, $empresaId)
    {
        if (empty($customerId)) {
            throw new Exception('ID do cliente Asaas não informado.');
        }

        $url = self::getBaseUrl($empresaId) . "/customers/{$customerId}";

        $response = Http::withHeaders(self::getHeaders($empresaId))->get($url);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao consultar cliente Asaas: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    public static function buscarClientePorCpfCnpj($cpfCnpj, $empresaId)
    {
        $cpfCnpj = preg_replace('/\D/', '', (string)$cpfCnpj);

        if (empty($cpfCnpj)) {
            return null;
        }

        $url = self::getBaseUrl($empresaId) . '/customers';

        $response = Http::withHeaders(self::getHeaders($empresaId))
            ->get($url, [
                'cpfCnpj' => $cpfCnpj,
            ]);

        Log::info('Buscar cliente Asaas por CPF/CNPJ', [
            'empresa_id' => $empresaId,
            'cpf_cnpj' => $cpfCnpj,
            'status_http' => $response->status(),
            'response' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao buscar cliente Asaas: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $json = $response->json();

        if (!empty($json['data']) && isset($json['data'][0]['id'])) {
            return $json['data'][0];
        }

        return null;
    }

    public static function criarCliente(array $payload, $empresaId)
    {
        $url = self::getBaseUrl($empresaId) . '/customers';

        $response = Http::withHeaders(self::getHeaders($empresaId))
            ->post($url, $payload);

        Log::info('Criar cliente Asaas', [
            'empresa_id' => $empresaId,
            'payload' => $payload,
            'status_http' => $response->status(),
            'response' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao criar cliente Asaas: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }
}