<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Exception;

class TesteEmailController extends Controller
{
    public function index()
    {
        $configEmail = [
            'mailer' => env('MAIL_MAILER'),
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD') ? '********' : 'Não informado',
            'encryption' => env('MAIL_ENCRYPTION'),
            'from_address' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
        ];

        return view('teste_email.index', compact('configEmail'));
    }

    public function enviar(Request $request)
    {
        $request->validate([
            'email_destino' => 'required|email',
            'assunto' => 'required|max:255',
            'texto' => 'required'
        ]);

        try {

            $emailTemplate = [
                'assunto' => $request->assunto,
                'conteudo' => nl2br($request->texto)
            ];

            Mail::send('teste_email.template', [
                'conteudo' => $emailTemplate['conteudo']
            ], function($m) use ($request, $emailTemplate) {

                $nomeEmpresa = str_replace("_", " ", env('MAIL_FROM_NAME'));
                $emailEnvio = env('MAIL_USERNAME');

                $m->from($emailEnvio, $nomeEmpresa);

                $m->subject($emailTemplate['assunto']);

                $m->to($request->email_destino);
            });

            session()->flash('mensagem_sucesso', 'E-mail enviado com sucesso!');
        } catch (Exception $e) {

            session()->flash('mensagem_erro', $e->getMessage());
        }

        return redirect()->back();
    }
}