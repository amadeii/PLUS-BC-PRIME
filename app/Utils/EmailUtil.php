<?php

namespace App\Utils;

use Illuminate\Support\Str;
use App\Models\Empresa;
use App\Models\PlanoConta;
use App\Models\EmailConfig;
use App\Models\EscritorioContabil;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Mail;

class EmailUtil {

	public function enviaEmailPHPMailer($destinatario, $subject, $body, $emailConfig, $fileDir = null, $filePdf = null){
		$mail = new PHPMailer(true);

		try {
			if($emailConfig->smtp_debug){
				$mail->SMTPDebug = SMTP::DEBUG_SERVER;   
			}                   
			$mail->isSMTP();                                            
			$mail->Host = $emailConfig->host;                     
			$mail->SMTPAuth = (bool)$emailConfig->smtp_auth;                                   
			$mail->Username = $emailConfig->email;                     
			$mail->Password = $emailConfig->senha;                               
			// $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			if (strtolower($emailConfig->criptografia) == 'ssl') {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			} else {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			}        
			$mail->Port = $emailConfig->porta; 

			$mail->setFrom($emailConfig->email, $emailConfig->nome); 
			$mail->addAddress($destinatario); 

			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';

			if($fileDir){
				if(is_array($fileDir)){
					foreach($fileDir as $f){
						$this->anexarArquivo($mail, $f);
					}
				}else{
					$this->anexarArquivo($mail, $fileDir);
				}
			}

			if($filePdf){
				$this->anexarArquivo($mail, $filePdf);
			}

			$mail->CharSet = 'UTF-8';

			$mail->Subject = $subject ?? '';
			$body = (string) $body;
			$mail->Body = $body ?? '';
			$mail->AltBody = strip_tags($body ?? '');


			$mail->send();
			return [
				'sucesso' => true
			];
		} catch (Exception $e) {
			\Log::error('Erro ao enviar e-mail PHPMailer', [
				'erro_phpmailer' => $mail->ErrorInfo,
				'exception' => $e->getMessage(),
				'fileDir' => $fileDir,
				'filePdf' => $filePdf,
			]);

			return [
				'erro' => $mail->ErrorInfo ?: $e->getMessage()
			];
		}
	}

	private function anexarArquivo($mail, $arquivo)
	{
		if (!$arquivo) return;

		if (!file_exists($arquivo) || !is_readable($arquivo)) {
			\Log::error('Arquivo de anexo não encontrado ou sem permissão', [
				'arquivo' => $arquivo,
				'exists' => file_exists($arquivo),
				'readable' => is_readable($arquivo),
			]);

			return;
		}

		$mail->addAttachment($arquivo, basename($arquivo));
	}

	public function enviarXmlContador($empresa_id, $fileDir, $documento, $chave){
		$escritorio = EscritorioContabil::where('empresa_id', $empresa_id)
		->where('envio_xml_automatico', 1)->first();
		if($escritorio == null) return 0;

		$emailConfig = EmailConfig::where('empresa_id', $empresa_id)
		->where('status', 1)
		->first();

		$destinatario = $escritorio->email;
		$assunto = "Envio de XML";
		$body = "$documento chave $chave";

		try{
			if($emailConfig != null){

				$result = $this->enviaEmailPHPMailer($destinatario, $assunto, $body, $emailConfig, $fileDir);
			}else{
				Mail::send('mail.envio_xml', ['body' => $body], function($m) use ($destinatario, $assunto, $fileDir, $chave){

					$nomeEmail = env('MAIL_FROM_NAME');

					$m->from(env('MAIL_USERNAME'), $nomeEmail);
					$m->subject($assunto);
					$m->to($destinatario);

					$m->attach($fileDir, [
						'as' => $chave . '.xml',
						'mime' => 'application/xml',
					]);
				});
			}
			return 1;
		}catch(\Exception $e){
			return 0;
		}
	}

	public function enviarXmlContadorZip($empresa_id, $fileDir, $documento, $body, $filePdf = null){
		$escritorio = EscritorioContabil::where('empresa_id', $empresa_id)->first();
		if($escritorio == null) return 0;

		$emailConfig = EmailConfig::where('empresa_id', $empresa_id)
		->where('status', 1)
		->first();

		$destinatario = $escritorio->email;
		$assunto = "Envio de XML $documento";

		try{
			if($emailConfig != null){

				$result = $this->enviaEmailPHPMailer($destinatario, $assunto, $body, $emailConfig, $fileDir, $filePdf);
			}else{
				Mail::send('mail.envio_xml', ['body' => $body], function($m) use ($destinatario, $assunto, $fileDir, $filePdf){

					$nomeEmail = env('MAIL_FROM_NAME');
					$m->from(env('MAIL_USERNAME'), $nomeEmail);
					$m->subject($assunto);
					// $m->attach($fileDir);
					// $m->attach($filePdf);
					if($fileDir){
						if(is_array($fileDir)){
							foreach($fileDir as $f){
								if($f && file_exists($f)){
									$m->attach($f);
								}
							}
						}else{
							if(file_exists($fileDir)){
								$m->attach($fileDir);
							}
						}
					}

					if($filePdf && file_exists($filePdf)){
						$m->attach($filePdf);
					}
					$m->to($destinatario);
				});
			}
			return 1;
		}catch(\Exception $e){
			echo $e->getMessage();

			return 0;
		}
	}

	public function enviarCobrancaRecorrente($empresa_id, $destinatario, $assunto, $body)
	{
		$emailConfig = EmailConfig::where('empresa_id', $empresa_id)
		->where('status', 1)
		->first();

		try{
			if($emailConfig != null){
				$result = $this->enviaEmailPHPMailer($destinatario, $assunto, $body, $emailConfig);

				if(isset($result['erro'])){
					return [
						'sucesso' => false,
						'erro' => $result['erro']
					];
				}

				return [
					'sucesso' => true
				];
			}else{
				Mail::send('mail.envio_xml', ['body' => $body], function($m) use ($destinatario, $assunto){
					$nomeEmail = env('MAIL_FROM_NAME');

					$m->from(env('MAIL_USERNAME'), $nomeEmail);
					$m->subject($assunto);
					$m->to($destinatario);
				});

				return [
					'sucesso' => true
				];
			}

		}catch(\Exception $e){
			return [
				'sucesso' => false,
				'erro' => $e->getMessage()
			];
		}
	}

}