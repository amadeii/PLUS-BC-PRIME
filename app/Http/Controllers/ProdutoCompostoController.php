<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoComposicao;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

class ProdutoCompostoController extends Controller
{
    public function create($id)
    {
        $data = $this->getComposicaoRecursiva($id);
        $item = Produto::findOrFail($id);

        return view('produtos.composto.composto', compact('data', 'item'));
    }

    public function store(Request $request, $id)
    {
        // dd($request);
        $produto = Produto::findOrFail($id);
        try {
            $request->merge([
                'produto_id' => $request->produto_id,
                'ingrediente_id' => $request->ingrediente_id,
                'quantidade' => __convert_value_bd($request->quantidade)
            ]);
            ProdutoComposicao::create($request->all());
            $this->atualizarValorCompraProdutoComposto($produto->id);
            session()->flash("flash_success", "Item inserido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('produto-composto.create', [$produto->id]);
    }

    public function destroy($id)
    {
        $item = ProdutoComposicao::findOrFail($id);
        try {
            $produtoId = $item->produto_id;
            $item->delete();

            $this->atualizarValorCompraProdutoComposto($produtoId);
            session()->flash("flash_success", "Item removido!");

        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    private function atualizarValorCompraProdutoComposto($produtoId)
    {
        $produto = Produto::findOrFail($produtoId);

        $itens = ProdutoComposicao::with('ingrediente')
        ->where('produto_id', $produtoId)
        ->get();

        $valorCompra = 0;

        foreach ($itens as $item) {
            $valorIngrediente = $item->ingrediente->valor_compra ?? 0;
            $valorCompra += ((float)$item->quantidade * (float)$valorIngrediente);
        }

        $produto->valor_compra = $valorCompra;
        $produto->save();
    }

    public function show($id)
    {
        $item = Produto::findOrFail($id);
        $data = $this->getComposicaoRecursiva($id);

        return view('produtos.composto.composto', compact('item', 'data'));
    }

    private function getComposicaoRecursiva($produtoId, $nivel = 0)
    {
        $composicoes = ProdutoComposicao::with('ingrediente.composicao')
        ->where('produto_id', $produtoId)
        ->get();

        $resultado = collect();

        foreach ($composicoes as $composicao) {
            $composicao->nivel = $nivel;
            $resultado->push($composicao);

            if ($composicao->ingrediente && $composicao->ingrediente->composicao->count() > 0) {
                $subItens = $this->getComposicaoRecursiva($composicao->ingrediente_id, $nivel + 1);
                $resultado = $resultado->merge($subItens);
            }
        }

        return $resultado;
    }

    public function print($id)
    {
        $item = Produto::findOrFail($id);
        $data = $this->getComposicaoRecursiva($id);

        $html = view('produtos.composto.print', compact('item', 'data'))->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="composicao_produto_'.$item->nome.'.pdf"');
    }

    public function composicaoCompleta($id)
    {
        $produto = Produto::findOrFail($id);

        $lista = [];
        $this->montarComposicaoRecursiva($produto->id, $lista, 0);

        return view('produtos.partials.modal_composicao_completa', compact('produto', 'lista'));
    }

    private function montarComposicaoRecursiva($produtoId, &$lista, $nivel = 0)
    {
        $itens = ProdutoComposicao::with(['ingrediente.categoria'])
        ->where('produto_id', $produtoId)
        ->get();

        foreach ($itens as $item) {
            $lista[] = [
                'id' => $item->id,
                'nivel' => $nivel,
                'codigo' => $item->ingrediente->codigo ?? $item->ingrediente->id,
                'nome' => $item->ingrediente->nome ?? '--',
                'quantidade' => $item->quantidade,
                'unidade' => $item->ingrediente->unidade ?? 'UN',
                'categoria' => $item->ingrediente->categoria->nome ?? '--',
                'valor_compra' => $item->ingrediente->valor_compra ?? 0,
                'total' => ($item->quantidade * ($item->ingrediente->valor_compra ?? 0)),
            ];

            $temFilhos = ProdutoComposicao::where('produto_id', $item->ingrediente_id)->exists();

            if ($temFilhos) {
                $this->montarComposicaoRecursiva($item->ingrediente_id, $lista, $nivel + 1);
            }
        }
    }
}
