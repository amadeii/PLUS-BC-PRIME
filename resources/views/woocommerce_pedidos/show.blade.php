@extends('layouts.app', ['title' => 'Pedido Woocommerce #'.$item->pedido_id])

@section('css')
<style type="text/css">
    @page { size: auto; margin: 0mm; }

    @media print {
        .print {
            margin: 10px;
        }

        .d-print-none {
            display: none !important;
        }

        .card,
        .pedido-card,
        .box-info,
        .box-section,
        .table-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        body {
            background: #fff !important;
        }
    }

    .pedido-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .pedido-topo {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 22px 24px;
        border-radius: 18px 18px 0 0;
    }

    .pedido-topo .pedido-numero {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .pedido-topo .pedido-subtitulo {
        color: rgba(255,255,255,0.82);
        font-size: 14px;
        margin: 0;
    }

    .topo-acoes {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-topo {
        border-radius: 10px;
        padding: 8px 14px;
        font-weight: 600;
    }

    .resumo-grid {
        margin-top: 20px;
    }

    .box-info {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        height: 100%;
    }

    .box-info .titulo {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .box-info .valor {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        color: #0f172a;
    }

    .box-info .valor.success {
        color: #16a34a;
    }

    .box-info .valor.danger {
        color: #dc2626;
    }

    .box-section {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        margin-top: 18px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .table-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    }

    .table-modern {
        margin-bottom: 0;
    }

    .table-modern thead th {
        background: #0f172a;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        border: 0;
        padding: 14px 16px;
        white-space: nowrap;
    }

    .table-modern tbody td {
        vertical-align: middle;
        padding: 14px 16px;
        border-color: #eef2f7;
    }

    .table-modern tbody tr:hover {
        background: #f8fafc;
    }

    .produto-nome {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .produto-sub {
        font-size: 12px;
        color: #64748b;
    }

    .badge-sem-produto {
        display: inline-block;
        margin-top: 6px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .cliente-nome {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .cliente-doc {
        color: #475569;
        font-size: 15px;
    }

    .campo-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 2px;
    }

    .campo-valor {
        font-size: 15px;
        color: #0f172a;
        font-weight: 600;
    }

    .observacao-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 12px;
        color: #334155;
    }

    .vazio {
        color: #94a3b8;
        font-style: italic;
    }

    .status-nfe {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-nfe.pendente {
        background: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
    }

    .status-nfe.emitida {
        background: #ecfdf5;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    @media (max-width: 768px) {
        .pedido-topo {
            padding: 18px;
        }

        .pedido-topo .pedido-numero {
            font-size: 24px;
        }

        .topo-acoes {
            justify-content: flex-start;
            margin-top: 14px;
        }

        .box-info .valor {
            font-size: 18px;
        }
    }
</style>
@endsection

@section('content')

<div class="card pedido-card mt-1 print">
    <div class="card-body p-0">

        <div class="pedido-topo">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="pedido-numero">Pedido #{{ $item->pedido_id }}</div>
                    <p class="pedido-subtitulo mb-0">
                        Data do pedido: <strong>{{ __data_pt($item->data) }}</strong>
                        &nbsp;|&nbsp;
                        Cadastrado no sistema: <strong>{{ __data_pt($item->created_at) }}</strong>
                    </p>
                </div>

                <div class="col-lg-5">
                    <div class="topo-acoes d-print-none">
                        <a href="{{ route('woocommerce-pedidos.index') }}" class="btn btn-danger btn-sm btn-topo">
                            <i class="ri-arrow-left-double-fill"></i> Voltar
                        </a>

                        <a href="javascript:window.print()" class="btn btn-dark btn-sm btn-topo">
                            <i class="ri-printer-line"></i> Imprimir
                        </a>

                        @if($item->nfe_id == 0)
                        <a href="{{ route('woocommerce-pedidos.gerar-nfe', $item->id) }}" class="btn btn-success btn-sm btn-topo">
                            <i class="ri-file-text-line"></i> Gerar NFe
                        </a>
                        @else
                        <a href="{{ route('nfe.show', $item->nfe_id) }}" class="btn btn-success btn-sm btn-topo">
                            <i class="ri-file-text-line"></i> Ver NFe
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 p-lg-4">

            <div class="row resumo-grid g-3">
                <div class="col-md-3 col-6">
                    <div class="box-info">
                        <div class="titulo">Valor total</div>
                        <div class="valor success">R$ {{ __moeda($item->total) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="box-info">
                        <div class="titulo">Valor entrega</div>
                        <div class="valor">R$ {{ __moeda($item->valor_frete) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="box-info">
                        <div class="titulo">Desconto</div>
                        <div class="valor danger">R$ {{ __moeda($item->desconto) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="box-info">
                        <div class="titulo">Status NFe</div>
                        <div class="mt-1">
                            @if($item->nfe_id == 0)
                            <span class="status-nfe pendente">
                                <i class="ri-time-line"></i> Pendente
                            </span>
                            @else
                            <span class="status-nfe emitida">
                                <i class="ri-checkbox-circle-line"></i> Emitida
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-section mt-4">
                <div class="section-title">
                    <span>Itens do pedido</span>
                </div>

                <div class="table-card">
                    <div class="table-responsive-sm">
                        <table class="table table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-end">Valor unitário</th>
                                    <th class="text-end">Sub total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->itens as $i)
                                <tr>
                                    <td>
                                        <div class="produto-nome">
                                            {{ $i->produto ? $i->produto->nome : $i->item_nome }}
                                        </div>

                                        <div class="produto-sub">
                                            Item ID: {{ $i->item_id ?? '--' }}
                                        </div>

                                        @if(!$i->produto)
                                            <span class="badge-sem-produto">Produto não vinculado no sistema</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{ number_format($i->quantidade, 0) }}
                                    </td>

                                    <td class="text-end">
                                        R$ {{ __moeda($i->valor_unitario) }}
                                    </td>

                                    <td class="text-end">
                                        <strong>R$ {{ __moeda($i->sub_total) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <div class="box-section">
                        <div class="section-title">
                            <span>Cliente</span>

                            @if($item->cliente)
                            <a href="{{ route('clientes.edit', [$item->cliente->id]) }}" class="btn btn-warning btn-sm d-print-none">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            @endif
                        </div>

                        @if($item->cliente)
                            <div class="cliente-nome">{{ $item->cliente->razao_social }}</div>
                            <div class="cliente-doc">{{ $item->cliente->cpf_cnpj }}</div>
                        @else
                            <div class="mb-2 vazio">Nenhum cliente atribuído ao pedido.</div>
                            <button class="btn btn-dark btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                                Atribuir Cliente
                            </button>
                        @endif

                        <div class="mt-3">
                            <div class="campo-label">Observação</div>
                            <div class="observacao-box">
                                @if($item->observacao)
                                    {{ $item->observacao }}
                                @else
                                    <span class="vazio">Sem observações.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box-section">
                        <div class="section-title">
                            <span>Dados de entrega</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="campo-label">Rua</div>
                                <div class="campo-valor">{{ $item->rua ?: '--' }}</div>
                            </div>

                            <div class="col-md-6">
                                <div class="campo-label">Número</div>
                                <div class="campo-valor">{{ $item->numero ?: '--' }}</div>
                            </div>

                            <div class="col-md-6">
                                <div class="campo-label">Bairro</div>
                                <div class="campo-valor">{{ $item->bairro ?: '--' }}</div>
                            </div>

                            <div class="col-md-6">
                                <div class="campo-label">Cidade</div>
                                <div class="campo-valor">{{ $item->cidade ?: '--' }}</div>
                            </div>

                            <div class="col-md-6">
                                <div class="campo-label">CEP</div>
                                <div class="campo-valor">{{ $item->cep ?: '--' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-cliente" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('woocommerce-pedidos.set-cliente', [$item->id]) }}">
                @csrf
                @method('put')

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Atribuir cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!!Form::select('cliente_id', 'Cliente')
                            ->required()
                            !!}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Atribuir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    $("#inp-cliente_id").select2({
        minimumInputLength: 2,
        language: "pt-BR",
        placeholder: "Digite para buscar o cliente",
        theme: "bootstrap4",
        dropdownParent: $('#modal-cliente'),
        ajax: {
            cache: true,
            url: path_url + "api/clientes/pesquisa",
            dataType: "json",
            data: function (params) {
                var query = {
                    pesquisa: params.term,
                    empresa_id: $("#empresa_id").val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];

                $.each(response, function (i, v) {
                    results.push({
                        id: v.id,
                        text: v.razao_social + " - " + v.cpf_cnpj,
                        value: v.id
                    });
                });

                return {
                    results: results,
                };
            },
        },
    });
</script>
@endsection