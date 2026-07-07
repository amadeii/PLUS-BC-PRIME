<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RelatorioXmlContadorConfig;
use App\Models\RelatorioXmlContadorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class RelatorioXmlContadorController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = $request->empresa_id;

        $config = RelatorioXmlContadorConfig::where('empresa_id', $empresa_id)->first();

        return view('relatorio_xml_contador.index', compact('config'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required',
            'dia_envio' => 'required|integer|min:1|max:28',
            'email_contador' => 'required|email',
            'ativo' => 'required|boolean',
            'mensagem_email' => 'nullable|string'
        ]);

        RelatorioXmlContadorConfig::updateOrCreate(
            ['empresa_id' => $request->empresa_id],
            [
                'dia_envio' => $request->dia_envio,
                'email_contador' => $request->email_contador,
                'ativo' => (bool) $request->ativo,
                'mensagem_email' => $request->mensagem_email
            ]
        );

        session()->flash('flash_success', 'Configuração salva com sucesso!');
        return redirect()->back();
    }


    public function logs(Request $request)
    {
        $empresa_id = $request->empresa_id;

        $logs = RelatorioXmlContadorLog::where('empresa_id', $empresa_id)
        ->when($request->email, function ($q) use ($request) {
            return $q->where('email_contador', 'LIKE', "%$request->email%");
        })
        ->when($request->status, function ($q) use ($request) {
            return $q->where('status', $request->status);
        })
        ->when($request->competencia, function ($q) use ($request) {

            [$mes, $ano] = explode('/', $request->competencia);

            return $q->whereMonth('competencia', $mes)
            ->whereYear('competencia', $ano);
        })
        ->orderBy('id', 'desc')
        ->paginate(30);

        return view('relatorio_xml_contador.logs', compact('logs'));
    }

    public function testarEnvio(Request $request)
    {
        $empresa_id = $request->empresa_id;

        Artisan::call('xml-contador:enviar', [
            'empresa_id' => $empresa_id
        ]);

        $saida = Artisan::output();

        session()->flash('flash_success', 'Teste executado com sucesso!');
        session()->flash('xml_contador_output', $saida);

        return redirect()->back();
    }
}