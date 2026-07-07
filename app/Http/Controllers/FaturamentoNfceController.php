<?php

namespace App\Http\Controllers;

use App\Models\Nfce;
use App\Models\ConfigGeral;
use App\Models\UsuarioEmissao;
use App\Models\Localizacao;
use App\Models\Empresa;
use Illuminate\Http\Request;

class FaturamentoNfceController extends Controller
{

    private function corrigeNumeros($empresa_id){

        $config = ConfigGeral::where('empresa_id', $empresa_id)->first();
        if($config != null && $config->corrigir_numeracao_fiscal == 0){
            return;
        }
        
        $item = UsuarioEmissao::where('usuario_empresas.empresa_id', request()->empresa_id)
        ->join('usuario_empresas', 'usuario_empresas.usuario_id', '=', 'usuario_emissaos.usuario_id')
        ->select('usuario_emissaos.*')
        ->where('usuario_emissaos.usuario_id', get_id_user())
        ->first();

        if($item != null){
            return;
        }

        $locais = Localizacao::where('empresa_id', $empresa_id)->where('status', 1)->get();
        foreach($locais as $key => $local){
            $empresa = Empresa::findOrFail($empresa_id);
            $caixa = __isCaixaAberto();
            if($caixa){
                $empresa = __objetoParaEmissao($empresa, $local->id);
            }


            if($empresa->ambiente == 1){
                $numero = $empresa->numero_ultima_nfce_producao;
            }else{
                $numero = $empresa->numero_ultima_nfce_homologacao;
            }

            if($numero){
                Nfce::where(function($q) {
                    $q->where('estado', 'novo')->orWhere('estado', 'rejeitado');
                })
                ->where('empresa_id', $empresa_id)
                ->where('local_id', $local->id)
            // ->where('caixa_id', $caixa->id)
                ->update(['numero' => $numero+1]);
            }
        }
    }

    public function index(Request $request)
    {
        $empresa_id = $request->empresa_id;
        $this->corrigeNumeros($request->empresa_id);

        $base = Nfce::where('empresa_id', $empresa_id);

        $query = Nfce::with('cliente')
        ->where('empresa_id', $empresa_id)
        ->when($request->cliente_id, fn($q) => $q->where('cliente_id', $request->cliente_id))
        ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
        ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
        ->when($request->estado, fn($q) => $q->where('estado', $request->estado));

        $resumo = [
            'novas' => (clone $base)->where('estado', 'novo')->count(),
            'novas_valor' => (clone $base)->where('estado', 'novo')->sum('total'),

            'emitidas_hoje' => (clone $base)->where('estado', 'aprovado')->whereDate('created_at', today())->count(),
            'emitidas_hoje_valor' => (clone $base)->where('estado', 'aprovado')->whereDate('created_at', today())->sum('total'),

            'canceladas' => (clone $base)->where('estado', 'cancelado')->count(),
            'canceladas_valor' => (clone $base)->where('estado', 'cancelado')->sum('total'),

            'rejeitadas' => (clone $base)->where('estado', 'rejeitado')->count(),
            'rejeitadas_valor' => (clone $base)->where('estado', 'rejeitado')->sum('total'),

            'valor_hoje' => (clone $base)->where('estado', 'aprovado')->whereDate('created_at', today())->sum('total'),

            'emitidas_total' => (clone $base)->where('estado', 'aprovado')->count(),
        ];

        $items = $query->orderByDesc('id')->paginate(10);

        return view('faturamento_nfce.index', compact('items', 'resumo'));
    }

    public function show($id)
    {
        $item = Nfce::with(['cliente', 'itens.produto'])->findOrFail($id);

        return view('faturamento_nfce.show', compact('item'));
    }

    public function downloadXml(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'estado' => 'nullable|string'
        ]);

        $items = Nfce::where('empresa_id', $request->empresa_id ?? __empresa_id())
        ->whereDate('created_at', '>=', $request->start_date)
        ->whereDate('created_at', '<=', $request->end_date)
        ->when($request->estado, fn($q) => $q->where('estado', $request->estado), fn($q) => $q->where('estado', 'aprovado'))
        ->get();

        if($items->count() == 0){
            session()->flash('flash_error', 'Nenhum XML encontrado para o período informado.');
            return redirect()->back();
        }

        $zipNome = 'xml_nfce_' . now()->format('Ymd_His') . '.zip';
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
                $xmlPath = public_path('xml_nfce/' . $item->chave . '.xml');
            }

            if($item->estado == 'cancelado'){
                $xmlPath = public_path('xml_nfce_cancelada/' . $item->chave . '.xml');
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

    public function lote(Request $request)
    {
        $perPage = $request->per_page ?? 30;

        $items = Nfce::where('empresa_id', $request->empresa_id)
        ->whereIn('estado', ['novo', 'rejeitado'])
        ->when($request->cliente_id, function($q) use ($request){
            return $q->where('cliente_id', $request->cliente_id);
        })
        ->when($request->start_date, function($q) use ($request){
            return $q->whereDate('created_at', '>=', $request->start_date);
        })
        ->when($request->end_date, function($q) use ($request){
            return $q->whereDate('created_at', '<=', $request->end_date);
        })
        ->when($request->tipo_pagamento, function($q) use ($request){
            $q->where(function($sub) use ($request){
                $sub->where('tipo_pagamento', $request->tipo_pagamento)
                ->orWhereHas('fatura', function($f) use ($request){
                    $f->where('tipo_pagamento', $request->tipo_pagamento);
                });
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        $tiposPagamento = Nfce::tiposPagamento();

        return view('faturamento_nfce.lote', compact('items', 'tiposPagamento'));
    }
}