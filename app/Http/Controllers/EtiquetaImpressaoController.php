<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EtiquetaModelo;
use App\Models\Produto;

class EtiquetaImpressaoController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            return $next($request);
        });
    }

    public function index()
    {
        $data = EtiquetaModelo::where('empresa_id', $this->empresa_id)
        ->where('ativo', true)
        ->orderBy('nome')
        ->paginate(__itensPagina());

        return view('etiquetas_impressao.index', compact('data'));
    }

    public function imprimir(Request $request)
    {
        $request->validate([
            'modelo_id' => 'required',
            'produtos' => 'required|array',
        ]);

        $modelo = EtiquetaModelo::where('empresa_id', $this->empresa_id)
        ->findOrFail($request->modelo_id);

        $itens = [];

        foreach ($request->produtos as $produtoId => $quantidade) {
            $quantidade = (int) $quantidade;

            if ($quantidade <= 0) {
                continue;
            }

            $produto = Produto::where('empresa_id', $this->empresa_id)
            ->find($produtoId);

            if ($produto) {
                for ($i = 0; $i < $quantidade; $i++) {
                    $itens[] = $produto;
                }
            }
        }

        if (sizeof($itens) == 0) {
            session()->flash('flash_error', 'Informe ao menos um produto com quantidade.');
            return redirect()->back();
        }

        return view('etiquetas_impressao.imprimir', compact('modelo', 'itens'));
    }
}