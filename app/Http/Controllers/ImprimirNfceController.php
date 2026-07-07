<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nfce;
use NFePHP\DA\NFe\Danfce;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class ImprimirNfceController extends Controller
{
    public function imprimir($chave){
        $item = Nfce::where('chave', $chave)->first();
        if (file_exists(public_path('xml_nfce/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfce/') . $item->chave . '.xml');
            $danfe = new Danfce($xml, $item);
            $pdf = $danfe->render();
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function imprimirTermica($chave)
    {
        $pathXml = public_path('xml_nfce/') . $chave . '.xml';

        if(!file_exists($pathXml)){
            abort(404, 'XML da NFC-e não encontrado');
        }

        $xml = file_get_contents($pathXml);

        if(!$xml){
            abort(500, 'Não foi possível ler o XML da NFC-e');
        }

        $doc = new \SimpleXMLElement($xml);
        $ns = $doc->getNamespaces(true);

        $nfe = $doc->children($ns[''])->NFe;
        $infNFe = $nfe->infNFe;

        $emit = $infNFe->emit;
        $ide = $infNFe->ide;
        $total = $infNFe->total->ICMSTot;
        $pag = $infNFe->pag;
        $prot = $doc->protNFe->infProt;

        $formasPagamento = [
            '01' => 'Dinheiro',
            '02' => 'Cheque',
            '03' => 'Cartão de Crédito',
            '04' => 'Cartão de Débito',
            '05' => 'Crédito Loja',
            '10' => 'Vale Alimentação',
            '11' => 'Vale Refeição',
            '12' => 'Vale Presente',
            '13' => 'Vale Combustível',
            '15' => 'Boleto Bancário',
            '16' => 'Depósito Bancário',
            '17' => 'PIX',
            '18' => 'Transferência Bancária',
            '19' => 'Programa Fidelidade',
            '90' => 'Sem Pagamento',
            '99' => 'Outros',
        ];

        $itens = [];

        foreach($infNFe->det as $det){
            $itens[] = [
                'codigo' => (string)$det->prod->cProd,
                'descricao' => (string)$det->prod->xProd,
                'quantidade' => (float)$det->prod->qCom,
                'unidade' => (string)$det->prod->uCom,
                'valor_unitario' => (float)$det->prod->vUnCom,
                'valor_total' => (float)$det->prod->vProd,
            ];
        }

        $pagamentos = [];

        if(isset($pag->detPag)){
            foreach($pag->detPag as $detPag){
                $tipo = (string)$detPag->tPag;

                $pagamentos[] = [
                    'tipo' => $tipo,
                    'descricao' => $formasPagamento[$tipo] ?? 'Outros',
                    'valor' => (float)$detPag->vPag,
                ];
            }
        }

        $dest = $infNFe->dest ?? null;

        $qrcodeTexto = (string)($nfe->infNFeSupl->qrCode ?? '');

        $qrcodeBase64 = '';

        if($qrcodeTexto){
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 5,
                'imageBase64' => true,
            ]);

            $qrcodeBase64 = (new QRCode($options))->render($qrcodeTexto);
        }

        $dados = [
            'empresa' => [
                'nome' => (string)$emit->xNome,
                'fantasia' => (string)($emit->xFant ?? ''),
                'cnpj' => (string)$emit->CNPJ,
                'ie' => (string)($emit->IE ?? ''),
                'logradouro' => (string)($emit->enderEmit->xLgr ?? ''),
                'numero' => (string)($emit->enderEmit->nro ?? ''),
                'complemento' => (string)($emit->enderEmit->xCpl ?? ''),
                'bairro' => (string)($emit->enderEmit->xBairro ?? ''),
                'cidade' => (string)($emit->enderEmit->xMun ?? ''),
                'uf' => (string)($emit->enderEmit->UF ?? ''),
                'cep' => (string)($emit->enderEmit->CEP ?? ''),
                'telefone' => (string)($emit->enderEmit->fone ?? ''),
            ],

            'cliente' => [
                'nome' => $dest ? (string)($dest->xNome ?? 'CONSUMIDOR NÃO IDENTIFICADO') : 'CONSUMIDOR NÃO IDENTIFICADO',
                'documento' => $dest ? (string)($dest->CPF ?? $dest->CNPJ ?? '') : '',
            ],

            'numero' => (string)$ide->nNF,
            'serie' => (string)$ide->serie,
            'data_emissao' => (string)$ide->dhEmi,
            'ambiente' => (int)$ide->tpAmb,
            'chave' => $chave,

            'valor_produtos' => (float)($total->vProd ?? 0),
            'valor_desconto' => (float)($total->vDesc ?? 0),
            'valor_frete' => (float)($total->vFrete ?? 0),
            'valor_outros' => (float)($total->vOutro ?? 0),
            'valor_total' => (float)($total->vNF ?? 0),
            'valor_tributos' => (float)($total->vTotTrib ?? 0),

            'itens' => $itens,
            'pagamentos' => $pagamentos,
            'troco' => (float)($pag->vTroco ?? 0),

            'url_consulta' => (string)($nfe->infNFeSupl->urlChave ?? ''),

            'protocolo' => (string)($prot->nProt ?? ''),
            'autorizacao' => (string)($prot->dhRecbto ?? ''),
            'motivo' => (string)($prot->xMotivo ?? ''),
            'inf_cpl' => (string)($infNFe->infAdic->infCpl ?? ''),
            'qrcode' => $qrcodeTexto,
            'qrcode_base64' => $qrcodeBase64,
        ];

        // dd($dados['qrcode_base64']);
        return view('nfce.impressao_termica', compact('dados'));
    }
}
