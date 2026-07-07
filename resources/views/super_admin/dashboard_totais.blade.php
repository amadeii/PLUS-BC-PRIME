@extends('layouts.app', ['title' => 'Dashboard SaaS'])

@section('css')
<style type="text/css">
    .card-dashboard{
        border-radius:22px;
        background:#fff;
        transition:.2s;
    }

    .card-dashboard:hover{
        transform:translateY(-3px);
    }

    .card-line{
        position:absolute;
        left:0;
        top:18px;
        bottom:18px;
        width:4px;
        border-radius:0 10px 10px 0;
    }

    .icon-dashboard{
        width:58px;
        height:58px;
        border-radius:18px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:28px;
        flex-shrink:0;
    }

    .badge-dashboard{
        background:#F3F4F6;
        font-size:12px;
        font-weight:600;
    }

    .bg-purple{
        background:#F3F0FF;
        color:#fff;
    }

    .bg-blue{
        background:#EEF4FF;
        color:#fff;
    }

    .bg-green{
        background:#ECFDF3;
        color:#fff;
    }

    .bg-orange{
        background:#FFF7ED;
        color:#fff;
    }

    .bg-red{
        background:#FEF2F2;
        color:#fff;
    }

    .bg-pink{
        background:#FDF2F8;
        color:#fff;
    }

    .line-purple{ background:#6D5FFC; }
    .line-blue{ background:#2563EB; }
    .line-green{ background:#16A34A; }
    .line-orange{ background:#EA580C; }
    .line-red{ background:#DC2626; }
    .line-pink{ background:#DB2777; }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div id="loaderDashboard" style="display:none;">
                    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background:rgba(0,0,0,.35); z-index:9999;">
                        <div class="bg-white rounded p-4 text-center shadow">
                            <div class="spinner-border text-primary"></div>
                            <div class="mt-2 text-muted">Carregando dados...</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-1">
                            <i class="ri-dashboard-3-line text-primary"></i>
                            Dashboard SaaS
                        </h4>
                        <small class="text-muted">Acompanhe os totais de emissões, vendas e movimentações</small>
                    </div>

                    <a href="{{ route('superadmin.dashboard-detalhado') }}" class="btn btn-primary">
                        <i class="ri-building-line"></i>
                        Ver por Empresa
                    </a>
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    <div class="row mt-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">Período rápido</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm btn-filtro active" data-tipo="dia">Hoje</button>
                                <button type="button" class="btn btn-light btn-sm btn-filtro" data-tipo="semana">Semana</button>
                                <button type="button" class="btn btn-light btn-sm btn-filtro" data-tipo="mes">Mês</button>
                                <button type="button" class="btn btn-light btn-sm btn-filtro" data-tipo="ano">Ano</button>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Data inicial</label>
                            <input type="date" id="data_inicial" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Data final</label>
                            <input type="date" id="data_final" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="btnPersonalizado">
                                <i class="ri-search-line"></i>
                                Filtrar
                            </button>
                        </div>

                    </div>

                    <div id="periodo" class="mt-3 text-muted small"></div>
                </div>

                <div class="row mt-3" id="cards"></div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    let tipoAtual = 'dia';

    function getIcon(titulo){
        switch(titulo){
            case 'Emissões NFe': return 'ri-file-text-line';
            case 'Emissões NFCe': return 'ri-shopping-cart-line';
            case 'Emissões CTe': return 'ri-truck-line';
            case 'Emissões MDFe': return 'ri-road-map-line';
            case 'Emissões NFSe': return 'ri-file-list-3-line';
            case 'Vendas PDV': return 'ri-computer-line';
            case 'Vendas Pedido': return 'ri-archive-line';
            case 'Compras': return 'ri-shopping-bag-3-line';
            case 'Ordens de Serviço': return 'ri-tools-line';
            default: return 'ri-bar-chart-box-line';
        }
    }

    function carregarTotais(){
        $('#loaderDashboard').fadeIn(150);

        let data = { tipo: tipoAtual };

        if(tipoAtual === 'personalizado'){
            data.data_inicial = $('#data_inicial').val();
            data.data_final = $('#data_final').val();
        }

        $.get("{{ route('superadmin.dashboard-totais.ajax') }}", data, function(res){

            $('#periodo').html(`<i class="ri-calendar-line"></i> Período: <strong>${res.periodo.data_inicial}</strong> até <strong>${res.periodo.data_final}</strong>`);

            let html = '';

            res.cards.forEach(c => {
                html += `
                <div class="col-md-4 mb-3">

                <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative card-dashboard">

                <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                <div>

                <div class="text-uppercase fw-semibold text-muted mb-2" style="font-size:11px; letter-spacing:.5px;">
                ${c.titulo}
                </div>

                <h2 class="fw-bold mb-1 text-dark">
                ${c.total}
                </h2>

                <div class="d-inline-flex align-items-center px-2 py-1 rounded-pill badge-dashboard">
                <i class="ri-money-dollar-circle-line me-1"></i>
                ${c.valor}
                </div>

                </div>

                <div class="icon-dashboard ${getCardColor(c.titulo)}">
                <i class="${getIcon(c.titulo)}"></i>
                </div>

                </div>

                </div>

                <div class="card-line ${getCardColor(c.titulo)}"></div>

                </div>

                </div>
                `;
            });

            $('#cards').html(html);

        }).always(function(){
            $('#loaderDashboard').fadeOut(150);
        });
    }

    $(document).ready(function(){

        carregarTotais();

        $('.btn-filtro').click(function(){
            $('.btn-filtro').removeClass('btn-primary active').addClass('btn-light');
            $(this).removeClass('btn-light').addClass('btn-primary active');

            tipoAtual = $(this).data('tipo');
            carregarTotais();
        });

        $('#btnPersonalizado').click(function(){
            tipoAtual = 'personalizado';
            $('.btn-filtro').removeClass('btn-primary active').addClass('btn-light');
            carregarTotais();
        });

    });

    function getCardColor(titulo){

        switch(titulo){

            case 'Emissões NFe':
            return 'bg-purple line-purple';

            case 'Emissões NFCe':
            return 'bg-blue line-blue';

            case 'Emissões CTe':
            return 'bg-green line-green';

            case 'Emissões MDFe':
            return 'bg-orange line-orange';

            case 'Emissões NFSe':
            return 'bg-pink line-pink';

            case 'Vendas PDV':
            return 'bg-green line-green';

            case 'Vendas Pedido':
            return 'bg-blue line-blue';

            case 'Compras':
            return 'bg-red line-red';

            default:
            return 'bg-purple line-purple';
        }
    }
</script>
@endsection