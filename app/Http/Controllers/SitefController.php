<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TefLog;
use App\Models\Nfce;

class SitefController extends Controller
{
     public function storeLog(Request $request){
        try {

            $tef = $request->input('tef', []);
            $data = [
                'venda_id' => null,
                'empresa_id' => $request->empresa_id,
                'tef_session_id' => $tef['sessionId'] ?? null,
                'tef_terminal_id' => $tef['terminalId'] ?? null,
                'tef_store_id' => $tef['storeId'] ?? null,
                'tef_clisitef_status' => $tef['clisitefStatus'] ?? null,
                'tef_function_id' => $tef['functionId'] ?? null,
                'tef_controle' => $tef['controle'] ?? null,
                'tef_sitef_ip' => $tef['sitefIp'] ?? null,

                'tef_nsu' => $tef['nsu'] ?? null,
                'tef_codigo_autorizacao' => $tef['codigoAutorizacao'] ?? null,
                'tef_bandeira' => $tef['bandeira'] ?? null,
                'tef_adquirente' => $tef['adquirente'] ?? null,
                'tef_raw' => json_encode($tef, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'comprovantes' => $tef['comprovantes'] ?? null,
            ];

            $tefLog = TefLog::create($data);
            return response()->json($tefLog, 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeLogScope(Request $request)
    {
        try {

            $data = [
                'venda_id' => null,
                'empresa_id' => $request->empresa_id,

                'tef_session_id' => $request->tef_session_id,
                'tef_terminal_id' => $request->tef_terminal_id,
                'tef_store_id' => $request->tef_store_id,

                'tef_clisitef_status' => $request->tef_clisitef_status,
                'tef_function_id' => $request->tef_function_id,
                'tef_controle' => $request->tef_controle,
                'tef_sitef_ip' => null,

                'tef_nsu' => $request->tef_nsu,
                'tef_codigo_autorizacao' => $request->tef_codigo_autorizacao,
                'tef_bandeira' => $request->tef_bandeira,
                'tef_adquirente' => $request->tef_adquirente,

                'valor_centavos' => $request->valor_centavos,
                'status' => $request->tef_clisitef_status,

                'tef_raw' => is_array($request->tef_raw) 
                ? json_encode($request->tef_raw, JSON_UNESCAPED_UNICODE)
                : $request->tef_raw,

            'comprovantes' => is_array($request->comprovantes) 
                ? json_encode($request->comprovantes, JSON_UNESCAPED_UNICODE)
                : $request->comprovantes,
            ];

            $tefLog = TefLog::create($data);

            return response()->json($tefLog, 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function vendas(Request $request)
    {
        try {

            $data = $request->data;

            $vendas = Nfce::query()
            ->where('nfces.empresa_id', $request->empresa_id)
            ->join('tef_logs', 'tef_logs.venda_id', '=', 'nfces.id')
            ->when(!empty($data), function ($query) use ($data) {
                $query->whereDate('nfces.created_at', $data);
            })
            ->with(['tefLog', 'cliente'])
            ->orderBy('nfces.id', 'desc')
            ->select([
                'nfces.id',
                'nfces.numero_sequencial',
                'nfces.total',
                'nfces.created_at',
                'nfces.cliente_id',
                'tef_logs.tef_nsu',
                'tef_logs.tef_function_id',
                'tef_logs.tef_clisitef_status',
                'tef_logs.tef_codigo_autorizacao',
                'tef_logs.created_at as tef_data',
            ])
            ->get();

            return response()->json([
                'success' => true,
                'vendas'  => $vendas
            ], 200);

        } catch (\Exception $e) {


            return response()->json([
                'success' => false,
                'error' => 'Erro ao carregar vendas TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function marcarCancelado(Request $request){
        $venda = Nfce::findOrFail($request->venda_id);
        $tef = $venda->tefLog;

        $tef->cancelado = 1;
        $tef->save();

        if($request->cancelar_venda == 1){
            $venda->total = 0;
            $venda->observacao = 'VENDA CANCELADA POR TEF';
            $venda->save();
        }
        return response()->json("ok", 200);
    }
}
