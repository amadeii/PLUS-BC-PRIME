<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecorrenciaCobranca;

class RecorrenciaCobrancaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:recorrencia_cobranca_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:recorrencia_cobranca_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:recorrencia_cobranca_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = RecorrenciaCobranca::where('empresa_id', request()->empresa_id)
        ->with(['cliente', 'recorrencia'])
        ->when(!empty($request->cliente_id), function ($q) use ($request) {
            return $q->where('cliente_id', $request->cliente_id);
        })
        ->when(!empty($request->status), function ($q) use ($request) {
            return $q->where('status', $request->status);
        })
        ->when(!empty($request->data_inicio), function ($q) use ($request) {
            return $q->whereDate('data_vencimento', '>=', $request->data_inicio);
        })
        ->when(!empty($request->data_fim), function ($q) use ($request) {
            return $q->whereDate('data_vencimento', '<=', $request->data_fim);
        })
        ->orderBy('data_vencimento', 'desc')
        ->paginate(__itensPagina());

        return view('recorrencia_cobrancas.index', compact('data'));
    }

    public function show($id)
    {
        $item = RecorrenciaCobranca::with(['cliente', 'recorrencia'])
        ->findOrFail($id);

        __validaObjetoEmpresa($item);

        return view('recorrencia_cobrancas.show', compact('item'));
    }

    public function marcarPago($id)
    {
        $item = RecorrenciaCobranca::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->status = 'pago';
            $item->pago_em = now();
            $item->save();

            session()->flash("flash_success", "Cobrança marcada como paga!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->back();
    }

    public function cancelar($id)
    {
        $item = RecorrenciaCobranca::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->status = 'cancelado';
            $item->cancelado_em = now();
            $item->save();

            session()->flash("flash_success", "Cobrança cancelada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->back();
    }

    public function destroy($id)
    {
        $item = RecorrenciaCobranca::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->delete();
            session()->flash("flash_success", "Cobrança removida com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-cobrancas.index');
    }
}