<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanoWhiteLabel;

class PlanoWhiteLabelController extends Controller
{
    public function index(Request $request)
    {
        $data = PlanoWhiteLabel::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%{$request->nome}%");
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        return view('plano_white_label.index', compact('data'));
    }

    public function create()
    {
        return view('plano_white_label.create');
    }

    public function edit($id)
    {
        $item = PlanoWhiteLabel::findOrFail($id);

        return view('plano_white_label.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        try {

            PlanoWhiteLabel::create([
                'nome' => $request->nome,
                'valor_mensal' => __convert_value_bd($request->valor_mensal),
                'valor_por_empresa' => __convert_value_bd($request->valor_por_empresa),
                'limite_empresas' => $request->limite_empresas,
                'ativo' => $request->ativo ? 1 : 0,
            ]);

            session()->flash('flash_success', 'Cadastro realizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('planos-white-label.index');
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);

        $item = PlanoWhiteLabel::findOrFail($id);

        try {

            $item->fill([
                'nome' => $request->nome,
                'valor_mensal' => __convert_value_bd($request->valor_mensal),
                'valor_por_empresa' => __convert_value_bd($request->valor_por_empresa),
                'limite_empresas' => $request->limite_empresas,
                'ativo' => $request->ativo ? 1 : 0,
            ])->save();

            session()->flash('flash_success', 'Cadastro atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('planos-white-label.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:100',
            'valor_mensal' => 'required',
            'valor_por_empresa' => 'required'
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'valor_mensal.required' => 'Informe o valor mensal.',
            'valor_por_empresa.required' => 'Informe o valor por empresa.'
        ];

        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = PlanoWhiteLabel::findOrFail($id);

        try {

            $item->delete();

            session()->flash('flash_success', 'Removido com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->back();
    }
}