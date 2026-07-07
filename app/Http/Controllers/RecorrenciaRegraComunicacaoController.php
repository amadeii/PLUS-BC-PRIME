<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecorrenciaRegraComunicacao;

class RecorrenciaRegraComunicacaoController extends Controller
{
    public function index(Request $request)
    {
        $data = RecorrenciaRegraComunicacao::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->gatilho), function ($q) use ($request) {
            return $q->where('gatilho', $request->gatilho);
        })
        ->when($request->ativo != '', function ($q) use ($request) {
            return $q->where('ativo', $request->ativo);
        })
        ->orderBy('gatilho', 'asc')
        ->orderBy('dias', 'asc')
        ->paginate(__itensPagina());

        return view('recorrencia_regra_comunicacao.index', compact('data'));
    }

    public function create()
    {
        return view('recorrencia_regra_comunicacao.create');
    }

    public function edit($id)
    {
        $item = RecorrenciaRegraComunicacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('recorrencia_regra_comunicacao.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'empresa_id' => request()->empresa_id,
                'ativo' => $request->has('ativo') ? 1 : 0,
                'email_ativo' => $request->has('email_ativo') ? 1 : 0,
                'whatsapp_ativo' => $request->has('whatsapp_ativo') ? 1 : 0,
            ]);

            RecorrenciaRegraComunicacao::create($request->all());

            session()->flash("flash_success", "Regra criada com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-regra-comunicacao.index');
    }

    public function update(Request $request, $id)
    {
        $item = RecorrenciaRegraComunicacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $request->merge([
                'ativo' => $request->has('ativo') ? 1 : 0,
                'email_ativo' => $request->has('email_ativo') ? 1 : 0,
                'whatsapp_ativo' => $request->has('whatsapp_ativo') ? 1 : 0,
            ]);

            $item->fill($request->all())->save();

            session()->flash("flash_success", "Regra alterada com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-regra-comunicacao.index');
    }

    public function destroy($id)
    {
        $item = RecorrenciaRegraComunicacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->delete();

            session()->flash("flash_success", "Regra removida com sucesso!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencia-regra-comunicacao.index');
    }

    public function show($id)
    {
        $item = RecorrenciaRegraComunicacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('recorrencia_regra_comunicacao.show', compact('item'));
    }
}