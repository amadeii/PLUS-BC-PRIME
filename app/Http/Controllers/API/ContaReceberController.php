<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\ContaReceber;
use Carbon\Carbon;

class ContaReceberController extends Controller
{
    public function recorrencia(Request $request)
    {
        $data = explode("/", $request->data);
        $vencimento = $request->vencimento;
        $valor = $request->valor;

        $dia = \Carbon\Carbon::parse($vencimento)->format('d');

        $novaData = "20" . $data[1] . "-" . $data[0] . "-" . $dia;
        $dif = strtotime($novaData) - strtotime($vencimento);

        $meses = floor($dif / (60 * 60 * 24 * 30));

        $datas = [];
        $data = $vencimento;
        // return response()->json($meses, 200);
        for ($i = 0; $i < $meses; $i++) {
            $data = date('Y-m-d', strtotime("+30 days", strtotime($data)));
            array_push($datas, $data);
        }

        return view('conta-receber.partials.recorrencia', compact('datas', 'valor'));
    }

    public function faturasDoCliente(Request $request)
    {
        $cliente_id = $request->cliente_id;

        $cliente = Cliente::findOrFail($cliente_id);
        $data = ContaReceber::where('status', 0)
        ->where('cliente_id', $cliente_id)->orderBy('data_vencimento')
        ->get();

        $totalPendente = 0;
        $totalAtrasado = 0;
        $hoje = Carbon::today();
        foreach ($data as $c) {
            if (Carbon::parse($c->data_vencimento)->lt($hoje)) {
                $totalAtrasado += $c->valor_integral;
            } else {
                $totalPendente += $c->valor_integral;
            }
        }

        return view('conta-receber.partials.faturas_cliente', compact('data', 'cliente', 'totalAtrasado', 'totalPendente'));
    }

    public function faturasDoClientePdv4(Request $request)
    {
        $cliente_id = $request->cliente_id;

        $cliente = Cliente::findOrFail($cliente_id);

        $contas = ContaReceber::where('status', 0)
        ->where('cliente_id', $cliente_id)
        ->orderBy('data_vencimento')
        ->get();

        $totalPendente = 0;
        $totalAtrasado = 0;
        $hoje = Carbon::today();

        foreach ($contas as $c) {
            if (Carbon::parse($c->data_vencimento)->lt($hoje)) {
                $totalAtrasado += $c->valor_integral;
            } else {
                $totalPendente += $c->valor_integral;
            }
        }

        return response()->json([
            'cliente' => $cliente,
            'contas' => $contas,
            'total_pendente' => $totalPendente,
            'total_atrasado' => $totalAtrasado,
            'total_aberto' => $totalPendente + $totalAtrasado
        ]);
    }

    public function historicoRecebimento($id)
    {

        $conta = ContaReceber::find($id);
        $contas = ContaReceber::where('conta_receber_origem_id', $id)
        ->get();

        if ($conta) {
            $contas->prepend($conta);
        }

        $html = '';

        foreach ($contas as $c) {

            $valor_recebido = __moeda($c->valor_recebido);
            $valor_original = __moeda($c->valor_original);

            $dataVenc = $c->data_vencimento 
            ? date('d/m/Y', strtotime($c->data_vencimento)) 
            : '-';

            $dataPag = $c->data_recebimento 
            ? date('d/m/Y', strtotime($c->data_recebimento)) 
            : '-';

            $status = $c->status == 1
            ? '<span class="badge bg-success">Recebido</span>'
            : '<span class="badge bg-warning">Pendente</span>';

            $html .= "
            <tr>
            <td>{$c->numero_sequencial}</td>
            <td>R$ {$valor_original}</td>
            <td>R$ {$valor_recebido}</td>
            <td>{$dataVenc}</td>
            <td>{$dataPag}</td>
            <td>{$status}</td>
            </tr>
            ";
        }

        return response()->json($html);
    }
}
