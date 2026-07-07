<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NuvemShopConfig;

class NuvemShopAuthController extends Controller
{
    public function index(Request $request)
    {
        $config = NuvemShopConfig::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            session()->flash('flash_warning', 'Defina a configuração da Nuvemshop!');
            return redirect()->route('nuvem-shop-config.index', [
                'empresa_id' => $request->empresa_id
            ]);
        }

        if (!$config->client_id || !$config->client_secret) {
            session()->flash('flash_warning', 'Informe o APP ID e o Client Secret.');
            return redirect()->route('nuvem-shop-config.index', [
                'empresa_id' => $request->empresa_id
            ]);
        }

        $auth = new \TiendaNube\Auth($config->client_id, $config->client_secret);
        $url = $auth->login_url_brazil();

        return redirect($url);
    }

    public function code(Request $request)
    {
        $config = NuvemShopConfig::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            session()->flash('flash_error', 'Configuração da Nuvemshop não encontrada.');
            return redirect()->route('nuvem-shop-config.index', [
                'empresa_id' => $request->empresa_id
            ]);
        }

        if (!$request->code) {
            session()->flash('flash_error', 'Código de autorização não informado pela Nuvemshop.');
            return redirect()->route('nuvem-shop-config.index', [
                'empresa_id' => $config->empresa_id
            ]);
        }

        try {
            $auth = new \TiendaNube\Auth($config->client_id, $config->client_secret);
            $store_info = $auth->request_access_token($request->code);

            $config->store_id = $store_info['store_id'] ?? null;
            $config->access_token = $store_info['access_token'] ?? null;
            $config->user_id_nuvemshop = $store_info['user_id'] ?? null;
            $config->scope = $store_info['scope'] ?? null;
            $config->token_gerado_em = now();
            $config->autenticado = !empty($store_info['access_token']) && !empty($store_info['store_id']);
            $config->save();

            $store_info['email'] = $config->email;
            session(['store_info' => $store_info]);

            session()->flash(
                'flash_success',
                'Autenticação realizada com sucesso! Loja: ' . ($config->store_id ?? '--')
            );

            return redirect()->route('nuvem-shop-pedidos.index', [
                'empresa_id' => $config->empresa_id
            ]);

        } catch (\Exception $e) {
            $config->autenticado = false;
            $config->save();

            session()->flash('flash_error', 'Erro ao autenticar com a Nuvemshop: ' . $e->getMessage());

            return redirect()->route('nuvem-shop-config.index', [
                'empresa_id' => $config->empresa_id
            ]);
        }
    }
}