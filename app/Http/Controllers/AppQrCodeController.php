<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfiguracaoCardapio;
use App\Models\Funcionario;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class AppQrCodeController extends Controller
{
    public function index(Request $request)
    {
        $config = ConfiguracaoCardapio::where('empresa_id', $request->empresa_id)
        ->first();

        if(!$config || empty($config->api_token)){

            return redirect()
            ->back()
            ->with('error', 'Nenhum token API foi configurado para esta empresa.');
        }

        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)
        ->whereNotNull('usuario_id')
        ->whereNotNull('codigo')
        ->get();

        if($funcionarios->count() == 0){

            return redirect()
            ->back()
            ->with('error', 'Nenhum funcionário com código de operador foi encontrado.');
        }

        $dados = [];

        foreach($funcionarios as $f){

            $payload = [
                'url' => url('/'),
                'token' => $config->api_token ?? '',
                'codigo_operador' => $f->codigo ?? '',
                'nome' => $f->nome,
                'cor_principal' => '#8448dc',
            ];

            $json = json_encode($payload);

            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 8,
                'imageBase64' => true,
            ]);

            $dados[] = [
                'funcionario' => $f,
                'payload' => $payload,
                'json' => $json,
                'qrcode' => (new QRCode($options))->render($json),
            ];
        }

        return view('app_qrcode.index', compact('dados', 'config'));
    }

    public function qrCodeImage(Request $request)
    {
        $json = $request->json;

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 8,
            'imageBase64' => false,
        ]);

        $qrcode = (new QRCode($options))->render($json);

        return response($qrcode)
        ->header('Content-Type', 'image/png');
    }
}