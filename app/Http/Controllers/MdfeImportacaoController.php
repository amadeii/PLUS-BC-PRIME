<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\CTeDescarga;
use App\Models\Empresa;
use App\Models\InfoDescarga;
use App\Models\Mdfe;
use App\Models\Motorista;
use App\Models\Localizacao;
use App\Models\MunicipioCarregamento;
use App\Models\NFeDescarga;
use App\Models\Percurso;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class MdfeImportacaoController extends Controller
{
    public function __construct()
    {
        if (!is_dir(public_path('xml_mdfe'))) {
            mkdir(public_path('xml_mdfe'), 0777, true);
        }

        if (!is_dir(storage_path('app/temp_mdfe_importacao'))) {
            mkdir(storage_path('app/temp_mdfe_importacao'), 0777, true);
        }
    }

    public function index()
    {
        return view('mdfe.importacao.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:zip'
        ]);

        $importados = 0;
        $duplicados = 0;
        $erros = 0;
        $mensagens = [];

        try {
            $empresaId = $request->empresa_id;
            $empresa = Empresa::findOrFail($empresaId);

            $zip = new ZipArchive();
            $file = $request->file('arquivo');

            if ($zip->open($file->getRealPath()) !== true) {
                session()->flash('flash_error', 'Não foi possível abrir o arquivo ZIP.');
                return redirect()->back();
            }

            $pastaTemp = storage_path('app/temp_mdfe_importacao/' . uniqid());
            mkdir($pastaTemp, 0777, true);

            $zip->extractTo($pastaTemp);
            $zip->close();

            $arquivos = $this->buscarXmls($pastaTemp);

            if (sizeof($arquivos) == 0) {
                session()->flash('flash_error', 'Nenhum XML de MDF-e encontrado no ZIP.');
                return redirect()->back();
            }


            foreach ($arquivos as $arquivoXml) {
                try {
                    DB::transaction(function () use ($arquivoXml, $empresa, &$importados, &$duplicados, &$mensagens) {

                        $xmlString = file_get_contents($arquivoXml);
                        $xml = simplexml_load_string($xmlString);

                        if (!$xml) {
                            throw new \Exception('XML inválido.');
                        }

                        $xml->registerXPathNamespace('n', 'http://www.portalfiscal.inf.br/mdfe');

                        $chave = $this->getChave($xml);

                        if (!$chave) {
                            throw new \Exception('Chave do MDF-e não encontrada.');
                        }

                        // if (Mdfe::where('empresa_id', $empresa->id)->where('chave', $chave)->exists()) {
                        //     $duplicados++;
                        //     $mensagens[] = "MDF-e {$chave} já importado.";
                        //     return;
                        // }

                        $ide = $this->first($xml->xpath('//n:infMDFe/n:ide'));
                        $emit = $this->first($xml->xpath('//n:infMDFe/n:emit'));
                        $rodo = $this->first($xml->xpath('//n:infMDFe/n:infModal/n:rodo'));
                        $tot = $this->first($xml->xpath('//n:infMDFe/n:tot'));
                        $infDoc = $this->first($xml->xpath('//n:infMDFe/n:infDoc'));
                        $prot = $this->first($xml->xpath('//n:protMDFe/n:infProt'));

                        $veiculo = $this->cadastrarVeiculoSeNecessario($xml, $empresa);
                        $motorista = $this->cadastrarMotoristaSeNecessario($xml, $empresa);

                        $ufInicio = $this->text($ide, 'UFIni');
                        $ufFim = $this->text($ide, 'UFFim');

                        $dataInicio = $this->text($ide, 'dhIniViagem') ?: $this->text($ide, 'dhEmi');
                        $dataInicio = $dataInicio ? date('Y-m-d', strtotime($dataInicio)) : date('Y-m-d');
                        $localizacao = Localizacao::where('empresa_id', $empresa->id)->first();
                        $mdfe = Mdfe::create([
                            'empresa_id' => $empresa->id,
                            'uf_inicio' => $ufInicio ?: $empresa->cidade->uf,
                            'uf_fim' => $ufFim ?: $empresa->cidade->uf,
                            'encerrado' => false,
                            'data_inicio_viagem' => $dataInicio,
                            'carga_posterior' => false,
                            'cnpj_contratante' => $this->getContratante($xml, $emit, $empresa),

                            'veiculo_tracao_id' => $veiculo ? $veiculo->id : null,
                            'veiculo_reboque_id' => null,
                            'veiculo_reboque2_id' => null,
                            'veiculo_reboque3_id' => null,

                            'estado_emissao' => 'aprovado',
                            'mdfe_numero' => (int) $this->text($ide, 'nMDF'),
                            'chave' => $chave,
                            'protocolo' => $this->text($prot, 'nProt'),

                            'seguradora_nome' => $this->value($xml, '//n:seg/n:infSeg/n:xSeg') ?: '',
                            'seguradora_cnpj' => $this->value($xml, '//n:seg/n:infSeg/n:CNPJ') ?: '',
                            'numero_apolice' => $this->value($xml, '//n:seg/n:nApol') ?: '',
                            'numero_averbacao' => $this->value($xml, '//n:seg/n:nAver') ?: '',

                            'valor_carga' => $this->decimal($this->text($tot, 'vCarga')),
                            'quantidade_carga' => $this->decimal($this->text($tot, 'qCarga')),
                            'info_complementar' => substr($this->value($xml, '//n:infAdic/n:infCpl') ?: '', 0, 60),
                            'info_adicional_fisco' => substr($this->value($xml, '//n:infAdic/n:infAdFisco') ?: '', 0, 60),

                            'condutor_nome' => $motorista ? $motorista->nome : '',
                            'condutor_cpf' => $motorista ? $motorista->cpf : '',
                            'lac_rodo' => '',
                            'tp_emit' => (int) ($this->text($ide, 'tpEmit') ?: 1),
                            'tp_transp' => (int) ($this->text($ide, 'tpTransp') ?: 0),

                            'produto_pred_nome' => '',
                            'produto_pred_ncm' => '',
                            'produto_pred_cod_barras' => '',
                            'cep_carrega' => '',
                            'cep_descarrega' => '',
                            'tp_carga' => '',

                            'latitude_carregamento' => '',
                            'longitude_carregamento' => '',
                            'latitude_descarregamento' => '',
                            'longitude_descarregamento' => '',
                            'local_id' => $localizacao->id,
                            'tipo_modal' => (int) ($this->text($ide, 'modal') ?: 1),

                            'nome_pagador' => null,
                            'documento_pagador' => null,
                            'ind_pag' => null,
                            'valor_transporte' => null,

                            'importado' => true,
                            'xml_importado' => $chave . '.xml',
                            'data_importacao' => now(),
                        ]);

                        file_put_contents(public_path('xml_mdfe/' . $chave . '.xml'), $xmlString);

                        $this->cadastrarMunicipiosCarregamento($xml, $mdfe);
                        $this->cadastrarPercurso($xml, $mdfe);
                        $this->cadastrarDescarregamentos($xml, $mdfe);

                        $importados++;
                        $mensagens[] = "MDF-e {$chave} importado com sucesso.";
                    });
} catch (\Exception $e) {
    $erros++;
    $mensagens[] = basename($arquivoXml) . ': ' . $e->getMessage();
}
}

__createLog($empresaId, 'MDFe', 'importar', "Importados: {$importados}, duplicados: {$duplicados}, erros: {$erros}");

session()->flash('flash_success', "Importação finalizada. Importados: {$importados}, duplicados: {$duplicados}, erros: {$erros}.");
session()->flash('mdfe_importacao_mensagens', $mensagens);

} catch (\Exception $e) {
    __createLog(request()->empresa_id, 'MDFe', 'erro', $e->getMessage());
    session()->flash('flash_error', 'Erro na importação: ' . $e->getMessage());
}

return redirect()->route('mdfe.index');
}

