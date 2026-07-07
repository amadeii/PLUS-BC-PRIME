<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\CategoriaConta;
use App\Models\Localizacao;
use Illuminate\Support\Facades\DB;

class ContaReceberController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = $request->empresa_id;
        $perPage = $request->per_page ?? 20;

        $cliente_id = $request->cliente_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $filtro_data = $request->filtro_data;
        $status = trim($request->status);

        if($filtro_data == 'data_recebimento'){
            $status = 1;
        }

        $query = ContaReceber::where('empresa_id', $empresa_id)
        ->with('cliente')
        ->select('conta_recebers.*')
        ->when(!empty($start_date), function ($query) use ($start_date, $filtro_data) {
            return $query->whereDate('conta_recebers.'.$filtro_data, '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date, $filtro_data) {
            return $query->whereDate('conta_recebers.'.$filtro_data, '<=', $end_date);
        })
        ->when($status !== '', function ($query) use ($status) {
            if($status == 0){
                return $query->where('conta_recebers.status', 0)->whereDate('conta_recebers.data_vencimento', '>', date('Y-m-d'));
            }else if($status == 1){
                return $query->where('conta_recebers.status', 1);
            }else{
                return $query->where('conta_recebers.status', 0)
                ->whereDate('conta_recebers.data_vencimento', '<', date('Y-m-d'));
            }
        });

        if (!empty($request->cliente)) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razao_social', 'like', '%' . $request->cliente . '%')
                ->orWhere('razao_social', 'like', '%' . $request->cliente . '%');
            });
        }


        $data = $query
        ->orderBy('data_vencimento')
        ->paginate($perPage);

        return response()->json($data, 200);
    }

    public function categorias(Request $request){
        $empresa_id = $request->empresa_id;

        $data = CategoriaConta::where('empresa_id', $empresa_id)
        ->where('tipo', 'receber')->get();

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $localizacaoPadrao = Localizacao::where('empresa_id', $request->empresa_id)
            ->first();
            $conta = ContaReceber::create([
                'cliente_id' => $request->cliente_id,
                'descricao' => $request->descricao ?? '',
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'data_vencimento' => $request->data_vencimento,
                'tipo_pagamento' => $request->tipo_pagamento,
                'status' => $request->status,
                'categoria_id' => $request->categoria_id ?? null,
                'data_recebimento' => $request->status ? date('Y-m-d') : null,
                'valor_recebido' => $request->status ? __convert_value_bd($request->valor_integral) : 0,
                'observacao' => $request->observacao ?? '',
                'observacao2' => $request->observacao2 ?? '',
                'observacao3' => $request->observacao3 ?? '',
                'empresa_id' => $request->empresa_id,
                'local_id' => $localizacaoPadrao->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conta cadastrada com sucesso!',
                'data'    => $conta
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar conta.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $item = ContaReceber::findOrFail($request->id);
            $conta = [
                'cliente_id' => $request->cliente_id,
                'descricao' => $request->descricao ?? '',
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'data_vencimento' => $request->data_vencimento,
                'tipo_pagamento' => $request->tipo_pagamento,
                'status' => $request->status,
                'categoria_id' => $request->categoria_id ?? null,
                'data_recebimento' => $request->status ? date('Y-m-d') : null,
                'valor_recebido' => $request->status ? __convert_value_bd($request->valor_integral) : 0,
                'observacao' => $request->observacao ?? '',
                'observacao2' => $request->observacao2 ?? '',
                'observacao3' => $request->observacao3 ?? '',
            ];

            $item->fill($conta)->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conta atualizada com sucesso!',
                'data'    => $conta
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar conta.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function receive(Request $request)
    {
        DB::beginTransaction();

        try {

            $conta = ContaReceber::findOrFail($request->id);

            if ($conta->status === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta conta já está recebida.'
                ], 400);
            }

            $valorRecebido = __convert_value_bd($request->valor_recebido);
            $valorIntegral = $conta->valor_integral;

            if ($valorRecebido <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valor recebido inválido.'
                ], 400);
            }

            $conta->update([
                'valor_recebido' => $valorRecebido,
                'data_recebimento' => $request->data_recebimento,
                'tipo_pagamento' => $request->tipo_pagamento,
                'status' => 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recebimento registrado com sucesso!',
                'data' => $conta->fresh()
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar recebimento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
