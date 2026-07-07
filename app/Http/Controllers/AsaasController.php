<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AsaasController extends Controller
{
    public function index(Request $request){
        $client = new \GuzzleHttp\Client();
        $accessToken = config('services.asaas.access_token');

        if (empty($accessToken)) {
            abort(500, 'Token Asaas não configurado.');
        }

        $response = $client->request('POST', 'https://api.asaas.com/v3/pix/qrCodes/static', [
            'body' => '{"value":1}',
            'headers' => [
                'accept' => 'application/json',
                'access_token' => $accessToken,
                'content-type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(),true);
        echo '<img src="data:image/jpeg;base64,'.($data['encodedImage']).'">';

        echo "<br>Payload: " . $data['payload'];
    }
}
