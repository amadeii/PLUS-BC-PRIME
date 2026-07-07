<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\Cliente;
use App\Models\SicrediConfig;
use App\Models\AsaasConfig;
use App\Models\CobrancaBancaria;
use Illuminate\Support\Facades\DB;
use App\Utils\SicrediUtil;
use App\Utils\AsaasUtil;

class CobrancaBancariaController extends Controller
{
    public function index(Request $request)
    {

        $cliente_id = $request->cliente_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = ContaReceber::query()
        ->where('empresa_id', request()->empresa_id)
        ->where('status', 0)
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('conta_recebers.cliente_id', $cliente_id);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('conta_recebers.data_vencimento', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('conta_recebers.data_vencimento', '<=', $end_date);
        })
        ->whereDoesntHave('cobrancaBancaria')
        ->orderBy('data_vencimento', 'asc')
        ->paginate(20);

        $cliente = null;
        if($cliente_id){
            $cliente = Cliente::findOrFail($cliente_id);
        }

        return view('cobranca_bancaria.index', compact('data', 'cliente'));
    }

    public function create(Request $request)
    {
        $ids = explode(',', $request->ids);

        $contas = ContaReceber::with('cliente')
        ->where('empresa_id', request()->empresa_id)
        ->where('status', 0)
        ->whereDoesntHave('cobrancaBancaria')
        ->whereIn('id', $ids)
        ->orderBy('data_vencimento', 'asc')
        ->get();

        if($contas->isEmpty()){
            return redirect()->route('cobranca-bancaria.index')
            ->with('flash_error', 'Nenhum título válido foi encontrado.');
        }

        $total = $contas->sum('valor_integral');

        $contasBancarias = __getBancosAtivos($request->empresa_id);

        return view('cobranca_bancaria.create', compact('contas', 'total', 'contasBancarias'));
    }

    public function bancoDados(Request $request)
    {
        $request->validate([
            'banco' => 'required|string'
        ]);

        $empresaId = $request->empresa_id;

        if ($request->banco == 'sicredi') {
            $config = SicrediConfig::where('empresa_id', $empresaId)->first();

            if (!$config) {
                return response()->json([
                    'status' => false,
                    'message' => 'Configuração do Sicredi não encontrada.'
                ], 404);
            }

            return response()->json([
                'banco' => 'sicredi',
                'juros' => (float)$config->juros_padrao,
                'multa' => (float)$config->multa_padrao,
                'instrucao' => $config->observacao_padrao,
                'ultimo_numero_boleto' => (int)$config->ultimo_numero_boleto,
            ]);
        }

        if ($request->banco == 'asaas') {
            $config = AsaasConfig::where('empresa_id', $empresaId)->first();

            if (!$config) {
                return response()->json([
                    'status' => false,
                    'message' => 'Configuração do Asaas não encontrada.'
                ], 404);
            }

            return response()->json([
                'banco' => 'asaas',
                'juros' => (float)$config->juros_padrao,
                'multa' => (float)$config->multa_padrao,
                'instrucao' => $config->observacao_padrao,
                'ultimo_numero_boleto' => (int)$config->ultimo_numero_boleto,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Banco não suportado.'
        ], 400);
    }

    public function store(Request $request)
    {

        $request->validate([
            'banco' => 'required|string',
            'boletos' => 'required|array|min:1',
            'boletos.*.conta_receber_id' => 'required|integer',
            'boletos.*.juros' => 'nullable',
            'boletos.*.multa' => 'nullable',
            'boletos.*.numero' => 'required',
            'boletos.*.instrucao' => 'nullable|string|max:255',
        ]);

        $empresaId = $request->empresa_id;

        $ids = collect($request->boletos)
        ->pluck('conta_receber_id')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

        $contas = ContaReceber::with('cliente')
        ->where('empresa_id', $empresaId)
        ->where('status', 0)
        ->whereDoesntHave('cobrancaBancaria')
        ->whereIn('id', $ids)
        ->get()
        ->keyBy('id');

        if ($contas->isEmpty()) {
            return redirect()->route('cobranca-bancaria.index')
            ->with('flash_error', 'Nenhum título disponível para gerar cobrança.');
        }

        DB::beginTransaction();

        try {
            $ultimoNumeroGerado = 0;

            foreach ($request->boletos as $boleto) {
                $contaId = $boleto['conta_receber_id'] ?? null;

                if (!$contaId || !isset($contas[$contaId])) {
                    continue;
                }

                $conta = $contas[$contaId];

                $juros = $this->converterValorBanco($boleto['juros'] ?? 0);
                $multa = $this->converterValorBanco($boleto['multa'] ?? 0);
                $numero = trim($boleto['numero'] ?? '');
                $instrucao = $boleto['instrucao'] ?? null;

                if ($request->banco == 'sicredi') {
                    $retorno = $this->enviarBoletoSicredi($conta, $numero, $juros, $multa, $instrucao, $empresaId);

                    if (!$retorno['success']) {
                        throw new \Exception($retorno['message']);
                    }

                    CobrancaBancaria::create([
                        'empresa_id' => $empresaId,
                        'conta_receber_id' => $conta->id,
                        'cliente_id' => $conta->cliente_id ?? null,
                        'banco' => $request->banco,
                        'numero' => $numero,
                        'nosso_numero' => $retorno['data']['nossoNumero'] ?? null,
                        'linha_digitavel' => $retorno['data']['linhaDigitavel'] ?? null,
                        'codigo_barras' => $retorno['data']['codigoBarras'] ?? null,
                        'txid' => $retorno['data']['txid'] ?? null,
                        'data_vencimento' => $conta->data_vencimento,
                        'data_emissao' => now(),
                        'valor' => $conta->valor_integral,
                        'juros' => $juros,
                        'multa' => $multa,
                        'instrucao' => $instrucao,
                        'status_banco' => 'GERADO',
                        'payload_envio' => json_encode($retorno['payload_envio'] ?? []),
                        'payload_retorno' => json_encode($retorno['payload_retorno'] ?? []),
                    ]);
                }

                if ((int)$numero > $ultimoNumeroGerado) {
                    $ultimoNumeroGerado = (int)$numero;
                }
            }

            if ($request->banco == 'asaas') {
                $retorno = $this->enviarBoletoAsaas($conta, $numero, $juros, $multa, $instrucao, $empresaId);

                if (!$retorno['success']) {
                    return redirect()->back()
                    ->withInput()
                    ->with('flash_error', $retorno['message']);
                }

                $data = $retorno['data'] ?? [];

                CobrancaBancaria::create([
                    'empresa_id' => $empresaId,
                    'conta_receber_id' => $conta->id,
                    'cliente_id' => $conta->cliente_id ?? null,
                    'banco' => $request->banco,
                    'numero' => $numero,
        // Asaas
                    'nosso_numero' => $data['id'] ?? null,
                    'linha_digitavel' => $data['identificationField'] ?? null,
                    'codigo_barras' => $data['nossoNumero'] ?? null,
                    'txid' => $data['transactionReceiptUrl'] ?? null,
                    'data_emissao' => now(),
                    'data_vencimento' => $conta->data_vencimento,
                    'valor' => $conta->valor_integral,
                    'juros' => $juros,
                    'multa' => $multa,
                    'instrucao' => $instrucao,

                    'status_banco' => $data['status'] ?? 'PENDING',

                    'payload_envio' => json_encode($retorno['payload_envio'] ?? []),
                    'payload_retorno' => json_encode($retorno['payload_retorno'] ?? []),
                ]);
            }

            if ($request->banco == 'sicredi' && $ultimoNumeroGerado > 0) {
                SicrediConfig::where('empresa_id', $empresaId)->update([
                    'ultimo_numero_boleto' => $ultimoNumeroGerado
                ]);
            }

            if ($request->banco == 'asaas' && $ultimoNumeroGerado > 0) {
                AsaasConfig::where('empresa_id', $empresaId)->update([
                    'ultimo_numero_boleto' => $ultimoNumeroGerado
                ]);
            }

            DB::commit();

            return redirect()->route('cobrancas.index')
            ->with('flash_success', 'Cobranças geradas com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
            ->withInput()
            ->with('flash_error', 'Erro ao gerar cobranças: ' . $e->getMessage());
        }
    }

    private function enviarBoletoSicredi($conta, $numero, $juros, $multa, $instrucao, $empresaId)
    {
        $config = SicrediConfig::where('empresa_id', $empresaId)->first();

        if (!$config) {
            return [
                'success' => false,
                'message' => 'Configuração do Sicredi não encontrada.'
            ];
        }

        $cpfCnpj = preg_replace('/\D/', '', $conta->cliente->cpf_cnpj ?? '');
        $tipoPessoa = strlen($cpfCnpj) > 11 ? 'PESSOA_JURIDICA' : 'PESSOA_FISICA';

        $payload = [
            'tipoCobranca' => $config->tipo_cobranca,
            'codigoBeneficiario' => $config->codigo_beneficiario,
            'dataVencimento' => $conta->data_vencimento,
            'especieDocumento' => $config->especie_documento,
            'seuNumero' => (string)($conta->numero_sequencial ?? $conta->id),
            'valor' => (float)$conta->valor_integral,

            'pagador' => [
                'cep' => preg_replace('/\D/', '', $conta->cliente->cep ?? ''),
                'cidade' => $conta->cliente->cidade->nome ?? '',
                'documento' => preg_replace('/\D/', '', $conta->cliente->cpf_cnpj ?? ''),
                'nome' => $conta->cliente->razao_social ?? $conta->cliente->nome ?? '',
                'tipoPessoa' => strlen(preg_replace('/\D/', '', $conta->cliente->cpf_cnpj ?? '')) > 11 ? 'PESSOA_JURIDICA' : 'PESSOA_FISICA',
                'endereco' => ($conta->cliente->rua ?? '') . ' ' . ($conta->cliente->numero ?? ''),
                'uf' => $conta->cliente->cidade->uf ?? '',
            ],

            'tipoJuros' => 'PERCENTUAL',
            'juros' => (float)$juros,

            'tipoMulta' => 'PERCENTUAL',
            'multa' => (float)$multa,

            'mensagem' => $instrucao ?: ($config->observacao_padrao ?? ''),
        ];
        // dd($payload);

        try {
            $body = SicrediUtil::gerarBoleto($payload, $empresaId);

            return [
                'success' => true,
                'message' => 'Boleto gerado com sucesso.',
                'data' => [
                    'nossoNumero' => $body['nossoNumero'] ?? $numero,
                    'linhaDigitavel' => $body['linhaDigitavel'] ?? null,
                    'codigoBarras' => $body['codigoBarras'] ?? null,
                    'txid' => $body['txid'] ?? null,
                ],
                'payload_envio' => $payload,
                'payload_retorno' => $body,
            ];
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'payload_envio' => $payload,
                'payload_retorno' => null,
            ];
        }
    }

    private function converterValorBanco($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim((string)$valor);

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float)$valor;
    }

    private function enviarBoletoAsaas($conta, $numero, $juros, $multa, $instrucao, $empresaId)
    {
        $payload = [];

        try {
            $cliente = $conta->cliente;

            if (!$cliente) {
                throw new \Exception('Cliente não encontrado para gerar boleto.');
            }

            $customerId = $this->obterOuCriarClienteAsaas($cliente, $empresaId);

            $payload = [
                'customer' => $customerId,
                'billingType' => 'BOLETO',
                'value' => (float)$conta->valor_integral,
                'dueDate' => \Carbon\Carbon::parse($conta->data_vencimento)->format('Y-m-d'),
                'description' => $instrucao ?: "Conta receber #{$conta->id}",
                'externalReference' => (string)$conta->id,
            ];

            if ((float)$juros > 0) {
                $payload['interest'] = [
                    'value' => (float)$juros
                ];
            }

            if ((float)$multa > 0) {
                $payload['fine'] = [
                    'value' => (float)$multa
                ];
            }

            $response = AsaasUtil::gerarBoleto($payload, $empresaId);

            return [
                'success' => true,
                'message' => 'Boleto Asaas gerado com sucesso.',
                'data' => $response,
                'payload_envio' => $payload,
                'payload_retorno' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'payload_envio' => $payload,
                'payload_retorno' => [],
            ];
        }
    }

    private function obterOuCriarClienteAsaas($cliente, $empresaId)
    {
        if (!$cliente) {
            throw new \Exception('Cliente não informado.');
        }

        if (!empty($cliente->asaas_id)) {
            try {
                $clienteAsaas = AsaasUtil::consultarCliente($cliente->asaas_id, $empresaId);

                if (!empty($clienteAsaas['id'])) {
                    return $clienteAsaas['id'];
                }
            } catch (\Throwable $e) {
                \Log::warning('Asaas: asaas_id local inválido ou não encontrado', [
                    'cliente_id' => $cliente->id ?? null,
                    'empresa_id' => $empresaId,
                    'asaas_id' => $cliente->asaas_id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

    // 2. Tenta localizar no Asaas por CPF/CNPJ
        $clienteExistente = \App\Utils\AsaasUtil::buscarClientePorCpfCnpj($cliente->cpf_cnpj ?? null, $empresaId);

        if (!empty($clienteExistente['id'])) {
            if (isset($cliente->asaas_id)) {
                $cliente->asaas_id = $clienteExistente['id'];
                $cliente->save();
            }

            return $clienteExistente['id'];
        }

    // 3. Cria no Asaas
        $payload = [
            'name' => $cliente->razao_social ?? $cliente->nome,
            'cpfCnpj' => preg_replace('/\D/', '', (string)($cliente->cpf_cnpj ?? '')),
            'email' => $cliente->email,
            'mobilePhone' => preg_replace('/\D/', '', (string)($cliente->telefone ?? $cliente->celular ?? '')),
            'address' => $cliente->rua ?? null,
            'addressNumber' => $cliente->numero ?? null,
            'complement' => $cliente->complemento ?? null,
            'province' => $cliente->bairro ?? null,
            'postalCode' => preg_replace('/\D/', '', (string)($cliente->cep ?? '')),
            'externalReference' => (string)$cliente->id,
            'notificationDisabled' => false,
        ];

    // limpa nulos/vazios
        $payload = array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($payload['name'])) {
            throw new \Exception('Nome do cliente não informado para cadastro no Asaas.');
        }

        if (empty($payload['cpfCnpj'])) {
            throw new \Exception('CPF/CNPJ do cliente não informado para cadastro no Asaas.');
        }

        $novoCliente = AsaasUtil::criarCliente($payload, $empresaId);

        if (empty($novoCliente['id'])) {
            throw new \Exception('Asaas não retornou o ID do cliente.');
        }

        if (isset($cliente->asaas_id)) {
            $cliente->asaas_id = $novoCliente['id'];
            $cliente->save();
        }

        return $novoCliente['id'];
    }

}
