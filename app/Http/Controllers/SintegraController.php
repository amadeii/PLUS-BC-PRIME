<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Utils\SintegraUtil;

class SintegraController extends Controller
{
    protected $util = null;

    public function __construct(SintegraUtil $util)
    {
        $this->util = $util;
    }

    private function soNumero($str)
    {
        return preg_replace("/[^0-9]/", "", (string)$str);
    }

    private function removeAcentos($texto)
    {
        $aFind = [
            '&', 'á', 'à', 'ã', 'â', 'é', 'ê',
            'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç',
            'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í',
            'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç'
        ];

        $aSubs = [
            'e', 'a', 'a', 'a', 'a', 'e', 'e',
            'i', 'o', 'o', 'o', 'u', 'u', 'c',
            'A', 'A', 'A', 'A', 'E', 'E', 'I',
            'O', 'O', 'O', 'U', 'U', 'C'
        ];

        $novoTexto = str_replace($aFind, $aSubs, (string)$texto);
        $novoTexto = preg_replace("/[^a-zA-Z0-9 @,\-.;:\/_]/", "", $novoTexto);

        return $novoTexto;
    }

    public function index()
    {
        return view('sintegra.index');
    }

    public function store(Request $request)
    {
        $dataInicial = $request->start_date;
        $dataFinal = $request->end_date;
        $local_id = $request->local_id;

        $emitente = Empresa::findOrFail($request->empresa_id);

        if ($local_id) {
            $emitente = __objetoParaEmissao($emitente, $local_id);
        }

        $cnpj = $this->soNumero($emitente->cpf_cnpj);

        $documentos = Nfe::where('empresa_id', $request->empresa_id)
        ->where(function ($q) use ($dataInicial, $dataFinal) {

        // NÃO importados → usa data_emissao
            $q->where(function ($t) use ($dataInicial, $dataFinal) {
                $t->where(function($x){
                    $x->whereNull('chave_importada')
                    ->orWhere('chave_importada', 0);
                })
                ->whereDate('data_emissao', '>=', $dataInicial)
                ->whereDate('data_emissao', '<=', $dataFinal)
                ->whereIn('estado', ['aprovado', 'cancelado']);
            });

        // IMPORTADOS → usa created_at
            $q->orWhere(function ($t) use ($dataInicial, $dataFinal) {
                $t->whereNotNull('chave_importada')
                ->where('chave_importada', '!=', 0)
                ->whereDate('created_at', '>=', $dataInicial)
                ->whereDate('created_at', '<=', $dataFinal);
            });

        })
        ->when($local_id, function ($query) use ($local_id) {
            return $query->where('local_id', $local_id);
        })
        ->get();

        $vendasPdv = Nfce::whereDate('data_emissao', '>=', $dataInicial)
        ->whereDate('data_emissao', '<=', $dataFinal)
        ->whereIn('estado', ['aprovado', 'cancelado'])
        ->where('empresa_id', $request->empresa_id)
        ->when($local_id, function ($query) use ($local_id) {
            return $query->where('local_id', $local_id);
        })
        ->get();

        $dataXml = [];

        $dInicio = \Carbon\Carbon::parse($dataInicial)->format('Ymd');
        $dFinal = \Carbon\Carbon::parse($dataFinal)->format('Ymd');

        /*
        |--------------------------------------------------------------------------
        | REGISTRO 10
        |--------------------------------------------------------------------------
        */
        $registro10 = '10';
        $registro10 .= str_pad($cnpj, 14, '0', STR_PAD_LEFT);
        $registro10 .= str_pad((string)$emitente->ie, 14, ' ');
        $registro10 .= str_pad(substr($this->removeAcentos($emitente->nome), 0, 35), 35, ' ');
        $registro10 .= str_pad($this->removeAcentos($emitente->cidade->nome), 30, ' ');
        $registro10 .= str_pad($emitente->cidade->uf, 2, ' ');
        $registro10 .= str_pad(substr($this->soNumero($emitente->celular), 0, 10), 10, ' ');
        $registro10 .= $dInicio;
        $registro10 .= $dFinal;
        $registro10 .= '3';
        $registro10 .= '3';
        $registro10 .= '1';
        $registro10 .= "\r\n";

        $sintegra = strtoupper($registro10);

        /*
        |--------------------------------------------------------------------------
        | REGISTRO 11
        |--------------------------------------------------------------------------
        */
        $registro11 = '11';
        $registro11 .= str_pad(substr($this->removeAcentos($emitente->rua), 0, 34), 34, ' ');

        $numero = $this->soNumero($emitente->numero);

        if ($numero == '' || $numero == '0') {
            $numero = '0000';
        }

        $registro11 .= str_pad($numero, 5, '0', STR_PAD_LEFT);
        $registro11 .= str_pad(substr($this->removeAcentos($emitente->complemento), 0, 22), 22, ' ');
        $registro11 .= str_pad(substr($this->removeAcentos($emitente->bairro), 0, 15), 15, ' ');
        $registro11 .= str_pad($this->soNumero($emitente->cep), 8, '0', STR_PAD_LEFT);
        $registro11 .= str_pad(substr($this->removeAcentos($emitente->nome), 0, 28), 28, ' ');
        $registro11 .= str_pad(substr($this->soNumero($emitente->celular), 0, 12), 12, '0', STR_PAD_LEFT);
        $registro11 .= "\r\n";

        $sintegra .= strtoupper($registro11);

        /*
        |--------------------------------------------------------------------------
        | CONTADORES
        |--------------------------------------------------------------------------
        */
        $totalregistro50 = 0;
        $totalregistro51 = 0;
        $totalregistro54 = 0;
        $totalregistro61 = 0;
        $totalregistro70 = 0;
        $totalregistro74 = 0;
        $totalregistro75 = 0;

        $registro50 = '';
        $registro54 = '';
        $registro61R = '';
        $registro75 = '';

        $produtos = [];
        $registros50Agrupados = [];

        /*
        |--------------------------------------------------------------------------
        | CARREGA XMLS NF-e
        |--------------------------------------------------------------------------
        */
        foreach ($documentos as $doc) {
            $xml = $this->util->getXml($doc, 'xml_nfe/');
            if ($xml == null && !empty($doc->chave_importada)) {

                $arquivo = public_path('xml_entrada/') . $doc->chave_importada . '.xml';

                if (file_exists($arquivo)) {
                    try {
                        $xml = simplexml_load_file($arquivo);
                    } catch (\Exception $e) {
                        $xml = null;
                    }
                }
            }
            if ($xml != null) {
                $dataXml[] = [
                    'tipo' => ((int)$doc->tpNF === 0 ? 'entrada' : 'saida'),
                    'xml' => $xml,
                    'objeto' => $doc
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CARREGA XMLS NFC-e
        |--------------------------------------------------------------------------
        */
        foreach ($vendasPdv as $v) {
            $xml = $this->util->getXml($v, 'xml_nfce/');

            if ($xml != null) {
                $dataXml[] = [
                    'tipo' => 'saida',
                    'xml' => $xml,
                    'objeto' => $v
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PROCESSAMENTO DOS XMLS
        |--------------------------------------------------------------------------
        */
        foreach ($dataXml as $l) {

            $xml = $l['xml'];
            $obj = $l['objeto'];

            $destinatario = $this->util->getDestinatario($xml);
            $emitenteXml = $this->util->getEmitente($xml);
            $ide = $this->util->getIde($xml);
            $itens = $this->util->getItensNfe($xml);
            $total = $this->util->getTotal($xml);

            if (!$ide || !$itens || count($itens) == 0) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | PARTICIPANTE
            | Entrada: usa emitente do XML, fornecedor.
            | Saída: usa destinatário do XML, cliente.
            |--------------------------------------------------------------------------
            */
            $isEntrada = $l['tipo'] === 'entrada';

// Entrada: fornecedor é o emitente do XML
// Saída: cliente é o destinatário do XML
            $enderecoParticipante = $participante->enderDest ?? $participante->enderEmit ?? null;
            $ufDestinatario = (string)($enderecoParticipante->UF ?? '  ');

            $enderecoParticipante = $participante->enderDest ?? $participante->enderEmit ?? null;

            $docDestinatario = isset($participante->CNPJ)
            ? (string)$participante->CNPJ
            : (string)($participante->CPF ?? '');

            $ufDestinatario = (string)($enderecoParticipante->UF ?? '  ');

            $ieDestinatario = (!isset($participante->IE) || (string)$participante->IE == '')
            ? 'ISENTO'
            : $this->soNumero($participante->IE);

            $vFreteTotal = (float)($total->vFrete ?? 0);
            $vOutroTotal = (float)($total->vOutro ?? 0);

            /*
            |--------------------------------------------------------------------------
            | REGISTRO 50 AGRUPADO
            |--------------------------------------------------------------------------
            */
            foreach ($itens as $item) {

                $prod = $item->prod ?? null;
                $imposto = $item->imposto ?? null;

                if (!$prod || !$imposto) {
                    continue;
                }

                $arr = array_values((array)($imposto->ICMS ?? []));
                $icms = $arr[0] ?? new \stdClass();

                $pICMS = $icms->pICMS ?? 0;
                $vBCICMS = isset($icms->vBC) ? (float)$icms->vBC : 0;
                $vICMS = isset($icms->vICMS) ? (float)$icms->vICMS : 0;
                $vICMSST = isset($icms->vICMSST) ? (float)$icms->vICMSST : 0;
                $vFCPST = isset($icms->vFCPST) ? (float)$icms->vFCPST : 0;

                if ((string)$ide->mod != '65' && (string)$ide->mod != '57') {

                    $cfop = (string)$prod->CFOP;

                    $chave50 = implode('|', [
                        $this->soNumero($docDestinatario),
                        (string)$ide->mod,
                        (string)$ide->serie,
                        (string)$ide->nNF,
                        $cfop
                    ]);

                    $vProd = (float)($prod->vProd ?? 0);
                    $vFrete = (float)($prod->vFrete ?? 0);
                    $vOutro = (float)($prod->vOutro ?? 0);
                    $vDesc = (float)($prod->vDesc ?? 0);

                    $arrIpi = array_values((array)($imposto->IPI ?? []));
                    $vIPI = 0;

                    foreach ($arrIpi as $ipiItem) {
                        if (isset($ipiItem->vIPI)) {
                            $vIPI = (float)$ipiItem->vIPI;
                            break;
                        }
                    }

                    $valorTotalItem50 = $vProd + $vIPI + $vICMSST + $vFCPST + $vFrete + $vOutro - $vDesc;

                    if (!isset($registros50Agrupados[$chave50])) {
                        $registros50Agrupados[$chave50] = [
                            'tipo' => $l['tipo'],
                            'docDestinatario' => $docDestinatario,
                            'ieDestinatario' => $ieDestinatario,
                            'ufDestinatario' => $ufDestinatario,
                            'data' => $isEntrada
                            ? \Carbon\Carbon::parse($obj->created_at)->format('Ymd')
                            : \Carbon\Carbon::parse($ide->dhEmi)->format('Ymd'),
                            'mod' => (string)$ide->mod,
                            'serie' => (string)$ide->serie,
                            'nNF' => (string)$ide->nNF,
                            'cfop' => $cfop,
                            'pICMS' => $pICMS,
                            'tpEmis' => (string)($ide->tpEmis ?? 1),
                            'situacao' => (($obj->estado ?? '') == 'cancelado') ? 'S' : 'N',
                            'valor_total' => 0,
                            'base_icms' => 0,
                            'valor_icms' => 0,
                            'isentas_outras' => 0,
                            'outras' => 0,
                        ];
                    }

                    $registros50Agrupados[$chave50]['valor_total'] += $valorTotalItem50;
                    $registros50Agrupados[$chave50]['base_icms'] += $vBCICMS;
                    $registros50Agrupados[$chave50]['valor_icms'] += $vICMS;
                    // $registros50Agrupados[$chave50]['outras'] += $vOutro;

                    $cst = isset($icms->CST) ? (string)$icms->CST : '';
                    // $csosn = isset($icms->CSOSN) ? (string)$icms->CSOSN : '';

                    $vProdLiquido = $vProd - $vDesc;

                    if (in_array($cst, ['40', '41', '50'], true)) {
                        $registros50Agrupados[$chave50]['isentas_outras'] += $vProdLiquido;
                    } else {
                        if ($vBCICMS <= 0) {
                            $registros50Agrupados[$chave50]['outras'] += $vProdLiquido;
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | REGISTRO 54, 61R E 75
            |--------------------------------------------------------------------------
            */
            $contItem = 1;

            foreach ($itens as $item) {

                $prod = $item->prod ?? null;
                $imposto = $item->imposto ?? null;

                if (!$prod || !$imposto) {
                    continue;
                }

                $cfop = (string)$prod->CFOP;

                $arr = array_values((array)($imposto->ICMS ?? []));
                $icms = $arr[0] ?? new \stdClass();

                $cst_csosn = isset($icms->CST) ? $icms->CST : ($icms->CSOSN ?? '');
                $cst_csosn = (string)$cst_csosn;

                $vBCICMS = isset($icms->vBC) ? (float)$icms->vBC : 0;
                $vBCST = isset($icms->vBCST) ? (float)$icms->vBCST : 0;
                $pMVAST = isset($icms->pMVAST) ? (float)$icms->pMVAST : 0;
                $pICMS = $icms->pICMS ?? 0;

                $arrIpi = array_values((array)($imposto->IPI ?? []));
                $vIPI = 0;
                $pIPI = 0;

                foreach ($arrIpi as $ipiItem) {
                    if (isset($ipiItem->pIPI)) {
                        $pIPI = $ipiItem->pIPI;
                    }

                    if (isset($ipiItem->vIPI)) {
                        $vIPI = (float)$ipiItem->vIPI;
                    }
                }

                if ((string)$ide->mod != '65' && (string)$ide->mod != '57') {

                    $totalregistro54++;

                    $registro54 .= '54';
                    $registro54 .= str_pad($this->soNumero($docDestinatario), 14, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero($ide->mod), 2, '0');
                    $registro54 .= str_pad($this->soNumero($ide->serie), 3, ' ');
                    $registro54 .= str_pad(substr($this->soNumero($ide->nNF), -6), 6, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad(substr($this->soNumero($cfop), 0, 4), 4, '0', STR_PAD_LEFT);

                    $cstFormatado = str_pad(substr($this->soNumero($cst_csosn), 0, 3), 3, '0', STR_PAD_LEFT);

                    $registro54 .= $cstFormatado;
                    $registro54 .= str_pad(substr($this->soNumero($contItem), 0, 3), 3, '0', STR_PAD_LEFT);

                    $vDesc = isset($prod->vDesc) ? (float)$prod->vDesc : 0;

                    $registro54 .= str_pad(substr((string)$prod->cProd, 0, 14), 14, ' ');
                    $qtd = number_format((float)$prod->qCom, 3, '.', '');
                    $registro54 .= str_pad($this->soNumero($qtd), 11, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero(number_format(((float)$prod->vProd - $vDesc), 2, '.', '')), 12, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero(number_format($vDesc, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero(number_format($vBCICMS, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero(number_format($vBCST, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero(number_format($vIPI, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                    $registro54 .= str_pad($this->soNumero($pICMS), 4, '0', STR_PAD_LEFT);
                    $registro54 .= "\r\n";

                    $contItem++;

                } else {

                    $totalregistro61++;

                    $registro61R .= '61R';
                    $registro61R .= str_pad(\Carbon\Carbon::parse($ide->dhEmi)->format('mY'), 6, '0', STR_PAD_LEFT);
                    $registro61R .= str_pad(substr((string)$prod->cProd, 0, 14), 14, ' ');
                    $registro61R .= str_pad($this->soNumero($prod->qCom), 13, '0', STR_PAD_LEFT);
                    $registro61R .= str_pad($this->soNumero(number_format((float)$prod->vProd, 2, '.', '')), 16, '0', STR_PAD_LEFT);
                    $registro61R .= str_pad($this->soNumero(number_format($vBCICMS, 2, '.', '')), 16, '0', STR_PAD_LEFT);
                    $registro61R .= str_pad($this->soNumero($pICMS), 4, '0', STR_PAD_LEFT);
                    $registro61R .= str_pad(' ', 54, ' ');
                    $registro61R .= "\r\n";

                    $contItem++;
                }

                /*
                |--------------------------------------------------------------------------
                | REGISTRO 75
                |--------------------------------------------------------------------------
                */
                $codigoProduto75 = substr((string)$prod->cProd, 0, 14);

                if (!in_array($codigoProduto75, $produtos, true)) {

                    $produtos[] = $codigoProduto75;
                    $totalregistro75++;

                    $registro75 .= '75';
                    $registro75 .= $dInicio;
                    $registro75 .= $dFinal;
                    $registro75 .= str_pad($codigoProduto75, 14, ' ');
                    $registro75 .= str_pad(substr((string)($prod->NCM ?? ''), 0, 8), 8, ' ');

                    if (($prod->xProd ?? '') == '') {
                        $descricao = function_exists('BuscaDesc') ? BuscaDesc($prod->cProd) : '';
                        $registro75 .= str_pad(substr($this->removeAcentos(trim($descricao)), 0, 53), 53, ' ');
                    } else {
                        $registro75 .= str_pad(substr($this->removeAcentos(trim($prod->xProd)), 0, 53), 53, ' ');
                    }

                    $registro75 .= str_pad(substr((string)($prod->uCom ?? ''), 0, 6), 6, ' ');
                    $registro75 .= str_pad($this->soNumero($pIPI), 5, '0', STR_PAD_LEFT);
                    $registro75 .= str_pad($this->soNumero($pICMS), 4, '0', STR_PAD_LEFT);
                    $registro75 .= str_pad($this->soNumero($pMVAST), 5, '0', STR_PAD_LEFT);
                    $registro75 .= str_pad('0', 13, '0', STR_PAD_LEFT);
                    $registro75 .= "\r\n";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | REGISTRO 54 - FRETE
            |--------------------------------------------------------------------------
            */
            if ($vFreteTotal > 0 && (string)$ide->mod != '65' && (string)$ide->mod != '57') {

                $totalregistro54++;

                $cfopExtra = isset($itens[0]->prod->CFOP) ? (string)$itens[0]->prod->CFOP : '';

                $registro54 .= '54';
                $registro54 .= str_pad($this->soNumero($docDestinatario), 14, '0', STR_PAD_LEFT);
                $registro54 .= str_pad($this->soNumero($ide->mod), 2, '0');
                $registro54 .= str_pad($this->soNumero($ide->serie), 3, ' ');
                $registro54 .= str_pad(substr($this->soNumero($ide->nNF), -6), 6, '0', STR_PAD_LEFT);
                $registro54 .= str_pad(substr($this->soNumero($cfopExtra), 0, 4), 4, '0', STR_PAD_LEFT);
                $registro54 .= '   ';
                $registro54 .= '991';
                $registro54 .= str_pad('', 14, ' ');
                $registro54 .= str_pad('', 11, '0', STR_PAD_LEFT);
                $registro54 .= str_pad($this->soNumero(number_format($vFreteTotal, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad($this->soNumero(number_format($vFreteTotal, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 4, '0', STR_PAD_LEFT);
                $registro54 .= "\r\n";
            }

            /*
            |--------------------------------------------------------------------------
            | REGISTRO 54 - OUTRAS DESPESAS
            |--------------------------------------------------------------------------
            */
            if ($vOutroTotal > 0 && (string)$ide->mod != '65' && (string)$ide->mod != '57') {

                $totalregistro54++;

                $cfopExtra = isset($itens[0]->prod->CFOP) ? (string)$itens[0]->prod->CFOP : '';

                $registro54 .= '54';
                $registro54 .= str_pad($this->soNumero($docDestinatario), 14, '0', STR_PAD_LEFT);
                $registro54 .= str_pad($this->soNumero($ide->mod), 2, '0');
                $registro54 .= str_pad($this->soNumero($ide->serie), 3, ' ');
                $registro54 .= str_pad(substr($this->soNumero($ide->nNF), -6), 6, '0', STR_PAD_LEFT);
                $registro54 .= str_pad(substr($this->soNumero($cfopExtra), 0, 4), 4, '0', STR_PAD_LEFT);
                $registro54 .= '   ';
                $registro54 .= '999';
                $registro54 .= str_pad('', 14, ' ');
                $registro54 .= str_pad('', 11, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad($this->soNumero(number_format($vOutroTotal, 2, '.', '')), 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 12, '0', STR_PAD_LEFT);
                $registro54 .= str_pad('', 4, '0', STR_PAD_LEFT);
                $registro54 .= "\r\n";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MONTA REGISTRO 50 APÓS AGRUPAMENTO
        |--------------------------------------------------------------------------
        */
        foreach ($registros50Agrupados as $r) {

            $totalregistro50++;

            $registro50 .= '50';
            $registro50 .= str_pad($this->soNumero($r['docDestinatario']), 14, '0', STR_PAD_LEFT);

            if ($r['ieDestinatario'] === 'ISENTO') {
                $registro50 .= str_pad('ISENTO', 14, ' ');
            } else {
                if (strlen($this->soNumero($r['docDestinatario'])) < 14) {
                    $registro50 .= str_pad('ISENTO', 14, ' ');
                } else {
                    $registro50 .= str_pad($r['ieDestinatario'], 14, ' ');
                }
            }

            $registro50 .= $r['data'];
            $registro50 .= str_pad($r['ufDestinatario'], 2, ' ');
            $registro50 .= str_pad($this->soNumero($r['mod']), 2, '0');
            $registro50 .= str_pad($this->soNumero($r['serie']), 3, ' ');
            $registro50 .= str_pad(substr($this->soNumero($r['nNF']), -6), 6, '0', STR_PAD_LEFT);
            $registro50 .= str_pad(substr($this->soNumero($r['cfop']), 0, 4), 4, '0', STR_PAD_LEFT);
            $registro50 .= ($r['tipo'] === 'entrada') ? 'T' : 'P';

            $registro50 .= str_pad($this->soNumero(number_format($r['valor_total'], 2, '.', '')), 13, '0', STR_PAD_LEFT);
            $registro50 .= str_pad($this->soNumero(number_format($r['base_icms'], 2, '.', '')), 13, '0', STR_PAD_LEFT);
            $registro50 .= str_pad($this->soNumero(number_format($r['valor_icms'], 2, '.', '')), 13, '0', STR_PAD_LEFT);
            $registro50 .= str_pad($this->soNumero(number_format($r['isentas_outras'], 2, '.', '')), 13, '0', STR_PAD_LEFT);
            $registro50 .= str_pad($this->soNumero(number_format($r['outras'], 2, '.', '')), 13, '0', STR_PAD_LEFT);
            $registro50 .= str_pad(substr($this->soNumero($r['pICMS']), 0, 4), 4, '0', STR_PAD_LEFT);
            $registro50 .= $r['situacao'];
            $registro50 .= "\r\n";
        }

        $sintegra .= strtoupper($registro50);
        $sintegra .= strtoupper($registro54);

        /*
        |--------------------------------------------------------------------------
        | REGISTRO 61 - NFC-e
        |--------------------------------------------------------------------------
        */
        foreach ($dataXml as $l) {

            $ide = $this->util->getIde($l['xml']);
            $total = $this->util->getTotal($l['xml']);

            if (!$ide || !$total) {
                continue;
            }

            if ((string)$ide->mod == '65') {

                $totalregistro61++;

                $registro61 = '61';
                $registro61 .= str_pad(' ', 14, ' ');
                $registro61 .= str_pad(' ', 14, ' ');
                $registro61 .= \Carbon\Carbon::parse($ide->dhEmi)->format('Ymd');
                $registro61 .= str_pad($this->soNumero($ide->mod), 2, '0');
                $registro61 .= str_pad($this->soNumero($ide->serie), 3, ' ');
                $registro61 .= str_pad('', 2, ' ');
                $registro61 .= str_pad(substr($this->soNumero($ide->nNF), -6), 6, '0', STR_PAD_LEFT);
                $registro61 .= str_pad(substr($this->soNumero($ide->nNF), -6), 6, '0', STR_PAD_LEFT);
                $registro61 .= str_pad($this->soNumero(number_format((float)($total->vNF ?? 0), 2, '.', '')), 13, '0', STR_PAD_LEFT);
                $registro61 .= str_pad($this->soNumero(number_format((float)($total->vBC ?? 0), 2, '.', '')), 13, '0', STR_PAD_LEFT);
                $registro61 .= str_pad($this->soNumero(number_format((float)($total->vICMS ?? 0), 2, '.', '')), 12, '0', STR_PAD_LEFT);
                $registro61 .= str_pad('0', 13, '0', STR_PAD_LEFT);
                $registro61 .= str_pad($this->soNumero(number_format((float)($total->vOutro ?? 0), 2, '.', '')), 13, '0', STR_PAD_LEFT);
                $registro61 .= str_pad('0', 4, '0', STR_PAD_LEFT);
                $registro61 .= ' ';
                $registro61 .= "\r\n";

                $sintegra .= strtoupper($registro61);
            }
        }

        $sintegra .= strtoupper($registro61R);

        /*
        |--------------------------------------------------------------------------
        | REGISTRO 70 - CT-e
        |--------------------------------------------------------------------------
        */
        foreach ($dataXml as $l) {

            $ide = $this->util->getIde($l['xml']);
            $total = $this->util->getTotal($l['xml']);

            if (!$ide || !$total) {
                continue;
            }

            if ((string)$ide->mod == '57') {

                $destinatario = $this->util->getDestinatario($l['xml']);
                $docDestinatario = isset($destinatario->CNPJ)
                ? (string)$destinatario->CNPJ
                : (string)($destinatario->CPF ?? '');

                $itens = $this->util->getItensNfe($l['xml']);

                if (!$itens || !isset($itens[0])) {
                    continue;
                }

                $cfop = $itens[0]->prod->CFOP ?? '';

                $totalregistro70++;

                $registro70 = '70';
                $registro70 .= str_pad($this->soNumero($docDestinatario), 14, '0', STR_PAD_LEFT);
                $registro70 .= str_pad($this->soNumero($destinatario->IE ?? ''), 14, ' ');
                $registro70 .= \Carbon\Carbon::parse($ide->dhEmi)->format('Ymd');
                $registro70 .= str_pad($destinatario->enderDest->UF ?? '  ', 2, ' ');
                $registro70 .= str_pad($this->soNumero($ide->mod), 2, '0');
                $registro70 .= str_pad($this->soNumero($ide->serie), 1, ' ');
                $registro70 .= str_pad('', 2, ' ');
                $registro70 .= str_pad(substr($this->soNumero($ide->nNF), -6), 6, '0', STR_PAD_LEFT);
                $registro70 .= str_pad(substr($this->soNumero($cfop), 0, 4), 4, '0', STR_PAD_LEFT);
                $registro70 .= str_pad($this->soNumero(number_format((float)($total->vNF ?? 0), 2, '.', '')), 13, '0', STR_PAD_LEFT);
                $registro70 .= str_pad($this->soNumero(number_format((float)($total->vBC ?? 0), 2, '.', '')), 14, '0', STR_PAD_LEFT);
                $registro70 .= str_pad($this->soNumero(number_format((float)($total->vICMS ?? 0), 2, '.', '')), 14, '0', STR_PAD_LEFT);
                $registro70 .= str_pad('0', 14, '0', STR_PAD_LEFT);
                $registro70 .= str_pad($this->soNumero(number_format((float)($total->vOutro ?? 0), 2, '.', '')), 14, '0', STR_PAD_LEFT);
                $registro70 .= '1';

                $arr = array_values((array)($itens[0]->imposto->ICMS ?? []));
                $icms = $arr[0] ?? new \stdClass();

                $cst_csosn = isset($icms->CST) ? $icms->CST : ($icms->CSOSN ?? '');
                $cst_csosn = (string)$cst_csosn;

                $registro70 .= ($cst_csosn == '101') ? 'S' : 'N';
                $registro70 .= "\r\n";

                $sintegra .= strtoupper($registro70);
            }
        }

        $sintegra .= strtoupper($registro75);

        /*
        |--------------------------------------------------------------------------
        | REGISTRO 90
        |--------------------------------------------------------------------------
        */
        $totalgeral = $totalregistro50
        + $totalregistro51
        + $totalregistro54
        + $totalregistro61
        + $totalregistro70
        + $totalregistro74
        + $totalregistro75
        + 3;

        $registro90 = '90';
        $registro90 .= str_pad($this->soNumero($cnpj), 14, '0', STR_PAD_LEFT);
        $registro90 .= str_pad((string)$emitente->ie, 14, ' ');

        $tipos90 = [
            '50' => $totalregistro50,
            '51' => $totalregistro51,
            '54' => $totalregistro54,
            '61' => $totalregistro61,
            '70' => $totalregistro70,
            '74' => $totalregistro74,
            '75' => $totalregistro75,
        ];

        foreach ($tipos90 as $tipo => $total) {
            if ($total > 0) {
                $registro90 .= $tipo;
                $registro90 .= str_pad($total, 8, '0', STR_PAD_LEFT);
            }
        }

        $totalgeral = $totalregistro50
        + $totalregistro51
        + $totalregistro54
        + $totalregistro61
        + $totalregistro70
        + $totalregistro74
        + $totalregistro75
        + 3; // registros 10, 11 e 90

        $registro90 .= '99';
        $registro90 .= str_pad($totalgeral, 8, '0', STR_PAD_LEFT);

        $total90 = 125 - strlen($registro90);
        $registro90 .= str_pad(' ', $total90, ' ');
        $registro90 .= '1';

        $sintegra .= strtoupper($registro90);

        /*
        |--------------------------------------------------------------------------
        | GERA ARQUIVO
        |--------------------------------------------------------------------------
        */
        $mes = \Carbon\Carbon::parse($dataInicial)->format('m');
        $nomearquivo = "sintegra-" . $cnpj . "-" . $this->getMes($mes - 1) . ".txt";

        if (!is_dir(public_path("sintegra_files/"))) {
            mkdir(public_path("sintegra_files/"), 0775, true);
        }

        $arquivo = fopen(public_path("sintegra_files/") . $nomearquivo, "w");
        fwrite($arquivo, $sintegra);
        fclose($arquivo);

        return response()->download(public_path("sintegra_files/") . $nomearquivo);
    }

    private function getMes($indice)
    {
        $meses = [
            'janeiro',
            'fevereiro',
            'março',
            'abril',
            'maio',
            'junho',
            'julho',
            'agosto',
            'setembro',
            'outubro',
            'novembro',
            'dezembro'
        ];

        return $meses[$indice];
    }
}