@extends('layouts.app', ['title' => 'Ordem de Produção'])
@section('css')
<style type="text/css">
    @page { size: auto;  margin: 0mm; }

    @media print {
        .print{
            margin: 10px;
        }
    }

    .image-card{
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        transition: all .25s ease;
    }

    .image-card:hover{
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(15, 23, 42, 0.12) !important;
    }

    .image-card-wrapper{
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(135deg, #f8fafc, #eef2ff);
    }

    .image-card-img{
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
        transition: transform .35s ease;
    }

    .image-card:hover .image-card-img{
        transform: scale(1.04);
    }

    .image-overlay{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        padding: 12px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        background: linear-gradient(to bottom, rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.05));
    }

    .image-badge{
        display: inline-flex;
        align-items: center;
        background: rgba(255,255,255,0.16);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.18);
        box-shadow: 0 8px 20px rgba(0,0,0,0.10);
    }

    .btn-delete-image{
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 12px;
        background: rgba(220, 38, 38, 0.92);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 10px 22px rgba(220, 38, 38, 0.28);
        transition: all .2s ease;
    }

    .btn-delete-image:hover{
        transform: scale(1.06);
        background: #dc2626;
        color: #fff;
    }

    .empty-images-box{
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px dashed #cbd5e1;
        border-radius: 24px;
        padding: 40px 20px;
        text-align: center;
    }

    .empty-images-icon{
        width: 72px;
        height: 72px;
        margin: 0 auto 14px auto;
        border-radius: 20px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 16px 30px rgba(79, 70, 229, 0.22);
    }


    .op-header{
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 15px;
    }

    .op-badge{
        display: flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #4254BA, #6C63FF);
        color: #fff;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 999px;
        box-shadow: 0 8px 18px rgba(66, 84, 186, 0.25);
    }

    .op-title{
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        letter-spacing: 0.5px;
        position: relative;
    }

    .op-title::after{
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        width: 40px;
        height: 4px;
        border-radius: 10px;
        background: linear-gradient(135deg, #4254BA, #6C63FF);
    }


    .info-card{
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        padding: 14px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        transition: all .25s ease;
        height: 100%;
    }

    .info-card:hover{
        transform: translateY(-3px);
        box-shadow: 0 18px 35px rgba(15, 23, 42, 0.12);
    }

    .info-icon{
        width: 45px;
        height: 45px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        flex-shrink: 0;
    }

    .info-content span{
        display: block;
        font-size: 12px;
        color: #64748B;
        margin-bottom: 2px;
    }

    .info-content strong{
        font-size: 15px;
        color: #1E293B;
        font-weight: 600;
    }

    .obs-card{
        align-items: flex-start;
    }


    .status-pill{
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        margin-left: 8px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .2px;
        border: 1px solid transparent;
    }

    .status-novo{
        background: #F1F5F9;
        color: #475569;
        border-color: #E2E8F0;
    }

    .status-producao{
        background: rgba(66, 84, 186, 0.10);
        color: #4254BA;
        border-color: rgba(66, 84, 186, 0.25);
    }

    .status-expedicao{
        background: rgba(15, 23, 42, 0.08);
        color: #1E293B;
        border-color: rgba(15, 23, 42, 0.12);
    }

    .status-finalizado{
        background: rgba(22, 163, 74, 0.10);
        color: #16A34A;
        border-color: rgba(22, 163, 74, 0.20);
    }


</style>
@endsection
@section('content')

<div class="card mt-1">
    <div class="card-body">
        <div class="pl-lg-4">
            <div class="ms">
                <div class="mt- d-print-none" style="text-align: right;">
                    <a href="{{ route('ordem-producao.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="col-6">
                            <h5 class="m-2 fw-semibold text-dark">
                                Estado:
                                @if($item->estado == 'novo')
                                <span class="status-pill status-novo">Novo</span>
                                @elseif($item->estado == 'producao')
                                <span class="status-pill status-producao">Produção</span>
                                @elseif($item->estado == 'expedicao')
                                <span class="status-pill status-expedicao">Expedição</span>
                                @else
                                <span class="status-pill status-finalizado">Finalizado</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                    <div class="col-6">
                        <!-- <h3 class="text-primary">OP #{{ $item->codigo_sequencial }}</h3> -->

                        <div class="op-header">
                            <div class="op-badge">
                                <i class="ri-stack-line"></i>
                                <span>Ordem de Produção</span>
                            </div>

                            <h3 class="op-title">
                                OP #{{ $item->codigo_sequencial }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">

                    <div class="col-md-3 col-6">
                        <div class="info-card">
                            <div class="info-icon bg-primary">
                                <i class="ri-calendar-line"></i>
                            </div>
                            <div class="info-content">
                                <span>Data cadastro</span>
                                <strong>{{ __data_pt($item->created_at) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="info-card">
                            <div class="info-icon bg-purple">
                                <i class="ri-time-line"></i>
                            </div>
                            <div class="info-content">
                                <span>Entrega prevista</span>
                                <strong>
                                    {{ $item->data_prevista_entrega ? __data_pt($item->data_prevista_entrega, 0) : '--' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="info-card">
                            <div class="info-icon bg-success">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="info-content">
                                <span>Funcionário</span>
                                <strong>{{ $item->funcionario ? $item->funcionario->nome : '--' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="info-card">
                            <div class="info-icon bg-dark">
                                <i class="ri-user-settings-line"></i>
                            </div>
                            <div class="info-content">
                                <span>Usuário</span>
                                <strong>{{ $item->usuario->name }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($item->observacao)
                    <div class="col-12">
                        <div class="info-card obs-card">
                            <div class="info-icon bg-warning">
                                <i class="ri-chat-1-line"></i>
                            </div>
                            <div class="info-content">
                                <span>Observação</span>
                                <strong>{!! $item->observacao !!}</strong>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                <a target="_blank" class="btn btn-dark btn-sm" href="{{ route('ordem-producao.link', $item->hash_link) }}">
                    <i class="ri-links-fill"></i>
                    Link Cliente
                </a>

                <button type="button" class="btn btn-secondary btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal_alterar_estado"><i class="ri-refresh-line"></i>
                    Alterar Estado
                </button>
                <a target="_blank" class="btn btn-primary btn-sm d-print-none" href="{{ route('ordem-producao.imprimir', [$item->id]) }}">
                    <i class="ri-printer-line"></i>
                    Imprimir
                </a>

                <a target="_blank" onclick="printEtiqueta('{{ $item->id }}')" class="btn btn-success btn-sm d-print-none" ><i class="ri-printer-line"></i>
                    Imprimir Etiquetas
                </a>

                <a target="_blank" class="btn btn-info btn-sm d-print-none" href="{{ route('ordem-producao.impressao-tecnica', [$item->id]) }}">
                    <i class="ri-printer-fill"></i>
                    Impressão Técnica
                </a>

                <button class="btn btn-danger btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal_configuracao">
                    <i class="ri-settings-line"></i>
                    Configuração Etiquetas
                </button>

                <a class="btn btn-warning btn-sm d-print-none" href="{{ route('ordem-producao.edit', [$item->id]) }}">
                    <i class="ri-pencil-fill"></i>
                    Editar
                </a>

                <div class="card border-0 shadow-sm mt-2">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="ri-image-2-line text-primary"></i>
                                Imagens da Produção
                            </h5>
                        </div>

                        <form action="{{ route('ordem-producao.upload-imagens', [$item->id]) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                            @csrf

                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <input type="file"
                                    name="imagens[]"
                                    class="form-control"
                                    multiple
                                    accept="image/*"
                                    onchange="previewImagens(event)">
                                </div>

                                <div class="col-md-auto">
                                    <button class="btn btn-primary px-4">
                                        <i class="ri-upload-cloud-2-line"></i>
                                        Enviar imagens
                                    </button>
                                </div>
                            </div>
                        </form>


                        <div class="row g-3 mb-3" id="preview-container"></div>


                        <div class="row g-4">
                            @forelse($item->imagens as $key => $img)
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="card border-0 shadow-sm image-card h-100">
                                    <div class="image-card-wrapper">
                                        <img src="{{ $img->img }}" class="image-card-img" alt="Imagem da ordem">

                                        <div class="image-overlay">
                                            <span class="image-badge">
                                                <i class="ri-image-2-line me-1"></i>Imagem {{ $key + 1 }}
                                            </span>

                                            <form action="{{ route('ordem-producao.remover-imagem', [$img->id]) }}"
                                                id="form-image-{{ $img->id }}"
                                                method="POST"
                                                class="m-0">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-delete-image" onclick="return confirm('Deseja remover esta imagem?')">
                                                    <i class="ri-delete-bin-6-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="empty-images-box">
                                    <div class="empty-images-icon">
                                        <i class="ri-image-line"></i>
                                    </div>
                                    <h5 class="mb-1">Nenhuma imagem cadastrada</h5>
                                    <p class="mb-0 text-muted">Adicione imagens para acompanhar visualmente esta ordem de produção.</p>
                                </div>
                            </div>
                            @endforelse
                        </div>

                    </div>
                </div>

                <div class="row mb-2 mt-4">
                    <div class="alert alert-info">
                        <label><i class="ri-information-line"></i> Itens em verde concluídos</label>
                    </div>

                    <div style="display: flex;align-items: center;gap: 8px;margin-bottom: 10px;">
                        <div style="width: 4px;height: 20px;background: #4254BA;border-radius: 2px;"></div>
                        <h5 style="margin: 0;font-weight: 600;color: #1F2937;">
                            Produção
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th>Nº Pedido</th>
                                    <th>Cliente</th>
                                    <th>Quantidade</th>
                                    <th>Observação</th>
                                    <th class="d-print-none"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->itens as $i)
                                <tr>
                                    <td style="width: 60%" @if($i->status) class="text-success" @endif>
                                        {{ $i->produto->nome }}
                                        @if($i->itemProducao)
                                        {{ $i->itemProducao->dimensao }}
                                        @endif
                                    </td>

                                    @if($i->itemProducao)
                                    <td>{{ $i->itemProducao->itemNfe->nfe->numero_sequencial }}</td>
                                    <td>{{ $i->itemProducao->itemNfe->nfe->cliente->razao_social }}</td>
                                    @else
                                    <td>{{ $i->numero_pedido }}</td>
                                    <td>{{ $i->cliente->razao_social ?? '--' }}</td>
                                    @endif

                                    <td>
                                        @if(!$i->produto->unidadeDecimal())
                                        {{ number_format($i->quantidade, 0) }}
                                        @else
                                        {{ number_format($i->quantidade, 3, ',', '.') }}
                                        @endif
                                    </td>

                                    <td>{{ $i->observacao }}</td>

                                    <td class="d-print-none">
                                        @if(!$i->status)
                                        <a href="{{ route('ordem-producao-status-item', [$i->id]) }}" class="btn btn-sm btn-success">
                                            <i class="ri-checkbox-circle-line"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('ordem-producao-status-item', [$i->id]) }}" class="btn btn-sm btn-danger">
                                            <i class="ri-close-circle-line"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>

                                @if($i->produto && $i->produto->composto)
                                <tr class="bg-light">
                                    <td colspan="6" style="padding: 12px 18px;">
                                        <div class="mt-1">
                                            <span class="badge bg-primary mb-2">
                                                <i class="ri-list-check-2"></i> Composição do produto
                                            </span>

                                            <div class="row">
                                                @foreach($i->produto->composicao as $composicao)
                                                <div class="col-md-4 mb-2">
                                                    <div class="border rounded p-2 bg-white">
                                                        <strong>{{ $composicao->ingrediente->nome ?? 'Produto' }}</strong><br>

                                                        <small class="text-muted">
                                                            Qtd:
                                                            @php
                                                            $qtdComp = $composicao->quantidade * $i->quantidade;
                                                            @endphp

                                                            @if(isset($composicao->ingrediente) && !$composicao->ingrediente->unidadeDecimal())
                                                            {{ number_format($qtdComp, 0) }}
                                                            @else
                                                            {{ number_format($qtdComp, 3, ',', '.') }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endif

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($item->nfe_id == 0)
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('ordem-producao.gerar-venda', $item->id) }}">
                    <i class="ri-file-text-line"></i>
                    Gerar Venda
                </a>
                @else
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('nfe.show', $item->nfe_id) }}">
                    <i class="ri-file-text-line"></i>
                    Ver Venda
                </a>
                @endif

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_alterar_estado" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ route('ordem-producao.update-estado', [$item->id]) }}">
            @csrf
            @method('put')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Alterar Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            {!!Form::select('estado', 'Estado', App\Models\OrdemProducao::estados())
                            ->attrs(['class' => 'form-select'])
                            ->value($item->estado)
                            !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modal_configuracao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" action="{{ route('ordem-producao.config') }}">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Configuração de Etiquetas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            {!!Form::tel('margem_topo', 'Margem topo (px)')
                            ->attrs(['class' => 'percentual'])
                            ->value($config ? $config->margem_topo : '')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::tel('margem_lateral', 'Margem lateral (px)')
                            ->attrs(['class' => 'percentual'])
                            ->value($config ? $config->margem_lateral : '')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::tel('distancia_entre_etiquetas', 'Distância entre etiquetas (px)')
                            ->attrs(['class' => 'percentual'])
                            ->value($config ? $config->distancia_entre_etiquetas : '')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::tel('distancia_entre_linhas', 'Distância entre linhas (px)')
                            ->attrs(['class' => 'percentual'])
                            ->value($config ? $config->distancia_entre_linhas : '')
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::tel('largura_imagem', 'Largura da imagem (px)')
                            ->attrs(['class' => 'percentual'])
                            ->value($config ? $config->largura_imagem : '')
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::tel('altura_imagem', 'Altura da imagem (px)')
                            ->attrs(['class' => 'percentual'])
                            ->value($config ? $config->altura_imagem : '')
                            !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
    function printEtiqueta(id){
        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

        var docprint=window.open(path_url+"ordem-producao-imprimir-etiquetas/"+id, "",disp_setting);

        docprint.focus();
    }
</script>
@endsection