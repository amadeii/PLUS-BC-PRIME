<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\NfeDivergencia;

class CompraController extends Controller
{

    public function buscarPendentesFornecedor(Request $request)
    {

        $data = Nfe::with(['fornecedor', 'itens'])
        ->where('empresa_id', $request->empresa_id)
        ->where('tpNF', 0)
        ->where('fornecedor_id', $request->fornecedor_id)
        ->where('estado_compra', 'pendente')
        ->orderBy('id', 'desc')
        ->get();

        $fornecedorNome = optional($data->first()?->fornecedor)->info ?? '';

        $compras = $data->map(function ($item) {

            return [
                'id' => $item->id,
                'numero_sequencial' => $item->numero_sequencial ?? $item->id,
                'data' => __data_pt($item->created_at),
                'total' => number_format($item->total, 2, ',', '.'),
                'total_itens' => $item->itens ? $item->itens->count() : 0
            ];
        });

        return response()->json([
            'fornecedor_nome' => $fornecedorNome,
            'total_linhas' => $compras->count(),
            'compras' => $compras
        ]);

    }

    public function compararCompra(Request $request)
    {

        $compra = Nfe::with(['itens','fatura'])
        ->findOrFail($request->compra_id);

        $itensXml = $request->itens_xml ?? [];
        $faturaXml = $request->fatura_xml ?? [];

        $resultadoItens = [];
        $resultadoFaturas = [];

        $itensCompra = $compra->itens->keyBy('produto_id');

        foreach($itensXml as $itemXml){

            $itemCompra = $itensCompra->get($itemXml['produto_id']);

            if(!$itemCompra){

                $resultadoItens[] = [
                    'produto' => $itemXml['produto_nome'],
                    'status' => 'Não existe na compra'
                ];

                continue;
            }

            if(round($itemCompra->quantidade, 2) != round($itemXml['quantidade'], 2)){
                $resultadoItens[] = [
                    'produto' => $itemXml['produto_nome'],
                    'status' => 'Quantidade diferente',
                    'quantidade_xml' => round($itemXml['quantidade'], 2),
                    'quantidade_compra' => round($itemCompra->quantidade, 2)
                ];
            }

            if(round($itemCompra->valor_unitario,2) != round($itemXml['valor'],2)){
                $resultadoItens[] = [
                    'produto' => $itemXml['produto_nome'],
                    'status' => 'Valor diferente',
                    'valor_xml' => $itemXml['valor'],
                    'valor_compra' => $itemCompra->valor_unitario
                ];
            }

        }

        $faturasCompra = $compra->fatura ?? collect();

        foreach($faturaXml as $index => $fatura){

            $faturaCompra = $faturasCompra[$index] ?? null;

            if(!$faturaCompra){

                $resultadoFaturas[] = [
                    'status' => 'Parcela não existe na compra',
                    'vencimento_xml' => $fatura['vencimento'],
                    'valor_xml' => $fatura['valor']
                ];

                continue;
            }

            if(round($faturaCompra->valor,2) != round($fatura['valor'],2)){
                $resultadoFaturas[] = [
                    'status' => 'Valor da parcela diferente',
                    'valor_xml' => $fatura['valor'],
                    'valor_compra' => $faturaCompra->valor
                ];
            }

            $vencXml = \Carbon\Carbon::parse($fatura['vencimento'])->format('Y-m-d');
            $vencCompra = \Carbon\Carbon::parse($faturaCompra->data_vencimento)->format('Y-m-d');

            if($vencXml != $vencCompra){
                $resultadoFaturas[] = [
                    'status' => 'Vencimento diferente',
                    'vencimento_xml' => $vencXml,
                    'vencimento_compra' => $vencCompra
                ];
            }

        }

        $totalXml = collect($faturaXml)->sum('valor');
        $totalCompra = $faturasCompra->sum('valor');

        $divergenciaTotal = false;

        if(round($totalXml,2) != round($totalCompra,2)){
            $divergenciaTotal = true;
        }

        $temDivergencia = count($resultadoItens) > 0 
        || count($resultadoFaturas) > 0 
        || $divergenciaTotal;

        if($temDivergencia){
            NfeDivergencia::where('nfe_id', $compra->id)->delete();
            foreach($resultadoItens as $item){
                NfeDivergencia::create([
                    'nfe_id' => $compra->id,
                    'tipo' => 'item',
                    'produto' => $item['produto'] ?? null,
                    'status' => $item['status'],

                    'valor_xml' => $item['valor_xml'] ?? null,
                    'valor_compra' => $item['valor_compra'] ?? null,

                    'quantidade_xml' => $item['quantidade_xml'] ?? null,
                    'quantidade_compra' => $item['quantidade_compra'] ?? null
                ]);
            }

            foreach($resultadoFaturas as $fat){
                NfeDivergencia::create([
                    'nfe_id' => $compra->id,
                    'tipo' => 'fatura',
                    'status' => $fat['status'],

                    'valor_xml' => $fat['valor_xml'] ?? null,
                    'valor_compra' => $fat['valor_compra'] ?? null,

                    'vencimento_xml' => $fat['vencimento_xml'] ?? null,
                    'vencimento_compra' => $fat['vencimento_compra'] ?? null
                ]);
            }

            if($divergenciaTotal){
                NfeDivergencia::create([
                    'nfe_id' => $compra->id,
                    'tipo' => 'total',
                    'status' => 'Divergência no total da fatura'
                ]);
            }
        }

        return response()->json([
            'divergencias_itens' => $resultadoItens,
            'divergencias_faturas' => $resultadoFaturas,
            'divergencia_total_fatura' => $divergenciaTotal
        ]);

    }
}
