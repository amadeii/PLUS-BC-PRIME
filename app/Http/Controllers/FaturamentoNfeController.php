<?php

namespace App\Http\Controllers;

use App\Models\Nfe;
use App\Models\Cliente;
use Illuminate\Http\Request;

class FaturamentoNfeController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = $request->empresa_id ?? __getEmpresa()->id;

        $base = Nfe::where('empresa_id', $empresa_id)
        ->where('tpNF', 1)
        ->where('orcamento', 0);

        $query = Nfe::with('cliente')
        ->where('empresa_id', $empresa_id)
        ->where('tpNF', 1)
        ->where('orcamento', 0)
        ->when($request->cliente_id, fn($q) => $q->where('cliente_id', $request->cliente_id))
        ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
        ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
        ->when($request->estado_fatura, fn($q) => $q->where('estado_fatura', $request->estado_fatura))
        ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
        ->when($request->situacao_fiscal, fn($q) => $q->where('fiscal_status', $request->situacao_fiscal))
        ->when($request->cce, fn($q) => $q->where('sequencia_cce', '>', 0));

        $resumo = [
            'pendentes' => (clone $base)->where('estado_fatura', 'pendente')->count(),
            'pendentes_valor' => (clone $base)->where('estado_fatura', 'pendente')->sum('total'),

            'prontos' => (clone $base)->where('estado_fatura', 'pendente')->where('estado', 'novo')->where(function($q){
                $q->whereNull('fiscal_status')->orWhere('fiscal_status', 'ok');
            })->count(),

            'prontos_valor' => (clone $base)->where('estado_fatura', 'pendente')->where('estado', 'novo')->where(function($q){
                $q->whereNull('fiscal_status')->orWhere('fiscal_status', 'ok');
            })->sum('total'),

            'emitidas_hoje' => (clone $base)->whereIn('estado_fatura', ['aprovado', 'finalizado'])->whereDate('data_faturamento', today())->count(),
            'emitidas_hoje_valor' => (clone $base)->whereIn('estado_fatura', ['aprovado', 'finalizado'])->whereDate('data_faturamento', today())->sum('total'),

            'canceladas' => (clone $base)->where('estado', 'cancelado')->count(),
            'canceladas_valor' => (clone $base)->where('estado', 'cancelado')->sum('total'),

            'valor_hoje' => (clone $base)->whereIn('estado_fatura', ['aprovado', 'finalizado'])->whereDate('data_faturamento', today())->sum('total'),

            'aguardando_sefaz' => (clone $base)->where('estado_fatura', 'aprovado')->whereIn('estado', ['novo', 'processando'])->count(),
            'aguardando_sefaz_valor' => (clone $base)->where('estado_fatura', 'aprovado')->whereIn('estado', ['novo', 'processando'])->sum('total'),

            'rejeitadas' => (clone $base)->where('estado', 'rejeitado')->count(),
            'rejeitadas_valor' => (clone $base)->where('estado', 'rejeitado')->sum('total'),

            'faturamento_pendente' => (clone $base)->where('estado_fatura', 'pendente')->count(),
            'faturamento_aprovado' => (clone $base)->where('estado_fatura', 'aprovado')->count(),
            'faturamento_finalizado' => (clone $base)->where('estado_fatura', 'finalizado')->count(),

            'cartas_correcao' => (clone $base)->where('sequencia_cce', '>', 0)->count(),

            'emitidas_total' => (clone $base)->whereIn('estado', ['aprovado'])->count(),
        ];

        // $clientes = Cliente::where('empresa_id', $empresa_id)
        // ->orderBy('razao_social')
        // ->get();

        $items = $query->orderByDesc('id')->paginate(10);

        return view('faturamento_nfe.index', compact('items', 'resumo'));
    }

    public function faturar(Request $request, $id)
    {
        $item = Nfe::findOrFail($id);

        if ($item->estado_fatura != 'pendente') {
            session()->flash('flash_error', 'Esta NF-e já foi enviada para faturamento!');
            return redirect()->back();
        }

        if ($item->estado == 'cancelado') {
            session()->flash('flash_error', 'Não é possível faturar uma NF-e cancelada!');
            return redirect()->back();
        }

        if ($item->estado == 'aprovado') {
            session()->flash('flash_error', 'Esta NF-e já está autorizada!');
            return redirect()->back();
        }

        $item->estado_fatura = 'aprovado';
        $item->data_faturamento = $request->data_faturamento ?? now();
        $item->observacao_faturamento = $request->observacao_faturamento;
        $item->save();

        return redirect()->route('faturamento-nfe.index', $item->id);
    }

    public function show($id)
    {
        $item = Nfe::with([
            'cliente',
            'itens.produto'
        ])->findOrFail($id);

        return view('faturamento_nfe.show', compact('item'));
    }

    public function lote(Request $request)
    {
        $items = Nfe::where('empresa_id', $request->empresa_id)
        ->where('estado', 'novo')
        ->where('tpNF', 1)
        ->where('orcamento', 0)
        ->where('estado_fatura', 'pendente')
        ->when($request->cliente_id, function($q) use ($request){
            return $q->where('cliente_id', $request->cliente_id);
        })
        ->when($request->start_date, function($q) use ($request){
            return $q->whereDate('created_at', '>=', $request->start_date);
        })
        ->when($request->end_date, function($q) use ($request){
            return $q->whereDate('created_at', '<=', $request->end_date);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('faturamento_nfe.lote', compact('items'));
    }

    public function downloadXml(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'estado' => 'nullable|string'
        ]);

        $items = Nfe::where('empresa_id', $request->empresa_id ?? __empresa_id())
        ->whereDate('created_at', '>=', $request->start_date)
        ->whereDate('created_at', '<=', $request->end_date)
        ->when($request->estado, function($q) use ($request){
            $q->where('estado', $request->estado);
        }, function($q){
            $q->where('estado', 'aprovado');
        })
        ->where('tpNF', 1)
        ->where('orcamento', 0)
        ->get();

        if($items->count() == 0){
            session()->flash('flash_error', 'Nenhum XML encontrado para o período informado.');
            return redirect()->back();
        }

        $zipNome = 'xml_nfe_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipNome);

        if(!is_dir(storage_path('app/temp'))){
            mkdir(storage_path('app/temp'), 0777, true);
        }

        $zip = new \ZipArchive();

        if($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true){
            session()->flash('flash_error', 'Não foi possível gerar o arquivo ZIP.');
            return redirect()->back();
        }

        foreach($items as $item){

            $xmlPath = null;

            if($item->estado == 'aprovado'){
                $xmlPath = public_path('xml_nfe/' . $item->chave . '.xml');
            }

            if($item->estado == 'cancelado'){
                $xmlPath = public_path('xml_nfe_cancelada/' . $item->chave . '.xml');
            }

            if($xmlPath && file_exists($xmlPath)){
                $nomeXml = ($item->numero ?? $item->id) . '_' . $item->chave . '.xml';
                $zip->addFile($xmlPath, $nomeXml);
            }
        }

        $zip->close();

        if(!file_exists($zipPath) || filesize($zipPath) == 0){
            session()->flash('flash_error', 'Nenhum arquivo XML físico foi encontrado.');
            return redirect()->back();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


}