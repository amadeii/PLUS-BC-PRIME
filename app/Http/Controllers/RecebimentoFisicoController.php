<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\CompraConferencia;
use App\Models\ContaPagar;
use Dompdf\Dompdf;
use Dompdf\Options;

class RecebimentoFisicoController extends Controller
{
    public function show($id){
        $item = Nfe::findOrFail($id);

        $item->user_id = \Auth::user()->id;
        $item->save();

        $conferencia = CompraConferencia::with('itens')
        ->where('compra_id', $item->id)
        ->first();

        return view('recebimento_fisico.show', compact('item', 'conferencia'));
    }

    public function store(Request $request)
    {
        $item = Nfe::with('itens')->findOrFail($request->input_id);

        $request->validate([
            'observacao' => 'nullable|string',
            'itens' => 'required|array|min:1',
            'itens.*.compra_item_id' => 'required|integer',
            'itens.*.quantidade_xml' => 'required',
            'itens.*.quantidade_conferida' => 'nullable',
            'itens.*.observacao' => 'nullable|string',
        ]);

        \DB::beginTransaction();

        try {
            $conferencia = CompraConferencia::updateOrCreate(
                [
                    'compra_id' => $item->id
                ],
                [
                    'empresa_id' => $item->empresa_id,
                    'user_id' => auth()->id(),
                    'observacao' => $request->observacao,
                    'conferido_em' => now(),
                    'status' => 'pendente'
                ]
            );

        // apaga os itens antigos para recriar
            $conferencia->itens()->delete();

            $temDivergencia = false;

            foreach ($request->itens as $it) {
                $qtdXml = (float) str_replace(',', '.', $it['quantidade_xml'] ?? 0);
                $qtdConferida = (float) str_replace(',', '.', $it['quantidade_conferida'] ?? 0);
                $diferenca = $qtdConferida - $qtdXml;

                if (bccomp((string)$qtdXml, (string)$qtdConferida, 4) !== 0) {
                    $temDivergencia = true;
                }

                $conferencia->itens()->create([
                    'item_compra_id' => $it['compra_item_id'],
                    'qtd_xml' => $qtdXml,
                    'qtd_conferida' => $qtdConferida,
                    'diferenca' => $diferenca,
                    'observacao' => $it['observacao'] ?? null,
                ]);
            }

            $conferencia->update([
                'status' => $temDivergencia ? 'divergente' : 'conferido'
            ]);

            if($temDivergencia){
                ContaPagar::where('nfe_id', $item->id)->update(['desativado' => 1]);
            }else{
                ContaPagar::where('nfe_id', $item->id)->update(['desativado' => 0]);
            }

            \DB::commit();

            return redirect()
            ->route('recebimento-fisico.show', $item->id)
            ->with('flash_success', $temDivergencia
                ? 'Conferência salva com divergências.'
                : 'Conferência salva com sucesso.');
        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
            ->back()
            ->withInput()
            ->with('flash_error', 'Erro ao salvar conferência: ' . $e->getMessage());
        }
    }

    public function impressao($id)
    {
        $item = Nfe::with([
            'fornecedor',
            'itens.produto'
        ])->findOrFail($id);

        $html = view('recebimento_fisico.impressao', compact('item'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return response($dompdf->stream(
            'ficha-cega-'.$item->id.'.pdf',
            ["Attachment" => false]
        ));
    }
}