private function cadastrarVeiculoSeNecessario($xml, $empresa)
{
    $veic = $this->first($xml->xpath('//n:infMDFe/n:infModal/n:rodo/n:veicTracao'));

    if (!$veic) {
        return null;
    }

    $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', $this->text($veic, 'placa')));

    if (!$placa) {
        return null;
    }

    $veiculo = Veiculo::where('empresa_id', $empresa->id)->where('placa', $placa)->first();

    if ($veiculo) {
        return $veiculo;
    }

    $prop = $veic->prop ?? null;

    return Veiculo::create([
        'empresa_id' => $empresa->id,
        'placa' => $placa,
        'uf' => $this->text($veic, 'UF') ?: $empresa->cidade->uf,
        'cor' => 'NAO INF',
        'marca' => 'NAO INF',
        'modelo' => 'NAO INF',
        'rntrc' => $this->value($xml, '//n:infMDFe/n:infModal/n:rodo/n:infANTT/n:RNTRC') ?: null,

        'taf' => null,
        'renavam' => $this->text($veic, 'RENAVAM') ?: null,
        'numero_registro_estadual' => null,

        'tipo' => $this->text($veic, 'tpVeic') ?: '02',
        'tipo_carroceria' => $this->text($veic, 'tpCar') ?: '00',
        'tipo_rodado' => $this->text($veic, 'tpRod') ?: '01',

        'tara' => $this->text($veic, 'tara') ?: '0',
        'capacidade' => $this->text($veic, 'capKG') ?: '0',

        'proprietario_documento' => $prop ? ($this->text($prop, 'CNPJ') ?: $this->text($prop, 'CPF')) : preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
        'proprietario_nome' => $prop ? substr($this->text($prop, 'xNome'), 0, 40) : substr($empresa->nome, 0, 40),
        'proprietario_ie' => $prop ? ($this->text($prop, 'IE') ?: '') : '',
        'proprietario_uf' => $prop ? ($this->text($prop, 'UF') ?: $empresa->cidade->uf) : $empresa->cidade->uf,
        'proprietario_tp' => $prop ? (int) ($this->text($prop, 'tpProp') ?: 0) : 0,

        'funcionario_id' => null,
        'status' => 1,
    ]);
}

