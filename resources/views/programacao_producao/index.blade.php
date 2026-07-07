@extends('layouts.app', ['title' => 'Programação de Produção'])

@section('css')

<style>
    .pp-wrapper{
        padding-top: 5px;
    }

    .pp-card{
        border-radius: 8px;
        border: 1px solid rgba(120,120,120,.12);
        overflow: hidden;
    }

    .pp-header{
        padding: 18px 20px;
        border-bottom: 1px solid rgba(120,120,120,.12);
    }

    .pp-title-wrap{
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pp-icon{
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(132,72,220,.12);
        color: #8448dc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    .pp-title{
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
    }

    .pp-subtitle{
        font-size: 13px;
        opacity: .7;
        margin-top: 4px;
    }

    .pp-filter{
        padding: 16px 20px;
        border-bottom: 1px solid rgba(120,120,120,.12);
    }

    .pp-filter .form-control,
    .pp-filter .form-select{
        height: 40px;
        border-radius: 10px;
    }

    .pp-filter label{
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .pp-filter .btn{
        height: 40px;
        border-radius: 10px;
        font-weight: 600;
    }

    .pp-content{
        padding: 18px 20px 20px 20px;
    }

    .pp-summary{
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 18px;
    }

    .pp-summary-card{
        border-radius: 16px;
        border: 1px solid rgba(120,120,120,.12);
        padding: 16px;
    }

    .pp-summary-title{
        font-size: 12px;
        opacity: .7;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .pp-summary-value{
        font-size: 30px;
        font-weight: 800;
        line-height: 1;
    }

    @media(max-width: 992px){
        .pp-summary{
            grid-template-columns: repeat(2,1fr);
        }
    }

    @media(max-width: 576px){

        .pp-wrapper{
            padding: 8px;
        }

        .pp-summary{
            grid-template-columns: 1fr;
        }

        .pp-title{
            font-size: 20px;
        }

        .pp-header{
            padding: 15px;
        }

        .pp-filter{
            padding: 15px;
        }

        .pp-content{
            padding: 15px;
        }
    }
</style>
@endsection
@section('content')

<div class="pp-wrapper">

    <div class="card pp-card shadow-sm">

        {{-- HEADER --}}
        <div class="pp-header">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

                <div class="pp-title-wrap">

                    <div class="pp-icon">
                        <i class="ri-settings-3-fill"></i>
                    </div>

                    <div>

                        <h3 class="pp-title">
                            Programação de Produção
                        </h3>

                        <div class="pp-subtitle">
                            Central de planejamento, materiais e ordens de fabricação
                        </div>

                    </div>

                </div>

                <form method="GET" action="{{ route('programacao-producao.index') }}">

                    @foreach(request()->except(['recalcular', 'incluir_semi']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary px-4 py-2 rounded-3 fw-semibold" name="recalcular" value="1">
                            <i class="ri-refresh-line me-1"></i>
                            Atualizar Programação
                        </button>

                        <!-- <button type="button" class="btn btn-success px-5 py-2 rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalGerarOf">
                            <i class="ri-hammer-fill me-1"></i>
                            Gerar OF
                        </button> -->
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                        <!-- <div class="form-check mb-0">
                            <input type="hidden" name="incluir_semi" value="0">

                            <input class="form-check-input" type="checkbox" name="incluir_semi" value="1" id="incluirSemiHeader" {{ $incluirSemi ? 'checked' : '' }}>

                            <label class="form-check-label small fw-semibold" for="incluirSemiHeader">
                                Incluir OFs dos semi-elaborados necessários
                            </label>
                        </div> -->

                        <small class="text-muted">
                            Último recálculo:
                            <strong>{{ $ultimoRecalculo ? $ultimoRecalculo->format('d/m/Y H:i') : '--' }}</strong>
                        </small>
                    </div>

                </form>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="pp-filter">

            {!! Form::open()->fill(request()->all())->get() !!}

            <div class="row g-2 align-items-end">

                <div class="col-lg-3 col-md-6">
                    {!! Form::text('produto', 'Buscar produto') !!}
                </div>

                <div class="col-lg-3 col-md-6">
                    {!! Form::text('descricao', 'Buscar descrição') !!}
                </div>

                <div class="col-lg-2 col-md-6">
                    {!! Form::select('tipo', 'Tipo', [
                    '' => 'Todos os tipos',
                    'acabado' => 'Acabado',
                    'semi' => 'Semi-elaborado'
                    ]) !!}
                </div>

                <div class="col-lg-4 col-md-6">

                    <button class="btn btn-primary">
                        <i class="ri-search-line me-1"></i>
                        Buscar
                    </button>

                    <a href="{{ route('programacao-producao.index') }}"
                    class="btn btn-danger">

                    <i class="ri-eraser-fill me-1"></i>
                    Limpar

                </a>

            </div>

        </div>

        {!! Form::close() !!}

    </div>

    {{-- CONTEÚDO --}}
    <div class="pp-content">

        {{-- RESUMO --}}
        <div class="pp-summary">

            <div class="pp-summary-card">
                <div class="pp-summary-title">
                    Produtos programados
                </div>

                <div class="pp-summary-value">
                    {{ count($produtos) }}
                </div>
            </div>

            <div class="pp-summary-card">
                <div class="pp-summary-title">
                    Pedidos pendentes
                </div>

                <div class="pp-summary-value">
                    {{ collect($pedidos)->where('status_producao', 'Pendente')->count() }}
                </div>
            </div>

            <div class="pp-summary-card">
                <div class="pp-summary-title">
                    Materiais críticos
                </div>

                <div class="pp-summary-value">
                    {{ collect($materiais)->where('situacao', 'FALTA')->count() }}
                </div>
            </div>

            <div class="pp-summary-card">
                <div class="pp-summary-title">
                    OPs abertas
                </div>

                <div class="pp-summary-value">
                    {{ count($ordens) }}
                </div>
            </div>

        </div>

        @include('programacao_producao.partials.pedidos')

        @include('programacao_producao.partials.produtos')

        @include('programacao_producao.partials.ordens')

        @include('programacao_producao.partials.materiais')

    </div>

</div>

</div>

@include('programacao_producao.partials.modal_gerar_of')

@endsection
@section('js')
<script type="text/javascript">
    $(document).on('click', '.btn-gerar-of', function () {
        let html = '';
        let pedidoId = $(this).data('pedido-id');
        let pedido = window.pedidosProgramacao[pedidoId];

        if (!pedido) {
            $('#modalGerarOfProdutos').html(`
                <tr>
                <td colspan="4" class="text-center text-muted py-4">
                Pedido não encontrado
                </td>
                </tr>
                `);
            return;
        }

        pedido.itens.forEach(function (item, index) {
            let key = pedidoId + '_' + index;
            let quantidade = parseFloat(item.quantidade || 0);

            html += `
            <tr>
            <td>
            <input type="hidden" name="produtos[${key}][selecionado]" value="1">
            <input type="hidden" name="produtos[${key}][pedido_id]" value="${pedidoId}">
            <input type="hidden" name="produtos[${key}][produto_id]" value="${item.produto_id ?? ''}">
            <input type="hidden" name="produtos[${key}][produto]" value="${item.produto}">
            <input type="hidden" name="produtos[${key}][cliente_id]" value="${pedido.cliente_id ?? ''}">
            <input type="hidden" name="produtos[${key}][numero_pedido]" value="${pedido.pedido ?? ''}">

            <span class="badge bg-success rounded-pill">
            Selecionado
            </span>
            </td>

            <td>
            <div class="fw-semibold">${item.produto}</div>
            <small class="text-muted">Pedido #${pedido.pedido}</small>
            </td>

            <td>
            <span class="badge bg-light text-dark border">
            ${item.categoria ?? '-'}
            </span>
            </td>

            <td>
            <input type="tel" min="0" class="form-control form-control-sm" name="produtos[${key}][quantidade]" value="${quantidade}">
            </td>
            </tr>
            `;
        });

        $('#modalGerarOfProdutos').html(html);
    });
</script>
<script>
    window.pedidosProgramacao = @json(collect($pedidos)->mapWithKeys(function($p){
        return [$p['id'] => [
        'pedido' => $p['pedido'],
        'cliente_id' => $p['cliente_id'],
        'itens' => $p['itens']
        ]];
    }));
</script>
@endsection