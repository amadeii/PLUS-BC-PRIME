<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RelatorioXmlContadorConfig;
use App\Models\RelatorioXmlContadorLog;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\Empresa;
use Mail;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;
use Dompdf\Dompdf;
use App\Models\EmailConfig;
use App\Utils\EmailUtil;
use App\Models\Inutilizacao;

class EnviarXmlContadorCommand extends Command
{

    protected $emailUtil;

    public function __construct(EmailUtil $emailUtil)
    {
        parent::__construct();
        $this->emailUtil = $emailUtil;
    }

    protected $signature = 'xml-contador:enviar {empresa_id?}';
    protected $description = 'Envia XMLs de NFe/NFCe aprovadas e canceladas para o contador';

    public function handle()
    {
        $hoje = Carbon::now();
        $empresaId = $this->argument('empresa_id');

        $configs = RelatorioXmlContadorConfig::where('ativo', true)
        ->when($empresaId, function ($q) use ($empresaId) {
            return $q->where('empresa_id', $empresaId);
        })
        ->when(!$empresaId, function ($q) use ($hoje) {
            return $q->where('dia_envio', $hoje->day);
        })
        ->get();

        foreach ($configs as $config) {
            $this->processarEmpresa($config);
        }

        return Command::SUCCESS;
    }

    private function processarEmpresa($config)
    {
        $competencia = Carbon::now()->subMonthNoOverflow()->startOfMonth();

        $inicio = $competencia->copy()->startOfMonth();
        $fim = $competencia->copy()->addMonth();

        $log = RelatorioXmlContadorLog::create([
            'empresa_id' => $config->empresa_id,
            'email_contador' => $config->email_contador,
            'competencia' => $competencia->format('Y-m-01'),
            'status' => 'erro',
            'mensagem' => 'Processando envio...'
        ]);

        try {

            $nfeAprovadas = Nfe::where('empresa_id', $config->empresa_id)
            ->where('data_emissao', '>=', $inicio)
            ->where('data_emissao', '<', $fim)
            ->where('estado', 'aprovado')
            ->get();

            $nfeCanceladas = Nfe::where('empresa_id', $config->empresa_id)
            ->where('data_emissao', '>=', $inicio)
            ->where('data_emissao', '<', $fim)
            ->where('estado', 'cancelado')
            ->get();

            $nfceAprovadas = Nfce::where('empresa_id', $config->empresa_id)
            ->where('data_emissao', '>=', $inicio)
            ->where('data_emissao', '<', $fim)
            ->where('estado', 'aprovado')
            ->get();

            $nfceCanceladas = Nfce::where('empresa_id', $config->empresa_id)
            ->where('data_emissao', '>=', $inicio)
            ->where('data_emissao', '<', $fim)
            ->where('estado', 'cancelado')
            ->get();

            $pasta = 'xml_contador/' . $config->empresa_id . '/' . $competencia->format('Y-m');

            Storage::disk('public')->makeDirectory($pasta);

            $zipNfeEmitidas = $this->gerarZip(
                $pasta . '/nfe_emitidas_' . $competencia->format('Y_m') . '.zip',
                $nfeAprovadas,
                'nfe',
                'aprovado'
            );

            $zipNfeCanceladas = $this->gerarZip(
                $pasta . '/nfe_canceladas_' . $competencia->format('Y_m') . '.zip',
                $nfeCanceladas,
                'nfe',
                'cancelado'
            );

            $zipNfceEmitidas = $this->gerarZip(
                $pasta . '/nfce_emitidas_' . $competencia->format('Y_m') . '.zip',
                $nfceAprovadas,
                'nfce',
                'aprovado'
            );

            $zipNfceCanceladas = $this->gerarZip(
                $pasta . '/nfce_canceladas_' . $competencia->format('Y_m') . '.zip',
                $nfceCanceladas,
                'nfce',
                'cancelado'
            );

            $empresa = Empresa::find($config->empresa_id);

            $inutilizadas = Inutilizacao::where('empresa_id', $config->empresa_id)
            ->where('created_at', '>=', $inicio)
            ->where('created_at', '<', $fim)
            ->get();

            $pdf = $this->gerarPdfResumo(
                $pasta,
                $competencia,
                $empresa,
                $nfeAprovadas,
                $nfeCanceladas,
                $nfceAprovadas,
                $nfceCanceladas,
                $inutilizadas
            );

            $retorno = $this->emailUtil->enviarXmlContadorZip(
                $config->empresa_id,
                [
                    $zipNfeEmitidas,
                    $zipNfeCanceladas,
                    $zipNfceEmitidas,
                    $zipNfceCanceladas
                ],
                $competencia->format('m/Y'),
                $config->mensagem_email,
                $pdf
            );

            if (!$retorno) {
                throw new \Exception('Falha ao enviar e-mail');
            }

            if (!$retorno) {
                throw new \Exception('Falha ao enviar e-mail');
            }

            $log->update([
                'total_nfe_aprovada' => $nfeAprovadas->count(),
                'total_nfe_cancelada' => $nfeCanceladas->count(),
                'total_nfce_aprovada' => $nfceAprovadas->count(),
                'total_nfce_cancelada' => $nfceCanceladas->count(),
                'arquivo_zip_nfe' => $zipNfeEmitidas,
                'arquivo_zip_nfce' => $zipNfceEmitidas,
                'arquivo_pdf' => $pdf,
                'status' => 'sucesso',
                'mensagem' => 'E-mail enviado com sucesso',
                'enviado_em' => now()
            ]);

            $this->info('Enviado empresa #' . $config->empresa_id);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ]);

            $this->error('Erro empresa #' . $config->empresa_id . ': ' . $e->getMessage());
        }
    }

    private function gerarZip($caminhoRelativo, $documentos, $tipo, $estado)
    {
        $caminhoCompleto = storage_path('app/public/' . $caminhoRelativo);

        $zip = new ZipArchive();

        if ($zip->open($caminhoCompleto, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            foreach ($documentos as $doc) {

                if (!$doc->chave) {
                    continue;
                }

                $nomeXml = $doc->chave . '.xml';
                $pastaXml = $this->getPastaXml($tipo, $estado);
                $caminhoXml = public_path($pastaXml . '/' . $nomeXml);

                if (!file_exists($caminhoXml)) {
                    continue;
                }

                $zip->addFile($caminhoXml, $nomeXml);
            }

            $zip->close();
        }

        return $caminhoCompleto;
    }

    private function getPastaXml($tipo, $estado)
    {
        if ($tipo == 'nfe') {
            return $estado == 'cancelado' ? 'xml_nfe_cancelada' : 'xml_nfe';
        }

        return $estado == 'cancelado' ? 'xml_nfce_cancelada' : 'xml_nfce';
    }

    private function gerarPdfResumo($pasta, $competencia, $empresa, $nfeAprovadas, $nfeCanceladas, $nfceAprovadas, $nfceCanceladas, $inutilizadas)
    {
        $html = view('pdf.xml_contador_resumo', compact(
            'competencia',
            'empresa',
            'nfeAprovadas',
            'nfeCanceladas',
            'nfceAprovadas',
            'nfceCanceladas',
            'inutilizadas'
        ))->render();

        $domPdf = new Dompdf(['enable_remote' => true]);
        $domPdf->loadHtml($html);
        $domPdf->setPaper('A4');
        $domPdf->render();

        $arquivo = $pasta . '/resumo_' . $competencia->format('Y_m') . '.pdf';
        $caminhoCompleto = storage_path('app/public/' . $arquivo);

        file_put_contents($caminhoCompleto, $domPdf->output());

        return $caminhoCompleto;
    }
}