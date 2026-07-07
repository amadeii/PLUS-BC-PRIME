<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfce;
use App\Models\Empresa;
use App\Models\Contigencia;
use App\Models\UsuarioEmissao;
use App\Services\NFCeService;

class FaturamentoNfceController extends Controller
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

        $ids = collect($request->ids)->filter()->sort()->values();

        foreach($ids as $id){

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
                    'pedido' => $id,
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

            $nfce = Nfce::findOrFail($id);

            if($nfce->estado == 'aprovado'){
                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                    'erro' => 1,
                    'mensagem' => 'NFC-e já transmitida'
                ];
            }

            $empresa = Empresa::findOrFail($nfce->empresa_id);
            $empresa = __objetoParaEmissao($empresa, $nfce->local_id);

            if($empresa->arquivo == null){
                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                    'erro' => 1,
                    'mensagem' => 'Certificado não encontrado para este emitente'
                ];
            }

            $configUsuarioEmissao = UsuarioEmissao::where('usuario_empresas.empresa_id', $nfce->empresa_id)
            ->join('usuario_empresas', 'usuario_empresas.usuario_id', '=', 'usuario_emissaos.usuario_id')
            ->select('usuario_emissaos.*')
            ->where('usuario_emissaos.usuario_id', $nfce->user_id)
            ->first();

            $proximoNumero = $this->getProximoNumeroNfce($empresa, $configUsuarioEmissao, $nfce);

            $nfce->numero = $proximoNumero;
            $nfce->save();

            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfce->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_010_V1.21",
                "versao" => "4.00",
                "CSC" => $empresa->csc,
                "CSCid" => $empresa->csc_id
            ], $empresa);

            $doc = $nfe_service->gerarXml($nfce);

            if(isset($doc['erros_xml'])){
                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
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
                    $icms = array_values((array)$item->imposto->ICMS);

                    if(sizeof($icms) == 0){
                        $itensComErro .= " Produto " . $item->prod->xProd . " não formando a TAG ICMS, confira se o CST do item corresponde a tributação, regime configurado: $regime";
                    }
                }
            }

            if($itensComErro){
                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                    'erro' => 1,
                    'mensagem' => $itensComErro
                ];
            }

            $signed = $nfe_service->sign($xml);

            if($this->getContigencia($nfce->empresa_id)){

                if(!is_dir(public_path('xml_nfce_contigencia'))){
                    mkdir(public_path('xml_nfce_contigencia'), 0777, true);
                }

                $nfce->contigencia = 1;
                $nfce->reenvio_contigencia = 0;
                $nfce->chave = $chave;
                $nfce->estado = 'aprovado';
                $nfce->numero = $doc['numero'];
                $nfce->data_emissao = date('Y-m-d H:i:s');
                $nfce->save();

                $this->atualizaNumeroNfce($empresa, $configUsuarioEmissao, $nfce, $doc['numero']);

                file_put_contents(public_path('xml_nfce_contigencia/').$chave.'.xml', $signed);

                __createLog($nfce->empresa_id, 'NFCe', 'transmitir', 'Emitida em contigência número ' . $doc['numero']);

                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                    'erro' => 0,
                    'mensagem' => 'NFC-e emitida em contingência',
                    'recibo' => '',
                    'chave' => $chave,
                    'contigencia' => 1
                ];
            }

            $resultado = $nfe_service->transmitir($signed, $doc['chave']);

            $nfce->reenvio_contigencia = 0;

            if($resultado['erro'] == 0){

                $nfce->chave = $doc['chave'];
                $nfce->estado = 'aprovado';
                $nfce->numero = $doc['numero'];
                $nfce->recibo = $resultado['success'];
                $nfce->data_emissao = date('Y-m-d H:i:s');
                $nfce->save();

                $this->atualizaNumeroNfce($empresa, $configUsuarioEmissao, $nfce, $doc['numero']);

                __createLog($nfce->empresa_id, 'NFCe', 'transmitir', "Emitida número $nfce->numero - $nfce->chave APROVADA");

                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                    'erro' => 0,
                    'mensagem' => 'NFC-e transmitida com sucesso',
                    'recibo' => $resultado['success'],
                    'chave' => $nfce->chave
                ];
            }

            $recibo = $resultado['recibo'] ?? null;
            $error = $resultado['error'];

            if($nfce->chave == ''){
                $nfce->chave = $doc['chave'];
            }

            if($nfce->signed_xml == null){
                $nfce->signed_xml = $signed;
            }

            if($nfce->recibo == null){
                $nfce->recibo = $recibo;
            }

            $nfce->estado = 'rejeitado';

            if(isset($error['protNFe'])){
                $motivo = $error['protNFe']['infProt']['xMotivo'];
                $cStat = $error['protNFe']['infProt']['cStat'];

                $nfce->motivo_rejeicao = substr("[$cStat] $motivo", 0, 200);
                $nfce->save();

                __createLog($nfce->empresa_id, 'NFCe', 'erro', "REJEITADA $nfce->chave - $motivo");

                return [
                    'id' => $nfce->id,
                    'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                    'erro' => 1,
                    'mensagem' => "[$cStat] $motivo"
                ];
            }

            $nfce->motivo_rejeicao = substr(is_string($error) ? $error : json_encode($error), 0, 200);
            $nfce->save();

            return [
                'id' => $nfce->id,
                'pedido' => $nfce->numero_sequencial ?? $nfce->id,
                'erro' => 1,
                'mensagem' => is_string($error) ? $error : json_encode($error)
            ];

        }catch(\Exception $e){

            if(isset($nfce)){
                __createLog($nfce->empresa_id, 'NFCe', 'erro', $e->getMessage());
            }

            return [
                'id' => $id,
                'pedido' => $id,
                'erro' => 1,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    private function getProximoNumeroNfce($empresa, $configUsuarioEmissao, $nfce)
    {
        if($configUsuarioEmissao == null){

            if($nfce->ambiente == 2){
                return ((int) $empresa->numero_ultima_nfce_homologacao) + 1;
            }

            return ((int) $empresa->numero_ultima_nfce_producao) + 1;
        }

        return ((int) $configUsuarioEmissao->numero_ultima_nfce) + 1;
    }

    private function atualizaNumeroNfce($empresa, $configUsuarioEmissao, $nfce, $numero)
    {
        if($configUsuarioEmissao == null){
            if($empresa->ambiente == 2){
                $empresa->numero_ultima_nfce_homologacao = $numero;
            }else{
                $empresa->numero_ultima_nfce_producao = $numero;
            }

            $empresa->save();
        }else{
            $configUsuarioEmissao->numero_ultima_nfce = $numero;
            $configUsuarioEmissao->save();
        }
    }

    private function getContigencia($empresa_id)
    {
        $active = Contigencia::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->where('documento', 'NFCe')
        ->first();

        return $active != null ? 1 : 0;
    }
}