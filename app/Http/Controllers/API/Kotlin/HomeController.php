<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\Produto;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request){

        $empresaId = $request->empresa_id;
        $mes = \Carbon\Carbon::now()->month;
        $ano = \Carbon\Carbon::now()->year;

        $totalNfe = Nfe::where('empresa_id', $empresaId)
        ->whereMonth('created_at', $mes)
        ->whereYear('created_at', $ano)
        ->where('estado', '!=', 'cancelado')
        ->where('tpNF', 1)
        ->where('orcamento', 0)
        ->sum('total');

        $totalNfce = Nfce::where('empresa_id', $empresaId)
        ->whereMonth('created_at', $mes)
        ->whereYear('created_at', $ano)
        ->where('estado', '!=', 'cancelado')
        ->sum('total');

        $totalVendasMes = $totalNfe + $totalNfce;

        $totalProdutos = Produto::
        where('empresa_id', $empresaId)
        ->where('status', 1)
        ->count();

        $somaContaReceber = ContaReceber::
        where('empresa_id', $empresaId)
        ->where('status', 0)
        ->whereMonth('data_vencimento', $mes)
        ->whereYear('data_vencimento', $ano)
        ->sum('valor_integral');

        $somaContaPagar = ContaPagar::
        where('empresa_id', $empresaId)
        ->where('status', 0)
        ->whereMonth('data_vencimento', $mes)
        ->whereYear('data_vencimento', $ano)
        ->sum('valor_integral');

        $inicio = Carbon::now()->subDays(6)->startOfDay();
        $fim    = Carbon::now()->endOfDay();

        $vendasDiarias = DB::query()
        ->fromSub(function ($query) use ($inicio, $fim, $empresaId) {

            $query->from('nves')
            ->selectRaw('DATE(created_at) as data, SUM(total) as total')
            ->where('empresa_id', $empresaId)
            ->where('estado', '!=', 'cancelado')
            ->whereBetween('created_at', [$inicio, $fim])
            ->where('tpNF', 1)
            ->groupByRaw('DATE(created_at)')

            ->unionAll(

                DB::table('nfces')
                ->selectRaw('DATE(created_at) as data, SUM(total) as total')
                ->whereBetween('created_at', [$inicio, $fim])
                ->where('empresa_id', $empresaId)
                ->where('estado', '!=', 'cancelado')
                ->groupByRaw('DATE(created_at)')
            );

        }, 'vendas')
        ->selectRaw('data, SUM(total) as total')
        ->groupBy('data')
        ->orderBy('data')
        ->get();

        $data = [
            'vendas_mensais' => $totalVendasMes,
            'contas_receber' => $somaContaReceber,
            'contas_pagar'   => $somaContaPagar,
            'total_produtos' => $totalProdutos,
            'vendas_diarias' => $vendasDiarias
        ];
        return response()->json($data, 200);
    }
}
