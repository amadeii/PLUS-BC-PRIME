<?php

namespace App\Http\Controllers;

use App\Models\ConfigGeral;
use App\Models\Nfce;
use Illuminate\Http\Request;
use App\Models\ConfiguracaoCardapio;
use Illuminate\Support\Facades\File;

class ConfigGeralController extends Controller
{
    public function create()
    {
        $item = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $config = ConfiguracaoCardapio::where('empresa_id', request()->empresa_id)->first();
        if($item != null){
            $item->notificacoes = json_decode($item->notificacoes);
            $item->tipos_pagamento_pdv = $item != null && $item->tipos_pagamento_pdv ? json_decode($item->tipos_pagamento_pdv) : [];
            $item->acessos_pdv_off = $item != null && $item->acessos_pdv_off ? json_decode($item->acessos_pdv_off) : [];

            $item->home_componentes = $item != null && $item->home_componentes ? json_decode($item->home_componentes) : [];
        }

        $path = public_path('assets/images/small');
        $smallImages = File::files($path);

        $tiposPagamento = Nfce::tiposPagamento();
        // dd($tiposPagamento);
        if($item != null){

            $temp = [];
            if(sizeof($item->tipos_pagamento_pdv) > 0){
                foreach($tiposPagamento as $key => $t){
                    if(in_array($t, $item->tipos_pagamento_pdv)){
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        return view('config_geral.index', compact('item', 'config', 'smallImages', 'tiposPagamento'));
    }

    public function store(Request $request)
    {
        $item = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $config = ConfiguracaoCardapio::where('empresa_id', request()->empresa_id)->first();

        try {

            if($config){
                $config->api_token = $request->api_token;
                $config->save();
            }else{
                ConfiguracaoCardapio::create([
                    'empresa_id' => $request->empresa_id,
                    'nome_restaurante' => '',
                    'logo' => '',
                    'fav_icon' => '',
                    'telefone' => '',
                    'rua' => '',
                    'numero' => '',
                    'bairro' => '',
                    'cidade_id' => 1,
                    'api_token' => $request->api_token ?? '',
                ]);
            }
            if(!isset($request->notificacoes)){
                $request->merge([
                    'notificacoes' => '[]'
                ]);
            }else{
                $request->merge([
                    'notificacoes' => json_encode($request->notificacoes)
                ]);
            }

            if(!isset($request->tipos_pagamento_pdv)){
                $request->merge([
                    'tipos_pagamento_pdv' => '[]'
                ]);
            }else{
                $request->merge([
                    'tipos_pagamento_pdv' => json_encode($request->tipos_pagamento_pdv)
                ]);
            }

            if(!isset($request->acessos_pdv_off)){
                $request->merge([
                    'acessos_pdv_off' => '[]'
                ]);
            }else{
                $request->merge([
                    'acessos_pdv_off' => json_encode($request->acessos_pdv_off)
                ]);
            }

            if(!isset($request->home_componentes)){
                $request->merge([
                    'home_componentes' => '[]'
                ]);
            }else{
                $request->merge([
                    'home_componentes' => json_encode($request->home_componentes)
                ]);
            }

            // dd($request->all());

            $request->merge([
                'margem_combo' => $request->margem_combo ? __convert_value_bd($request->margem_combo) : 50,
                'percentual_lucro_produto' => $request->percentual_lucro_produto ?? 0,
                'ultimo_codigo_produto' => $request->ultimo_codigo_produto ?? 0,
                'ultimo_codigo_cliente' => $request->ultimo_codigo_cliente ?? 0,
                'perc_multa_padrao' => $request->perc_multa_padrao ?? 0,
                'perc_juros_padrao' => $request->perc_juros_padrao ?? 0,
                'margem_lateral_impressao' => $request->margem_lateral_impressao ?? 0,
                'itens_por_pagina' => $request->itens_por_pagina ?? 30,
                'tipo_pagamento_padrao' => $request->tipo_pagamento_padrao ?? 30,
                'ultimo_codigo_fornecedor' => $request->ultimo_codigo_fornecedor ?? 0,
                'mensagem_padrao_impressao_venda' => $request->mensagem_padrao_impressao_venda ?? '',
                'mensagem_wpp_link' => $request->mensagem_wpp_link ?? '',
                'mensagem_padrao_impressao_os' => $request->mensagem_padrao_impressao_os ?? '',
                'cliente_padrao_pdv_off' => isset($request->cliente_padrao_pdv_off) ? $request->cliente_padrao_pdv_off : null,

                'enviar_danfe_wpp_link' => $request->enviar_danfe_wpp_link ? 1 : 0,
                'enviar_xml_wpp_link' => $request->enviar_xml_wpp_link ? 1 : 0,
                'enviar_pedido_a4_wpp_link' => $request->enviar_pedido_a4_wpp_link ? 1 : 0,
            ]);

            if ($item == null) {
                ConfigGeral::create($request->all());
                session()->flash("flash_success", "Dados cadastrados com sucesso!");
            } else {
                $item->fill($request->all())->save();
                session()->flash("flash_success", "Dados alterados com sucesso!");
            }

             // cria cliente padrão no Asaas somente se tiver token e ainda não tiver customer salvo
            // if (!empty($item->asaas_token) && empty($item->customer_asaas_id)) {

            //     try {
            //         $client = new \GuzzleHttp\Client();

            //         $response = $client->request('POST', 'https://api.asaas.com/v3/customers', [
            //             'json' => [
            //                 'name' => 'Consumidor PDV',
            //             ],
            //             'headers' => [
            //                 'accept' => 'application/json',
            //                 'access_token' => $item->asaas_token,
            //                 'content-type' => 'application/json',
            //             ],
            //         ]);

            //         $data = json_decode($response->getBody(), true);

            //         if (!empty($data['id'])) {
            //             $item->customer_asaas_id = $data['id'];
            //             $item->save();
            //         }
            //     } catch (\GuzzleHttp\Exception\ClientException $e) {
            //         $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);

            //         session()->flash(
            //             "flash_error",
            //             "Configuração salva, mas não foi possível criar o cliente padrão no Asaas: " .
            //             ($errorBody['errors'][0]['description'] ?? $e->getMessage())
            //         );

            //         return redirect()->back();
            //     }
            // }
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }
}
