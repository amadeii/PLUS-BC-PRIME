<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\Cte;
use App\Models\Mdfe;
use App\Models\NotaServico;
use App\Models\OrdemServico;

use App\Models\PlanoEmpresa;
use App\Models\ContadorEmpresa;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        return view('super_admin.dashboard_totais');
    }

    public function totais(Request $request)
    {
        [$dataInicial, $dataFinal] = $this->getPeriodo($request);

        $totalNfe = Nfe::where('estado', 'aprovado')
        ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 1)
        ->count();

        $valorNfe = Nfe::where('estado', 'aprovado')
        ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
        ->where('orcamento', 0)
        ->where('tpNF', 1)
        ->where('finNFe', '!=', 4)
        ->sum('total');

        $totalNfce = Nfce::where('estado', 'aprovado')
        ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
        ->count();

        $valorNfce = Nfce::where('estado', 'APROVADO')
        ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
        ->sum('total');

        $totalCte = Cte::where('estado', 'aprovado')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

        $valorCte = Cte::where('estado', 'aprovado')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->sum('valor_receber');

        $totalMdfe = Mdfe::where('estado_emissao', 'aprovado')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

        $valorMdfe = Mdfe::where('estado_emissao', 'aprovado')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->sum('valor_transporte');

        $totalNfse = NotaServico::where('estado', 'aprovado')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

        $valorNfse = NotaServico::where('estado', 'aprovado')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->sum('valor_total');

        $totalVendasPdv = Nfce::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

        $valorVendasPdv = Nfce::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->sum('total');

        $totalVendasPedido = Nfe::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 1)
        ->count();

        $valorVendasPedido = Nfe::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 1)
        ->sum('total');

        $totalCompras = Nfe::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 0)
        ->count();

        $valorCompras = Nfe::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 0)
        ->sum('total');

        $totalOs = OrdemServico::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

        $valorOs = OrdemServico::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->sum('valor');

        return response()->json([
            'periodo' => [
                'data_inicial' => $dataInicial->format('d/m/Y H:i'),
                'data_final' => $dataFinal->format('d/m/Y H:i'),
            ],

            'cards' => [
                [
                    'titulo' => 'Emissões NFe',
                    'total' => $totalNfe,
                    'valor' => $this->moeda($valorNfe),
                    'icone' => 'file-text'
                ],
                [
                    'titulo' => 'Emissões NFCe',
                    'total' => $totalNfce,
                    'valor' => $this->moeda($valorNfce),
                    'icone' => 'shopping-cart'
                ],
                [
                    'titulo' => 'Emissões CTe',
                    'total' => $totalCte,
                    'valor' => $this->moeda($valorCte),
                    'icone' => 'truck'
                ],
                [
                    'titulo' => 'Emissões MDFe',
                    'total' => $totalMdfe,
                    'valor' => $this->moeda($valorMdfe),
                    'icone' => 'route'
                ],
                [
                    'titulo' => 'Emissões NFSe',
                    'total' => $totalNfse,
                    'valor' => $this->moeda($valorNfse),
                    'icone' => 'receipt'
                ],
                [
                    'titulo' => 'Vendas PDV',
                    'total' => $totalVendasPdv,
                    'valor' => $this->moeda($valorVendasPdv),
                    'icone' => 'monitor'
                ],
                [
                    'titulo' => 'Vendas Pedido',
                    'total' => $totalVendasPedido,
                    'valor' => $this->moeda($valorVendasPedido),
                    'icone' => 'package'
                ],
                [
                    'titulo' => 'Compras',
                    'total' => $totalCompras,
                    'valor' => $this->moeda($valorCompras),
                    'icone' => 'shopping-bag'
                ],
                [
                    'titulo' => 'Ordens de Serviço',
                    'total' => $totalOs,
                    'valor' => $this->moeda($valorOs),
                    'icone' => 'settings'
                ],
            ]
        ]);
    }

    private function getPeriodo(Request $request)
    {
        $tipo = $request->tipo ?? 'dia';

        if ($tipo == 'semana') {
            return [
                Carbon::now()->startOfWeek()->startOfDay(),
                Carbon::now()->endOfWeek()->endOfDay()
            ];
        }

        if ($tipo == 'mes') {
            return [
                Carbon::now()->startOfMonth()->startOfDay(),
                Carbon::now()->endOfMonth()->endOfDay()
            ];
        }

        if ($tipo == 'ano') {
            return [
                Carbon::now()->startOfYear()->startOfDay(),
                Carbon::now()->endOfYear()->endOfDay()
            ];
        }

        if ($tipo == 'personalizado') {
            $inicio = $request->data_inicial
            ? Carbon::parse($request->data_inicial)->startOfDay()
            : Carbon::now()->startOfDay();

            $fim = $request->data_final
            ? Carbon::parse($request->data_final)->endOfDay()
            : Carbon::now()->endOfDay();

            return [$inicio, $fim];
        }

        return [
            Carbon::now()->startOfDay(),
            Carbon::now()->endOfDay()
        ];
    }

    private function moeda($valor)
    {
        return 'R$ ' . number_format((float)$valor, 2, ',', '.');
    }

    public function detalhado()
    {
        return view('super_admin.dashboard_detalhado');
    }

    public function detalhadoAjax(Request $request)
    {
        [$dataInicial, $dataFinal] = $this->getPeriodo($request);

        $empresa_id = $request->emp_id;

        if(!$empresa_id){
            return response()->json([
                'periodo' => [
                    'inicio' => $dataInicial->format('d/m/Y'),
                    'fim' => $dataFinal->format('d/m/Y'),
                ],
                'data' => []
            ]);
        }

        $empresa = Empresa::findOrFail($empresa_id);

        $planoEmpresa = PlanoEmpresa::with('plano')
        ->where('empresa_id', $empresa_id)
        ->orderBy('id', 'desc')
        ->first();

        $contador = null;

        if($empresa->contador_id > 0){
            $contador = ContadorEmpresa::find($empresa->contador_id);
        }

        $empresa = Empresa::findOrFail($empresa_id);

        $vendas = Nfe::where('empresa_id', $empresa_id)
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 1)
        ->whereBetween('created_at', [$dataInicial, $dataFinal]);

        $pdv = Nfce::where('empresa_id', $empresa_id)
        ->whereBetween('created_at', [$dataInicial, $dataFinal]);

        $contasReceber = ContaReceber::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->whereBetween('data_recebimento', [$dataInicial, $dataFinal]);

        $contasPagar = ContaPagar::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->whereBetween('data_pagamento', [$dataInicial, $dataFinal]);

        $compras = Nfe::where('empresa_id', $empresa_id)
        ->where('orcamento', 0)
        ->where('finNFe', '!=', 4)
        ->where('tpNF', 0)
        ->whereBetween('created_at', [$dataInicial, $dataFinal]);

        $ordensServico = OrdemServico::where('empresa_id', $empresa_id)
        ->whereBetween('created_at', [$dataInicial, $dataFinal]);

        return response()->json([
            'periodo' => [
                'inicio' => $dataInicial->format('d/m/Y'),
                'fim' => $dataFinal->format('d/m/Y'),
            ],
            'data' => [[
                'empresa' => $empresa->nome,

                'pedido_total' => $vendas->count(),
                'pedido_valor' => $this->moeda(
                    $vendas->sum(DB::raw('COALESCE(total,0)'))
                ),

                'pdv_total' => $pdv->count(),
                'pdv_valor' => $this->moeda(
                    $pdv->sum(DB::raw('COALESCE(total,0)'))
                ),

                'receber_total' => $contasReceber->count(),
                'receber_valor' => $this->moeda(
                    $contasReceber->sum(DB::raw('COALESCE(valor_recebido,0) + COALESCE(valor_juros,0) + COALESCE(valor_multa,0)'))
                ),

                'pagar_total' => $contasPagar->count(),
                'pagar_valor' => $this->moeda(
                    $contasPagar->sum(DB::raw('COALESCE(valor_pago,0)'))
                ),

                'compras_total' => $compras->count(),
                'compras_valor' => $this->moeda(
                    $compras->sum(DB::raw('COALESCE(total,0)'))
                ),

                'os_total' => $ordensServico->count(),
                'os_valor' => $this->moeda(
                    $ordensServico->sum(DB::raw('COALESCE(valor,0)'))
                ),

                'plano_nome' => $planoEmpresa && $planoEmpresa->plano ? $planoEmpresa->plano->nome : 'Sem plano',
                'plano_valor' => $planoEmpresa ? $this->moeda($planoEmpresa->valor) : 'R$ 0,00',
                'plano_expiracao' => $planoEmpresa && $planoEmpresa->expiracao ? \Carbon\Carbon::parse($planoEmpresa->expiracao)->format('d/m/Y') : '--',

                'empresa_cadastro' => $empresa->created_at ? $empresa->created_at->format('d/m/Y') : '--',
                'contador_nome' => $contador ? $contador->contador->nome : 'Sem contador',
            ]]
        ]);
    }
}
