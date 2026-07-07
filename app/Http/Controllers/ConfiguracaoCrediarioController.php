<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoCrediario;
use Illuminate\Http\Request;

class ConfiguracaoCrediarioController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:configuracao_crediario_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:configuracao_crediario_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:frigobar_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:configuracao_crediario_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $empresa_id = request()->empresa_id;

        $data = ConfiguracaoCrediario::where('empresa_id', $empresa_id)
        ->when($request->status != '', function($q) use ($request){
            return $q->where('ativo', $request->status);
        })
        ->orderBy('valor_minimo')
        ->paginate(env('PAGINACAO'));

        return view('configuracao_crediario.index', compact('data'));
    }

    public function create()
    {
        return view('configuracao_crediario.create');
    }

    public function edit($id)
    {
        $item = ConfiguracaoCrediario::findOrFail($id);

        return view('configuracao_crediario.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'valor_minimo' => 'required',
            'maximo_parcelas' => 'required|integer|min:1',
            'parcelas_sem_juros' => 'required|integer|min:1',
        ]);

        try {

            ConfiguracaoCrediario::create([
                'empresa_id' => request()->empresa_id,
                'valor_minimo' => __convert_value_bd($request->valor_minimo),
                'valor_maximo' => $request->valor_maximo ? __convert_value_bd($request->valor_maximo) : null,
                'maximo_parcelas' => $request->maximo_parcelas,
                'parcelas_sem_juros' => $request->parcelas_sem_juros,
                'juros_percentual' => __convert_value_bd($request->juros_percentual),
                'primeiro_vencimento_dias' => $request->primeiro_vencimento_dias ?? 30,
                'intervalo_parcelas_dias' => $request->intervalo_parcelas_dias ?? 30,
                'ativo' => $request->ativo ? 1 : 0,
            ]);

            session()->flash("success", "Configuração cadastrada com sucesso!");

        } catch (\Exception $e) {

            session()->flash("danger", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('configuracao-crediario.index');
    }

    public function update(Request $request, $id)
    {
        $item = ConfiguracaoCrediario::findOrFail($id);

        $this->validate($request, [
            'valor_minimo' => 'required',
            'maximo_parcelas' => 'required|integer|min:1',
            'parcelas_sem_juros' => 'required|integer|min:1',
        ]);

        try {

            $item->valor_minimo = __convert_value_bd($request->valor_minimo);
            $item->valor_maximo = $request->valor_maximo ? __convert_value_bd($request->valor_maximo) : null;

            $item->maximo_parcelas = $request->maximo_parcelas;
            $item->parcelas_sem_juros = $request->parcelas_sem_juros;

            $item->juros_percentual = __convert_value_bd($request->juros_percentual);

            $item->primeiro_vencimento_dias = $request->primeiro_vencimento_dias ?? 30;
            $item->intervalo_parcelas_dias = $request->intervalo_parcelas_dias ?? 30;

            $item->ativo = $request->ativo ? 1 : 0;

            $item->save();

            session()->flash("success", "Configuração atualizada com sucesso!");

        } catch (\Exception $e) {

            session()->flash("danger", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('configuracao-crediario.index');
    }

    public function destroy($id)
    {
        $item = ConfiguracaoCrediario::findOrFail($id);

        try {

            $item->delete();

            session()->flash("success", "Registro removido!");

        } catch (\Exception $e) {

            session()->flash("danger", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('configuracao-crediario.index');
    }
}