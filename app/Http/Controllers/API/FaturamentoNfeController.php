<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\Empresa;
use App\Models\Contigencia;
use App\Services\NFeService;
use Illuminate\Support\Facades\DB;

class FaturamentoNfeController extends Controller
{
    public function processarLote(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer'
        ]);

        $resultados = [];
        $sucesso = 0;
        $erros = 0;

        $ids = collect($request->ids)
        ->filter()
        ->sort()
        ->values();

        foreach($ids as $index => $id){

            try{

                $retorno = $this->emitirItem($id);

                if($retorno['erro'] == 0){
                    $sucesso++;
                }else{
                    $erros++;
                }

                $resultados[] = $retorno;

            }catch(\Exception $e){

                $erros++;

                $resultados[] = [
                    'id' => $id,
                    'erro' => 1,
                    'mensagem' => $e->getMessage()
                ];
            }
            usleep(700000);
        }

        return response()->json([
            'success' => true,
            'message' => 'Processamento finalizado',
            'sucesso' => $sucesso,
            'erros' => $erros,
            'total' => count($request->ids),
            'resultados' => $resultados
        ]);
    }

    private function emitirItem($id)
    {
        try{

            $nfe = Nfe::findOrFail($id);

            if($nfe->estado == 'aprovado'){
                return [
                    'id' => $nfe->id,
                    'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                    'erro' => 1,
                    'mensagem' => 'NF-e já transmitida'
                ];
            }

            $empresa = Empresa::findOrFail($nfe->empresa_id);
            $empresa = __objetoParaEmissao($empresa, $nfe->local_id);

            $campoNumero = $nfe->ambiente == 2 
            ? 'numero_ultima_nfe_homologacao' 
            : 'numero_ultima_nfe_producao';

            $ultimoNumero = (int) $empresa->{$campoNumero};

            $proximoNumero = $ultimoNumero + 1;

            $nfe->numero = $proximoNumero;
            $nfe->save();

            if($empresa->arquivo == null){
                return [
                    'id' => $nfe->id,
                    'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                    'erro' => 1,
                    'mensagem' => 'Certificado não encontrado para este emitente'
                ];
            }

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfe->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_010_V1.21",
                "versao" => "4.00",
            ], $empresa);

            $doc = $nfe_service->gerarXml($nfe);

            if(isset($doc['erros_xml'])){
                return [
                    'id' => $nfe->id,
                    'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                    'erro' => 1,
                    'mensagem' => $doc['erros_xml']
                ];
            }

            $xml = $doc['xml'];
            $chave = $doc['chave'];

            $xmlTemp = simplexml_load_string($xml);
            $itensComErro = "";
            $regime = $empresa->tributacao;

            foreach ($xmlTemp->infNFe->det as $item) {
                if (isset($item->imposto->ICMS)) {
                    $icms = (array_values((array)$item->imposto->ICMS));

                    if(sizeof($icms) == 0){
                        $itensComErro .= " Produto " . $item->prod->xProd . " não formando a TAG ICMS, confira se o CST do item corresponde a tributação, regime configurado: $regime";
                    }
                }
            }

            if($itensComErro){
                return [
                    'id' => $nfe->id,
                    'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                    'erro' => 1,
                    'mensagem' => $itensComErro
                ];
            }

            if($this->validarTagsICMS($xml) != ""){
                return [
                    'id' => $nfe->id,
                    'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                    'erro' => 1,
                    'mensagem' => "XML inválido: não foi encontrado o grupo ICMS/ICMSSN correspondente ao CST/CSOSN informado no " . $this->validarTagsICMS($xml)
                ];
            }

            $signed = $nfe_service->sign($xml);
            $resultado = $nfe_service->transmitir($signed, $doc['chave']);

            if ($resultado['erro'] == 0) {

                $nfe->chave = $doc['chave'];
                $nfe->estado = 'aprovado';
                $nfe->estado_fatura = 'aprovado';
                $nfe->data_faturamento = now();

                $empresa->{$campoNumero} = $doc['numero'];

                $nfe->numero = $doc['numero'];
                $nfe->recibo = $resultado['success'];
                $nfe->data_emissao = date('Y-m-d H:i:s');
                $nfe->contigencia = $this->getContigencia($nfe->empresa_id);
                $nfe->save();

                $empresa->save();

                $descricaoLog = "Emitida número $nfe->numero - $nfe->chave APROVADA";
                __createLog($nfe->empresa_id, 'NFe', 'transmitir', $descricaoLog);

                return [
                    'id' => $nfe->id,
                    'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                    'erro' => 0,
                    'mensagem' => 'NF-e transmitida com sucesso',
                    'recibo' => $resultado['success'],
                    'chave' => $nfe->chave
                ];
            }

            $error = $resultado['error'];
            $recibo = $resultado['recibo'] ?? null;

            $motivo = '';

            if(isset($error['protNFe'])){
                $motivo = $error['protNFe']['infProt']['xMotivo'];
                $cStat = $error['protNFe']['infProt']['cStat'];
                $nfe->motivo_rejeicao = substr("[$cStat] $motivo", 0, 200);
                $mensagem = "[$cStat] $motivo";
            }else{
                $nfe->motivo_rejeicao = substr($error, 0, 200);
                $mensagem = $error;
            }

            if($nfe->chave == ''){
                $nfe->chave = $doc['chave'];
            }

            if($nfe->signed_xml == null){
                $nfe->signed_xml = $signed;
            }

            if($nfe->recibo == null){
                $nfe->recibo = $recibo;
            }

            $nfe->estado = 'rejeitado';
            $nfe->save();

            $descricaoLog = "REJEITADA $nfe->chave - $motivo";
            __createLog($nfe->empresa_id, 'NFe', 'erro', $descricaoLog);

            return [
                'id' => $nfe->id,
                'pedido' => $nfe->numero_sequencial ?? $nfe->id,
                'erro' => 1,
                'mensagem' => $mensagem
            ];

        }catch(\Exception $e){

            return [
                'id' => $id,
                'pedido' => $id,
                'erro' => 1,
                'mensagem' => $this->formatarErroNFe($e->getMessage())
            ];
        }
    }

    private function formatarErroNFe($mensagem)
    {
        if (
            preg_match('/Element.*\}CNPJ.*pattern.*not accepted.*\[0-9\]\{14\}/s', $mensagem) ||
            preg_match('/TCnpj/s', $mensagem)
        ) {
            return "O CNPJ informado contém letras ou está em formato inválido para emissão fiscal. No momento, a SEFAZ/NF-e ainda aceita apenas CNPJ com 14 dígitos numéricos no XML. Use um CNPJ numérico válido para transmitir.";
        }

        if (preg_match('/cClassTrib.*pattern.*not accepted/', $mensagem)) {
            return "O código de classificação tributária (cClassTrib) deve conter 6 dígitos numéricos. Verifique o campo e corrija o valor enviado (exemplo: 000002).";
        }

        if (preg_match('/ICMSSN900.*Expected.*vICMS/', $mensagem)) {
            return "No regime Simples Nacional com CSOSN 900 é obrigatório informar o campo vICMS (mesmo que seja 0,00). Ajuste o XML e tente novamente.";
        }

        if (preg_match('/This element is not expected/', $mensagem)) {
            preg_match('/Element.*\{.*\}(.*?)\'.*Expected.*\{.*\}(.*?)\)/', $mensagem, $matches);

            if (isset($matches[1], $matches[2])) {
                return "Campo inválido no XML: <strong>{$matches[1]}</strong>. Esperado: <strong>{$matches[2]}</strong>.";
            }

            return "Erro de estrutura no XML: verifique os campos do ICMS.";
        }

        return "Erro ao validar XML. Detalhes técnicos: " . htmlspecialchars($mensagem);
    }

    function validarTagsICMS($xmlString)
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlString);
        $retorno = "";
        $cont = 1;
        foreach ($xml->infNFe->det as $det) {

            $nItem = (string) $det['nItem'];

            $imposto = $det->imposto;
            $temTag = 0;
            $gruposICMS = [
                'ICMS00','ICMS10','ICMS20','ICMS30','ICMS40','ICMS51',
                'ICMS60', 'ICMS61','ICMS70','ICMS90','ICMSST'
            ];

            foreach ($gruposICMS as $g) {
                if (isset($imposto->ICMS->{$g})) {
                    $temTag = 1;
                }
            }
            if (isset($imposto->ICMS->ICMSST)) {
                $temTag = 1;
            }

            $gruposSN = [
                'ICMSSN101','ICMSSN102','ICMSSN201','ICMSSN202','ICMSSN203',
                'ICMSSN500','ICMSSN900', 'ICMS61'
            ];

            foreach ($gruposSN as $g) {
                if (isset($imposto->ICMS->{$g})) {
                    $temTag = 1;
                }
            }

            if($temTag == 0){
                $retorno .= " item $cont.";
            }
            $cont++;
        }

        return $retorno;
    }

    private function getContigencia($empresa_id){
        $active = Contigencia::
        where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->where('documento', 'NFe')
        ->first();
        return $active != null ? 1 : 0;
    }

}
