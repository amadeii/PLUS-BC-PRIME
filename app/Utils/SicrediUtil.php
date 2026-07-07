<?php

namespace App\Utils;

use App\Models\SicrediConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class SicrediUtil
{
    private static function getConfig($empresaId = null)
    {

        $config = SicrediConfig::where('empresa_id', $empresaId)->first();

        if (!$config) {
            throw new Exception('Configuração Sicredi não encontrada.');
        }

        return $config;
    }

    private static function getBaseUrl()
    {
        return "https://api-parceiro.sicredi.com.br";
    }

    public static function gerarToken($empresaId = null)
    {
        $config = self::getConfig($empresaId);

        $url = self::getBaseUrl() . "/auth/openapi/token";

        $response = Http::withHeaders([
            'x-api-key' => $config->x_api_key,
            'context' => 'COBRANCA',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, [
            'grant_type' => 'password',
            'username' => $config->username,
            'password' => $config->password,
            'scope' => 'cobranca',
        ]);

        if (!$response->successful()) {
            Log::error('Erro token Sicredi', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception('Erro ao gerar token Sicredi: ' . $response->body());
        }

        $json = $response->json();

        if (empty($json['access_token'])) {
            throw new Exception('Token não retornado pela Sicredi.');
        }

        $expiresIn = (int)($json['expires_in'] ?? 0);

        return [
            'access_token' => $json['access_token'],
            'expires_in' => $expiresIn,
            'token_expires_at' => $expiresIn > 0
            ? Carbon::now()->addSeconds($expiresIn)
            : null,
            'response' => $json,
        ];
    }

    private static function getHeaders($empresaId = null)
    {
        $config = self::getConfig($empresaId);
        $token = self::getTokenValido($empresaId);

        return [
            'Authorization' => 'Bearer ' . $token,
            'x-api-key' => $config->x_api_key,
            'cooperativa' => $config->cooperativa,
            'posto' => $config->posto,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * 💸 GERAR BOLETO
     */
    public static function gerarBoleto(array $payload, $empresaId = null)
    {
        $url = self::getBaseUrl() . "/cobranca/boleto/v1/boletos";

        $response = Http::withHeaders(self::getHeaders($empresaId))
        ->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Erro gerar boleto Sicredi', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);

            throw new Exception('Erro ao gerar boleto: ' . $response->body());
        }

        return $response->json();
    }

    public static function consultarBoleto($nossoNumero, $empresaId = null)
    {
        if (empty($nossoNumero)) {
            throw new \Exception('Nosso número não informado para consulta do boleto.');
        }

        $config = self::getConfig($empresaId);

        $url = self::getBaseUrl() . '/cobranca/boleto/v1/boletos';

        $response = Http::withHeaders(self::getHeaders($empresaId))
        ->get($url, [
            'codigoBeneficiario' => $config->codigo_beneficiario,
            'nossoNumero' => $nossoNumero,
        ]);

        if (!$response->successful()) {
            throw new \Exception(
                'Erro ao consultar boleto. Status: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    public static function imprimirBoleto($linhaDigitavel, $empresaId = null)
    {
        if (empty($linhaDigitavel)) {
            throw new \Exception('Linha digitável não informada para impressão do boleto.');
        }

        $url = self::getBaseUrl() . "/cobranca/boleto/v1/boletos/pdf";

        $response = Http::withHeaders(self::getHeaders($empresaId))
        ->timeout(30)
        ->get($url, [
            'linhaDigitavel' => $linhaDigitavel
        ]);

        if ($response->successful()) {
            return $response->body();
        }

    // 👇 TRATAMENTO INTELIGENTE
        $status = $response->status();
        $body = $response->json();

    // Caso boleto já baixado / inválido
        if ($status == 422) {
            $mensagem = $body['message'] ?? 'Boleto não pode ser impresso.';
            abort(403, "⚠️ Este boleto não pode ser impresso.\nMotivo: " . $mensagem);
        }

    // Caso não encontrado
        if ($status == 404) {
            throw new \Exception("❌ Boleto não encontrado no banco.");
        }

    // Outros erros
        throw new \Exception(
            'Erro ao imprimir boleto. Status: ' . $status . ' - ' . $response->body()
        );
    }

    public static function getTokenValido($empresaId = null)
    {
        $config = self::getConfig($empresaId);

        if (
            !empty($config->access_token) &&
            !empty($config->token_expires_at) &&
            Carbon::parse($config->token_expires_at)->greaterThan(now()->addMinute())
        ) {
            return $config->access_token;
        }

        $tokenData = self::gerarToken($empresaId);

        $config->update([
            'access_token' => $tokenData['access_token'],
            'token_expires_at' => $tokenData['token_expires_at'],
        ]);

        return $tokenData['access_token'];
    }

    private static function getHeadersBaixa($empresaId = null)
    {
        $config = self::getConfig($empresaId);
        $token = self::getTokenValido($empresaId);

        return [
            'Authorization' => 'Bearer ' . $token,
            'x-api-key' => $config->x_api_key,
            'cooperativa' => $config->cooperativa,
            'posto' => $config->posto,
            'codigoBeneficiario' => $config->codigo_beneficiario,
            'Content-Type' => 'application/json',
        ];
    }

    public static function baixarBoleto($nossoNumero, $empresaId = null)
    {
        if (empty($nossoNumero)) {
            throw new Exception('Nosso número não informado para baixa do boleto.');
        }

        $config = self::getConfig($empresaId);
        $nossoNumero = trim((string)$nossoNumero);

        $url = self::getBaseUrl() . "/cobranca/boleto/v1/boletos/{$nossoNumero}/baixa";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::getTokenValido($empresaId),
            'x-api-key' => $config->x_api_key,
            'Cooperativa' => $config->cooperativa,
            'posto' => $config->posto,
            'codigoBeneficiario' => $config->codigo_beneficiario,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->send('PATCH', $url, [
            'body' => '{}',
        ]);

        Log::info('Baixa Sicredi debug', [
            'empresa_id' => $config->empresa_id,
            'nosso_numero' => $nossoNumero,
            'codigo_beneficiario' => $config->codigo_beneficiario,
            'cooperativa' => $config->cooperativa,
            'posto' => $config->posto,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new Exception(
                'Erro ao baixar boleto. Status: ' . $response->status() . ' - ' . ($response->body() ?: 'sem retorno')
            );
        }

        return $response->json();
    }
}