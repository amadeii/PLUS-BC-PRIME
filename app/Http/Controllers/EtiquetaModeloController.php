<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EtiquetaModelo;
use App\Models\Produto;

class EtiquetaModeloController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $data = EtiquetaModelo::where('empresa_id', $this->empresa_id)
        ->when($request->pesquisa, function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->pesquisa%");
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        return view('etiquetas_modelos.index', compact('data'));
    }

    public function create()
    {
        return view('etiquetas_modelos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|max:100',
            'largura' => 'required|numeric|min:10',
            'altura' => 'required|numeric|min:10',
            'etiquetas_por_linha' => 'required|integer|min:1',
            'largura_codigo_barras' => 'nullable|integer|min:1',
            'altura_codigo_barras' => 'nullable|integer|min:1',
        ]);

        EtiquetaModelo::create([
            'empresa_id' => $this->empresa_id,
            'nome' => $request->nome,
            'largura' => __convert_value_bd($request->largura),
            'altura' => __convert_value_bd($request->altura),
            'etiquetas_por_linha' => $request->etiquetas_por_linha,
            'fonte_padrao' => $request->fonte_padrao ?? 10,
            'espaco_horizontal' => $request->espaco_horizontal ?? 2,
            'espaco_vertical' => $request->espaco_vertical ?? 2,

            'mostrar_numero_codigo_barras' => $request->mostrar_numero_codigo_barras ? true : false,
            'largura_codigo_barras' => $request->largura_codigo_barras ?? 38,
            'altura_codigo_barras' => $request->altura_codigo_barras ?? 10,

            'layout_json' => $request->layout_json,
            'ativo' => $request->ativo ? true : false,
        ]);

        session()->flash('flash_success', 'Modelo de etiqueta criado com sucesso!');
        return redirect()->route('etiqueta-modelos.index');
    }

    public function edit($id)
    {
        $item = EtiquetaModelo::where('empresa_id', $this->empresa_id)->findOrFail($id);
        return view('etiquetas_modelos.edit', compact('item'));
    }

    public function editor($id)
    {
        $item = EtiquetaModelo::where('empresa_id', $this->empresa_id)
        ->findOrFail($id);

        return view('etiquetas_modelos.editor', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = EtiquetaModelo::where('empresa_id', $this->empresa_id)->findOrFail($id);

        $request->validate([
            'nome' => 'required|max:100',
            'largura' => 'required|numeric|min:10',
            'altura' => 'required|numeric|min:10',
            'etiquetas_por_linha' => 'required|integer|min:1',
            'largura_codigo_barras' => 'nullable|integer|min:1',
            'altura_codigo_barras' => 'nullable|integer|min:1',
        ]);

        $item->update([
            'nome' => $request->nome,
            'largura' => __convert_value_bd($request->largura),
            'altura' => __convert_value_bd($request->altura),
            'etiquetas_por_linha' => $request->etiquetas_por_linha,
            'fonte_padrao' => $request->fonte_padrao ?? 10,
            'espaco_horizontal' => $request->espaco_horizontal ?? 2,
            'espaco_vertical' => $request->espaco_vertical ?? 2,

            'mostrar_numero_codigo_barras' => $request->mostrar_numero_codigo_barras ? true : false,
            'largura_codigo_barras' => $request->largura_codigo_barras ?? 38,
            'altura_codigo_barras' => $request->altura_codigo_barras ?? 10,

        // 'layout_json' => $request->layout_json,
            'ativo' => $request->ativo ? true : false,
        ]);

        session()->flash('flash_success', 'Modelo de etiqueta atualizado com sucesso!');
        return redirect()->route('etiqueta-modelos.index');
    }

    public function destroy($id)
    {
        $item = EtiquetaModelo::where('empresa_id', $this->empresa_id)->findOrFail($id);
        $item->delete();

        session()->flash('flash_success', 'Modelo removido com sucesso!');
        return redirect()->route('etiqueta-modelos.index');
    }

    public function salvarLayout(Request $request, $id)
    {
        $item = EtiquetaModelo::findOrFail($id);

        $request->validate([
            'layout_json' => 'required'
        ]);

        $item->layout_json = $request->layout_json;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Layout salvo com sucesso!'
        ]);
    }

    public function selecionarProdutos($id)
    {
        $item = EtiquetaModelo::findOrFail($id);

        return view('etiquetas_modelos.selecionar_produtos', compact('item'));
    }

    public function imprimirProdutos(Request $request, $id)
    {
        $item = EtiquetaModelo::findOrFail($id);

        $produtosSelecionados = [];

        foreach(($request->produtos ?? []) as $produtoId => $dados){

            $qtd = (int)($dados['qtd'] ?? 0);

            if($qtd <= 0){
                continue;
            }

            $produto = Produto::find($produtoId);

            if(!$produto){
                continue;
            }

            $produtosSelecionados[] = [
                'produto' => $produto,
                'qtd' => $qtd
            ];
        }

        if(sizeof($produtosSelecionados) == 0){
            session()->flash('flash_error', 'Informe a quantidade de pelo menos um produto.');
            return redirect()->back();
        }

        $generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();

        $produtosSelecionados = collect($produtosSelecionados)->map(function($linha) use ($generatorPNG){

            $produto = $linha['produto'];
            $codigoBarras = preg_replace('/\D/', '', $produto->codigo_barras ?? '');

            $produto->barcode_base64 = null;

            if($codigoBarras){
                try{
                    if(strlen($codigoBarras) == 13){
                        $barCode = $generatorPNG->getBarcode($codigoBarras, $generatorPNG::TYPE_EAN_13);
                    }else{
                        $barCode = $generatorPNG->getBarcode($codigoBarras, $generatorPNG::TYPE_CODE_128);
                    }

                    $produto->barcode_base64 = 'data:image/png;base64,' . base64_encode($barCode);

                }catch(\Exception $e){
                    $produto->barcode_base64 = null;
                }
            }

            $linha['produto'] = $produto;
            return $linha;
        });

        $layout = json_decode($item->layout_json ?? '[]', true);

        return view('etiquetas_modelos.imprimir_produtos', compact(
            'item',
            'produtosSelecionados',
            'layout'
        ));
    }
}