private function cadastrarMotoristaSeNecessario($xml, $empresa)
{
    $condutor = $this->first($xml->xpath('//n:infMDFe/n:infModal/n:rodo/n:veicTracao/n:condutor'));

    if (!$condutor) {
        return null;
    }

    $cpf = preg_replace('/[^0-9]/', '', $this->text($condutor, 'CPF'));
    $nome = trim($this->text($condutor, 'xNome'));

    if (!$cpf || !$nome) {
        return null;
    }

    $cpf = substr($cpf, 0, 3) . '.' .
    substr($cpf, 3, 3) . '.' .
    substr($cpf, 6, 3) . '-' .
    substr($cpf, 9, 2);

    return Motorista::firstOrCreate([
        'empresa_id' => $empresa->id,
        'cpf' => $cpf,
    ], [
        'nome' => $nome,
        'padrao' => 0,
    ]);
}

private function cadastrarMunicipiosCarregamento($xml, $mdfe)
{
    $itens = $xml->xpath('//n:infMDFe/n:ide/n:infMunCarrega');

    foreach ($itens as $item) {
        $codigo = $this->text($item, 'cMunCarrega');
        $cidade = Cidade::where('codigo', $codigo)->first();

        if ($cidade) {
            MunicipioCarregamento::create([
                'mdfe_id' => $mdfe->id,
                'cidade_id' => $cidade->id,
            ]);
        }
    }
}

private function cadastrarPercurso($xml, $mdfe)
{
    $itens = $xml->xpath('//n:infMDFe/n:ide/n:infPercurso');

    foreach ($itens as $item) {
        $uf = $this->text($item, 'UFPer');

        if ($uf) {
            Percurso::create([
                'mdfe_id' => $mdfe->id,
                'uf' => $uf,
            ]);
        }
    }
}

private function cadastrarDescarregamentos($xml, $mdfe)
{
    $itens = $xml->xpath('//n:infMDFe/n:infDoc/n:infMunDescarga');

    foreach ($itens as $item) {
        $codigo = $this->text($item, 'cMunDescarga');
        $cidade = Cidade::where('codigo', $codigo)->first();

        if (!$cidade) {
            continue;
        }

        $info = InfoDescarga::create([
            'mdfe_id' => $mdfe->id,
            'tp_unid_transp' => 1,
            'id_unid_transp' => '',
            'quantidade_rateio' => 0,
            'cidade_id' => $cidade->id,
        ]);

        foreach ($item->infNFe ?? [] as $nfe) {
            $chave = $this->text($nfe, 'chNFe');

            if ($chave) {
                NFeDescarga::create([
                    'info_id' => $info->id,
                    'chave' => $chave,
                    'seg_cod_barras' => '',
                ]);
            }
        }

        foreach ($item->infCTe ?? [] as $cte) {
            $chave = $this->text($cte, 'chCTe');

            if ($chave) {
                CTeDescarga::create([
                    'info_id' => $info->id,
                    'chave' => $chave,
                    'seg_cod_barras' => '',
                ]);
            }
        }
    }
}

private function buscarXmls($dir)
{
    $arquivos = [];

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            $arquivos = array_merge($arquivos, $this->buscarXmls($path));
        } elseif (strtolower(pathinfo($path, PATHINFO_EXTENSION)) == 'xml') {
            $arquivos[] = $path;
        }
    }

    return $arquivos;
}

private function getChave($xml)
{
    $inf = $this->first($xml->xpath('//n:infMDFe'));

    if (!$inf) {
        return null;
    }

    $id = (string) $inf['Id'];

    return str_replace('MDFe', '', $id);
}

private function getContratante($xml, $emit, $empresa)
{
    $doc = $this->value($xml, '//n:infContratante/n:CNPJ') ?: $this->value($xml, '//n:infContratante/n:CPF');

    if ($doc) {
        return $doc;
    }

    $doc = $this->text($emit, 'CNPJ') ?: $this->text($emit, 'CPF');

    return $doc ?: preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
}

private function value($xml, $path)
{
    $res = $xml->xpath($path);

    if (!$res || !isset($res[0])) {
        return '';
    }

    return trim((string) $res[0]);
}

private function text($node, $field)
{
    if (!$node || !isset($node->{$field})) {
        return '';
    }

    return trim((string) $node->{$field});
}

private function first($array)
{
    return $array && isset($array[0]) ? $array[0] : null;
}

private function decimal($valor)
{
    if ($valor === null || $valor === '') {
        return 0;
    }

    return (float) str_replace(',', '.', $valor);
}
}