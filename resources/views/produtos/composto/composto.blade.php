@extends('layouts.app', ['title' => 'Composição do Produto'])

@section('css')
<style type="text/css">
    .page-composicao .card {
        border: 0;

        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .page-composicao .main-card-header {


        padding: 18px 22px;
    }

    .page-composicao .main-card-header h4 {
        margin: 0;
        font-weight: 700;
    }

    .page-composicao .main-card-header small {
        opacity: .9;
        font-size: 13px;
    }

    .page-composicao .card-body {
        background: #ffffff;
        border-radius: 0 0 16px 16px;
        padding: 24px;
    }

    .page-composicao .btn-primary {
        background-color: #4254BA;
        border-color: #4254BA;
    }

    .page-composicao .btn-primary:hover {
        background-color: #3345a3;
        border-color: #3345a3;
    }

    .page-composicao .btn-dark {
        background-color: #1f2937;
        border-color: #1f2937;
    }

    .page-composicao .btn-danger,
    .page-composicao .btn-success,
    .page-composicao .btn-dark,
    .page-composicao .btn-primary {
        border-radius: 10px;
        font-weight: 600;
    }

    .page-composicao .resumo-card {
        background: linear-gradient(135deg, #4254BA 0%, #2f3ea1 100%);
        color: #fff;
        border-radius: 18px;
        padding: 22px 24px;
        box-shadow: 0 10px 24px rgba(66, 84, 186, 0.25);
        height: 100%;
    }

    .page-composicao .resumo-card .label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .08em;
        opacity: .85;
        margin-bottom: 8px;
    }

    .page-composicao .resumo-card .produto-nome {
        font-size: 24px;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 16px;
    }

    .page-composicao .resumo-card .valor-custo {
        font-size: 34px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 6px;
    }

    .page-composicao .resumo-card .sub-info {
        font-size: 13px;
        opacity: .9;
    }

    .page-composicao .form-card {
        background: #f8faff;
        border: 1px solid #e7ecff;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 22px;
    }

    .page-composicao .form-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 14px;
    }

    

    .page-composicao .ingrediente-nome {
        font-weight: 700;
        color: #111827;
    }

    .page-composicao .ingrediente-sub {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    .page-composicao .badge-qtd {
        background: #4254BA;
        color: #fff;
        /*font-size: 13px;*/
        font-weight: 700;
        padding: 5px 10px;
        /*border-radius: 999px;*/
        display: inline-block;
        /*min-width: 80px;*/
        text-align: center;
    }

    .page-composicao .badge-nivel {
        background: #eef2ff;
        color: #4254BA;
        font-size: 11px;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 10px;
        margin-left: 8px;
    }

    .page-composicao .empty-state {
        padding: 30px 20px;
        text-align: center;
        color: #6b7280;
    }

    .page-composicao .acoes-topo {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .page-composicao .footer-actions {
        border-top: 1px solid #edf0f7;
        margin-top: 22px;
        padding-top: 20px;
        text-align: right;
    }

    @media (max-width: 768px) {
        .page-composicao .main-card-header {
            padding-bottom: 14px;
        }

        .page-composicao .acoes-topo {
            justify-content: flex-start;
            margin-top: 14px;
        }

        .page-composicao .resumo-card .produto-nome {
            font-size: 20px;
        }

        .page-composicao .resumo-card .valor-custo {
            font-size: 28px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-composicao">
    <div class="card mt-1">
        <div class="main-card-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h4>Composição do Produto</h4>
                    <small>Gerencie os insumos e ingredientes vinculados ao produto</small>
                </div>
                <div class="col-md-5">
                    <div class="acoes-topo">
                        <a href="{{ route('produto-composto.print', $item->id) }}" target="_blank" class="btn btn-dark btn-sm px-3">
                            <i class="ri-printer-line"></i> Imprimir
                        </a>
                        <a href="{{ route('produtos.index') }}" class="btn btn-danger btn-sm px-3">
                            <i class="ri-arrow-left-double-fill"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-4">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="resumo-card">
                        <div class="label">Produto</div>
                        <div class="produto-nome">{{ $item->nome }}</div>

                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="label">Valor de custo atual</div>
                                <div class="valor-custo">
                                    R$ {{ __moeda($item->valor_compra ?? 0) }}
                                </div>
                                <div class="sub-info">
                                    Esse valor é recalculado com base nos ingredientes da composição.
                                </div>
                            </div>

                            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                <i class="ri-money-dollar-circle-line" style="font-size: 64px; opacity: .25;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-card h-100 d-flex flex-column justify-content-center">
                        <div class="form-card-title">
                            <i class="ri-stack-line"></i> Resumo rápido
                        </div>
                        <div class="mb-2">
                            <strong>Total de itens:</strong> {{ count($data) }}
                        </div>
                        <div class="mb-2">
                            <strong>Produto:</strong> {{ $item->nome }}
                        </div>
                        <div>
                            <strong>Custo atual:</strong> R$ {{ __moeda($item->valor_compra ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-card-title">
                    <i class="ri-add-circle-line"></i> Adicionar produto à composição
                </div>

                {!!Form::open()
                ->post()
                ->route('produto-composto.store', [$item->id])
                !!}

                <input type="hidden" name="produto_id" value="{{$item->id}}">

                <div class="row align-items-end">
                    <div class="col-md-6">
                        {!!Form::select('ingrediente_id', 'Selecionar Produto')
                        ->attrs(['class' => 'select2 form-control'])
                        ->required()
                        !!}
                    </div>

                    <div class="col-md-3">
                        {!!Form::text('quantidade', 'Quantidade')
                        ->attrs(['class' => 'qtd form-control'])
                        ->required()
                        !!}
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-add-line"></i> Adicionar Item
                        </button>
                    </div>
                </div>

                {!!Form::close()!!}
            </div>

            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto insumo / ingrediente</th>
                                <th width="140">Quantidade</th>
                                <th width="150" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $comp)
                            <tr>
                                <td>
                                    <div style="padding-left: {{ $comp->nivel * 25 }}px;">
                                        @if($comp->nivel > 0)
                                        <span class="text-muted me-1">↳</span>
                                        @endif

                                        <span class="ingrediente-nome">{{ $comp->ingrediente->nome }}</span>

                                        @if($comp->nivel > 0)
                                        <span class="badge-nivel">Subcomposição</span>
                                        @endif

                                        <div class="ingrediente-sub">
                                            Item vinculado à composição do produto
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-qtd">
                                        <!-- @if(!$comp->produto->unidadeDecimal())
                                        {{ number_format($comp->quantidade, 0, '.', '') }}
                                        @else
                                        {{ number_format($comp->quantidade, 3, '.', '') }}
                                        @endif -->

                                        {{ number_format($comp->quantidade, 3, '.', '') }}

                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($comp->nivel == 0)
                                    <form action="{{ route('produto-composto.destroy', $comp->id) }}" method="post" id="form-delete-{{$comp->id}}">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" title="Remover item" class="btn btn-danger btn-sm btn-delete">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-muted">--</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <i class="ri-inbox-2-line" style="font-size: 34px;"></i>
                                        <div class="mt-2">Nenhum ingrediente adicionado.</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer-actions">
                <a href="{{ route('produtos.index') }}" class="btn btn-success px-5">
                    <i class="ri-check-line"></i> Finalizar Produto
                </a>
            </div>

        </div>
    </div>
</div>
@endsection