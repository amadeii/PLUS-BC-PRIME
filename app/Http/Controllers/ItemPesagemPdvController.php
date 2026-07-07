<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\PdvPesagemItem;
use App\Models\ComandaPesoItem;

class ItemPesagemPdvController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:item_pesagem_pdv_view', ['only' => ['index', 'apiItens']]);
        $this->middleware('permission:item_pesagem_pdv_edit', ['only' => ['store', 'storeComanda', 'destroy', 'destroyComanda']]);
    }

    public function index(Request $request)
    {
        $empresa_id = request()->empresa_id;

        $produtos = Produto::where('empresa_id', $empresa_id)
        ->orderBy('nome')
        ->where('status', 1)
        ->get();

        $data = PdvPesagemItem::with('produto')
        ->where('empresa_id', $empresa_id)
        ->when($request->pesquisa, function ($q) use ($request) {
            $q->whereHas('produto', function ($query) use ($request) {
                $query->where('nome', 'like', "%{$request->pesquisa}%")
                ->orWhere('codigo_barras', 'like', "%{$request->pesquisa}%");
            });
        })
        ->orderBy('ordem')
        ->paginate(__itensPagina());

        $itensComanda = ComandaPesoItem::with('produto')
        ->where('empresa_id', $empresa_id)
        ->orderBy('ordem')
        ->get();

        return view('item_pesagem_pdv.index', compact('data', 'produtos', 'itensComanda'));
    }

    public function store(Request $request)
    {
        $empresa_id = request()->empresa_id;

        $request->validate([
            'produto_id' => 'required',
            'valor' => 'required',
            'ordem' => 'nullable|integer',
        ]);

        PdvPesagemItem::updateOrCreate(
            [
                'empresa_id' => $empresa_id,
                'produto_id' => $request->produto_id,
            ],
            [
                'valor' => __convert_value_bd($request->valor),
                'ordem' => $request->ordem ?? 0,
                'status' => $request->status ? true : false,
                'sem_peso' => $request->sem_peso
            ]
        );

        session()->flash('flash_success', 'Item de pesagem salvo com sucesso.');
        return redirect()->route('item-pesagem-pdv.index');
    }

    public function storeComanda(Request $request)
    {
        $empresa_id = request()->empresa_id;

        $request->validate([
            'produto_id' => 'required',
            'ordem' => 'nullable|integer',
        ]);

        ComandaPesoItem::updateOrCreate(
            [
                'empresa_id' => $empresa_id,
                'produto_id' => $request->produto_id,
            ],
            [
                'ordem' => $request->ordem ?? 0,
                'ativo' => $request->ativo ? true : false,
            ]
        );

        session()->flash('flash_success', 'Item da comanda salvo com sucesso.');
        return redirect()->route('item-pesagem-pdv.index');
    }

    public function destroy($id)
    {
        $item = PdvPesagemItem::where('empresa_id', request()->empresa_id)
        ->findOrFail($id);

        $item->delete();

        session()->flash('flash_success', 'Item removido com sucesso.');
        return redirect()->route('item-pesagem-pdv.index');
    }

    public function destroyComanda($id)
    {
        $item = ComandaPesoItem::where('empresa_id', request()->empresa_id)
        ->findOrFail($id);

        $item->delete();

        session()->flash('flash_success', 'Item da comanda removido com sucesso.');
        return redirect()->route('item-pesagem-pdv.index');
    }
}