<?php

namespace App\Utils;

use App\Models\MercadoLivreConfig;
use App\Models\MercadoLivrePergunta;
use App\Models\Notificacao;
use App\Models\PedidoMercadoLivre;
use App\Models\ItemPedidoMercadoLivre;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Cidade;

class MercadoLivreUtil
{
    private function request($method, $url, $token = null, $data = null, $headers = [])
    {
        $curl = curl_init();

        $defaultHeaders = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        if ($token) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
        ]);

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        }

        $res = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($curlError) {
            return (object)[
                'error' => 'curl_error',
                'message' => $curlError,
                'http_code' => $httpCode
            ];
        }

        $json = json_decode($res);

        if (!$json) {
            return (object)[
                'error' => 'invalid_json',
                'message' => 'Resposta inválida do Mercado Livre',
                'raw' => $res,
                'http_code' => $httpCode
            ];
        }

        $json->http_code = $httpCode;

        return $json;
    }

    public function refreshToken($empresa_id)
    {
        $config = MercadoLivreConfig::where('empresa_id', $empresa_id)->first();

        if ($config == null) {
            return 0;
        }

        $strtotimeAtual = strtotime(date('Y-m-d H:i:s'));

        if ($config->token_expira && $strtotimeAtual < $config->token_expira) {
            return "token valido!";
        }

        $curl = curl_init();

        $payload = http_build_query([
            "grant_type" => "refresh_token",
            "client_id" => $config->client_id,
            "client_secret" => $config->client_secret,
            "refresh_token" => $config->refresh_token
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mercadolibre.com/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ],
        ]);

        $res = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            return (object)[
                'error' => 'curl_error',
                'message' => $curlError,
                'http_code' => $httpCode
            ];
        }

        $retorno = json_decode($res);

        if (!$retorno) {
            return (object)[
                'error' => 'invalid_json',
                'message' => 'Resposta inválida ao renovar token',
                'raw' => $res,
                'http_code' => $httpCode
            ];
        }

        $retorno->http_code = $httpCode;

        if (isset($retorno->access_token)) {
            $config->access_token = $retorno->access_token;
            $config->refresh_token = $retorno->refresh_token ?? $config->refresh_token;
            $config->user_id = $retorno->user_id ?? $config->user_id;
            $config->token_expira = strtotime(date('Y-m-d H:i:s')) + ($retorno->expires_in ?? 21600);
            $config->save();
        }

        return $retorno;
    }

    public function getNotification($config, $request)
    {
        $resource = $request->resource;
        $tipo = explode("/", $resource);
        $tipo = $tipo[1] ?? null;

        $retorno = $this->request(
            'GET',
            "https://api.mercadolibre.com" . $resource,
            $config->access_token
        );

        if (isset($retorno->error)) {
            return $retorno->message ?? $retorno->error;
        }

        if ($tipo == 'questions') {
            $this->inserePergunta($retorno, $config);
            return "pergunta inserida";
        }

        if ($tipo == 'orders') {
            $item = $this->criaPedido($retorno, $config);
            $this->criaNotificacaoPedido($item);
            return "pedido inserido";
        }

        return $tipo;
    }

    private function inserePergunta($retorno, $config)
    {
        if (!isset($retorno->id)) {
            return null;
        }

        $pergunta = MercadoLivrePergunta::where('_id', $retorno->id)->first();

        if ($pergunta == null) {
            $pergunta = MercadoLivrePergunta::create([
                'empresa_id' => $config->empresa_id,
                '_id' => $retorno->id,
                'item_id' => $retorno->item_id ?? null,
                'status' => $retorno->status ?? '',
                'texto' => $retorno->text ?? '',
                'data' => isset($retorno->date_created) ? substr($retorno->date_created, 0, 20) : now()
            ]);

            $this->criaNotificacaoPergunta($pergunta);

            return $pergunta;
        }

        return $pergunta;
    }

    private function criaNotificacaoPergunta($pergunta)
    {
        $descricao = view('notificacao.partials.pergunta_mercado_livre', compact('pergunta'));

        Notificacao::create([
            'empresa_id' => $pergunta->empresa_id,
            'tabela' => 'mercado_livre_perguntas',
            'descricao' => $descricao,
            'descricao_curta' => 'Pergunta ' . ($pergunta->anuncio ? $pergunta->anuncio->nome : $pergunta->item_id),
            'referencia' => $pergunta->id,
            'status' => 1,
            'por_sistema' => 0,
            'super' => 1,
            'prioridade' => 'alta',
            'visualizada' => 0,
            'titulo' => 'Pergunta mercado livre'
        ]);
    }

    private function criaNotificacaoPedido($item)
    {
        if (!$item) {
            return;
        }

        $descricao = view('notificacao.partials.novo_pedido_mercado_livre', compact('item'));

        Notificacao::create([
            'empresa_id' => $item->empresa_id,
            'tabela' => 'pedido_mercado_livres',
            'descricao' => $descricao,
            'descricao_curta' => 'Novo pedido mercado livre #' . $item->_id,
            'referencia' => $item->id,
            'status' => 1,
            'por_sistema' => 0,
            'super' => 1,
            'prioridade' => 'alta',
            'visualizada' => 0,
            'titulo' => 'Pedido mercado livre'
        ]);
    }

    public function criaPedido($pedido, $config)
    {
        if (!isset($pedido->id)) {
            return null;
        }

        $dataPedido = [
            'empresa_id' => $config->empresa_id,
            '_id' => $pedido->id,
            'tipo_pagamento' => $pedido->payments[0]->payment_type ?? '',
            'status' => $pedido->status ?? '',
            'total' => $pedido->total_amount ?? 0,
            'valor_entrega' => $pedido->shipping_cost ?? 0,
            'nickname' => $pedido->seller->nickname ?? '',
            'seller_id' => $pedido->seller->id ?? null,
            'entrega_id' => $pedido->shipping->id ?? null,
            'data_pedido' => isset($pedido->date_created) ? substr($pedido->date_created, 0, 19) : now(),
            'comentario' => $pedido->comment ?? '',
        ];

        $pedidoInsert = PedidoMercadoLivre::where('empresa_id', $config->empresa_id)
            ->where('_id', $pedido->id)
            ->first();

        if ($pedidoInsert == null) {
            $pedidoInsert = PedidoMercadoLivre::create($dataPedido);
        }

        foreach (($pedido->order_items ?? []) as $itemPedido) {
            $produto = Produto::where('mercado_livre_id', $itemPedido->item->id ?? null)->first();

            $dataItem = [
                'pedido_id' => $pedidoInsert->id,
                'produto_id' => $produto ? $produto->id : null,
                'item_id' => $itemPedido->item->id ?? null,
                'item_nome' => $itemPedido->item->title ?? '',
                'condicao' => $itemPedido->item->condition ?? '',
                'variacao_id' => $itemPedido->item->variation_id ?? null,
                'quantidade' => $itemPedido->quantity ?? 0,
                'valor_unitario' => $itemPedido->unit_price ?? 0,
                'sub_total' => ($itemPedido->quantity ?? 0) * ($itemPedido->unit_price ?? 0),
                'taxa_venda' => $itemPedido->sale_fee ?? 0
            ];

            $itemInsert = ItemPedidoMercadoLivre::where('pedido_id', $pedidoInsert->id)
                ->where('item_id', $dataItem['item_id'])
                ->first();

            if ($itemInsert == null) {
                ItemPedidoMercadoLivre::create($dataItem);
            }
        }

        if (isset($pedido->shipping->id)) {
            $this->setDadosEntrega($pedido->shipping->id, $pedidoInsert, $config);
        }

        $this->getDadosCliente($pedidoInsert->id);

        return PedidoMercadoLivre::findOrFail($pedidoInsert->id);
    }

    private function setDadosEntrega($shipping_id, $pedido, $config)
    {
        $retorno = $this->request(
            'GET',
            "https://api.mercadolibre.com/shipments/$shipping_id",
            $config->access_token
        );

        if (isset($retorno->destination->shipping_address)) {
            $shipping_address = $retorno->destination->shipping_address;

            $pedido->rua_entrega = $shipping_address->street_name ?? '';
            $pedido->numero_entrega = $shipping_address->street_number ?? '';
            $pedido->cep_entrega = $shipping_address->zip_code ?? '';
            $pedido->comentario_entrega = $shipping_address->comment ?? '';
            $pedido->bairro_entrega = $shipping_address->neighborhood->name ?? '';
            $pedido->cidade_entrega = ($shipping_address->city->name ?? '') . " - " . ($shipping_address->state->name ?? '');
            $pedido->save();
        }
    }

    private function getDadosCliente($pedido_id)
    {
        $item = PedidoMercadoLivre::findOrFail($pedido_id);

        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)->first();

        if (!$config) {
            return null;
        }

        $retorno = $this->request(
            'GET',
            "https://api.mercadolibre.com/orders/$item->_id/billing_info",
            $config->access_token,
            null,
            ['x-version: 2']
        );

        if (!isset($retorno->buyer->billing_info)) {
            return null;
        }

        $info = $retorno->buyer->billing_info;
        $address = $info->address ?? null;

        try {
            $cidade = Cidade::where('nome', $address->city_name ?? '')->first();

            $documento = $info->identification->number ?? '';
            $tipoDocumento = $info->identification->type ?? 'CPF';

            if ($tipoDocumento == 'CPF') {
                $dataCliente = [
                    'cpf_cnpj' => $documento,
                    'razao_social' => trim(($info->name ?? '') . ' ' . ($info->last_name ?? '')),
                    'email' => '',
                    'rua' => $address->street_name ?? '',
                    'numero' => $address->street_number ?? '',
                    'bairro' => $address->neighborhood ?? '',
                    'consumidor_final' => 1,
                    'cep' => $address->zip_code ?? '',
                    'cidade_id' => $cidade ? $cidade->id : 1,
                    'empresa_id' => $item->empresa_id
                ];
            } else {
                $ie = $info->taxes->inscriptions->state_registration ?? '';

                $dataCliente = [
                    'cpf_cnpj' => $documento,
                    'razao_social' => trim(($info->name ?? '') . ' ' . ($info->last_name ?? '')),
                    'email' => '',
                    'ie' => $ie,
                    'contribuinte' => $ie ? 1 : 0,
                    'consumidor_final' => $ie ? 0 : 1,
                    'rua' => $info->street_name ?? ($address->street_name ?? ''),
                    'numero' => $info->street_number ?? ($address->street_number ?? ''),
                    'bairro' => $address->neighborhood ?? '',
                    'cep' => $info->zip_code ?? ($address->zip_code ?? ''),
                    'cidade_id' => $cidade ? $cidade->id : 1,
                    'empresa_id' => $item->empresa_id
                ];
            }
        } catch (\Exception $e) {
            $dataCliente = [
                'cpf_cnpj' => $info->identification->number ?? '',
                'razao_social' => trim(($info->name ?? '') . ' ' . ($info->last_name ?? '')),
                'email' => '',
                'ie' => '',
                'contribuinte' => 0,
                'consumidor_final' => 1,
                'rua' => '',
                'numero' => '',
                'bairro' => '',
                'cep' => '',
                'cidade_id' => 1,
                'empresa_id' => $item->empresa_id
            ];
        }

        if (empty($dataCliente['cpf_cnpj'])) {
            return null;
        }

        $item->cliente_nome = $dataCliente['razao_social'];
        $item->cliente_documento = $dataCliente['cpf_cnpj'];

        $cliente = Cliente::where('empresa_id', $item->empresa_id)
            ->where('cpf_cnpj', $dataCliente['cpf_cnpj'])
            ->first();

        if ($cliente == null) {
            $cliente = Cliente::create($dataCliente);
        }

        if ($cliente) {
            $item->cliente_id = $cliente->id;
        }

        $item->save();

        return $cliente;
    }
}