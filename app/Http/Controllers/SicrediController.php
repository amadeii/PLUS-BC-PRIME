<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SicrediController extends Controller
{

    public function token()
    {
        $url = "https://api-parceiro.sicredi.com.br/auth/openapi/token";
        $xApiKey = '83576fd9-4223-4e09-8ae4-d2dda894e906';
        $codigoBeneficiario = '42090';
        $dataVencimento = '2026-04-30';

        $username = '420902102';
        $password = 'A400577585D5B5A387DB1DBFC5E5ECAFAF05255D917C71DB9CD7FD0577CB1044';

        $response = Http::withHeaders([
            'x-api-key' => $xApiKey,
            'context' => 'COBRANCA',
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])
        ->asForm()
        ->post($url, [
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'scope' => 'cobranca'
        ]);
        if(!isset($response->json()['access_token'])){
            echo "Credenciais incorretas!";
            die;
        }

        $token = $response->json()['access_token'];

        $boletoUrl = "https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos";
        $payload = [
            // "beneficiarioFinal" => [
            //     "cep" => "49290000",
            //     "cidade" => "ITABAIANINHA",
            //     "documento" => "33254896000194",
            //     "logradouro" => "RUA PROF MARIETE CARDOSO E SILVA",
            //     "nome" => "BARBOSA REPRESENTACOES COMERCIAIS LTDA",
            //     "numeroEndereco" => "20",
            //     "tipoPessoa" => "PESSOA_JURIDICA",
            //     "uf" => "SE"
            // ],
            "codigoBeneficiario" => $codigoBeneficiario,
            "dataVencimento" => $dataVencimento,
            "especieDocumento" => "DUPLICATA_MERCANTIL_INDICACAO",
            "pagador" => [
                "cep" => "84200000",
                "cidade" => "JAGUARIAIVA",
                "documento" => "09520985980",
                "nome" => "MARCOS MELLO",
                "tipoPessoa" => "PESSOA_FISICA",
                "endereco" => "RUA DOUTOR VARGAS 150",
                "uf" => "PR"
            ],
            "tipoCobranca" => "NORMAL",
            // "nossoNumero" => "600046210",
            "seuNumero" => "20",
            "valor" => 1.00,
            // "tipoDesconto" => "PERCENTUAL",
            // "valorDesconto1" => 10.00,
            // "dataDesconto1" => "2026-04-15",
            // "valorDesconto2" => 7.00,
            // "dataDesconto2" => "2026-04-20",
            // "valorDesconto3" => 3.00,
            // "dataDesconto3" => "2026-04-25",
            "tipoJuros" => "PERCENTUAL",
            "juros" => 0.10,
            "mensagem" => "",
        ];

        $coperativa = '2102';
        $posto = '11';

        $boleto = Http::withHeaders([
            'x-api-key' => $xApiKey,
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'cooperativa' => $coperativa,
            'posto' => $posto,
        ])->post($boletoUrl, $payload);

        // dd([
        //     'status' => $boleto->status(),
        //     'body' => $boleto->json() ?? $boleto->body(),
        // ]);

        $dados = $boleto->json();

        $linhaDigitavel = $dados['linhaDigitavel'];

        $pdf = $this->gerarPdfBoleto($token, $linhaDigitavel, $xApiKey);

        return response($pdf, 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="boleto.pdf"');

    }

    public function gerarPdfBoleto($token, $linhaDigitavel, $xApiKey)
    {
        $url = "https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos/pdf";

        $response = Http::withHeaders([
            'x-api-key' => $xApiKey,
            'Authorization' => 'Bearer ' . $token
        ])->get($url, [
            'linhaDigitavel' => $linhaDigitavel
        ]);

        if (!$response->successful()) {
            return [
                'status' => $response->status(),
                'erro' => $response->body()
            ];
        }

        return $response->body();
    }
}
