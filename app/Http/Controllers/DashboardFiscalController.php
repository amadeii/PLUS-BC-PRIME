<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\Nfce;
use Carbon\Carbon;

class DashboardFiscalController extends Controller
{
    public function dashboardFiscal(Request $request)
    {
        $start_date = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $end_date = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        $periodo = [
            Carbon::parse($start_date)->startOfDay(),
            Carbon::parse($end_date)->endOfDay()
        ];

        $estados = ['novo', 'aprovado', 'cancelado', 'rejeitado'];

        $resumo = [
            'nfe' => [],
            'nfce' => [],
        ];

        foreach($estados as $estado){
            $resumo['nfe'][$estado] = [
                'qtd' => Nfe::where('empresa_id', request()->empresa_id ?? auth()->user()->empresa_id)->where('estado', $estado)->whereBetween('created_at', $periodo)->count(),
                'valor' => Nfe::where('empresa_id', request()->empresa_id ?? auth()->user()->empresa_id)->where('estado', $estado)->whereBetween('created_at', $periodo)->sum('total'),
                'items' => Nfe::with('cliente')->where('empresa_id', request()->empresa_id ?? auth()->user()->empresa_id)->where('estado', $estado)->whereBetween('created_at', $periodo)->orderBy('id', 'desc')->limit(80)->get()
            ];

            $resumo['nfce'][$estado] = [
                'qtd' => Nfce::where('empresa_id', request()->empresa_id ?? auth()->user()->empresa_id)->where('estado', $estado)->whereBetween('created_at', $periodo)->count(),
                'valor' => Nfce::where('empresa_id', request()->empresa_id ?? auth()->user()->empresa_id)->where('estado', $estado)->whereBetween('created_at', $periodo)->sum('total'),
                'items' => Nfce::with('cliente')->where('empresa_id', request()->empresa_id ?? auth()->user()->empresa_id)->where('estado', $estado)->whereBetween('created_at', $periodo)->orderBy('id', 'desc')->limit(80)->get()
            ];
        }

        return view('dashboard_fiscal.index', compact('resumo', 'start_date', 'end_date'));
    }
}
