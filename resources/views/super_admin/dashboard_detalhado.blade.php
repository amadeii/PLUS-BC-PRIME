@extends('layouts.app', ['title' => 'Dashboard por Empresa'])

@section('css')
<style>
    .card-jidox{
        border-radius:18px;
        overflow:hidden;
        position:relative;
        transition:.2s;
    }

    .card-jidox:hover{
        transform:translateY(-2px);
    }

    .card-jidox:before{
        content:'';
        position:absolute;
        left:0;
        top:14px;
        bottom:14px;
        width:4px;
        border-radius:0 8px 8px 0;
        background:#4254BA;
    }

    .icon-jidox{
        width:52px;
        height:52px;
        border-radius:16px;
        background:#F3F0FF;
        color:#4254BA;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:24px;
        flex-shrink:0;
    }

    .badge-jidox{
        background:#F3F0FF;
        color:#4254BA;
        font-weight:600;
        font-size:12px;
        padding:6px 10px;
        border-radius:999px;
    }

    .card-purple:before{ background:#6D5FFC; }
    .card-purple .icon-jidox,
    .card-purple .badge-jidox{
        background:#F3F0FF;
        color:#6D5FFC;
    }

    .card-blue:before{ background:#2563EB; }
    .card-blue .icon-jidox,
    .card-blue .badge-jidox{
        background:#EEF4FF;
        color:#2563EB;
    }

    .card-green:before{ background:#16A34A; }
    .card-green .icon-jidox,
    .card-green .badge-jidox{
        background:#ECFDF3;
        color:#16A34A;
    }

    .card-red:before{ background:#DC2626; }
    .card-red .icon-jidox,
    .card-red .badge-jidox{
        background:#FEF2F2;
        color:#DC2626;
    }

    .card-orange:before{ background:#EA580C; }
    .card-orange .icon-jidox,
    .card-orange .badge-jidox{
        background:#FFF7ED;
        color:#EA580C;
    }

    .card-pink:before{ background:#DB2777; }
    .card-pink .icon-jidox,
    .card-pink .badge-jidox{
        background:#FDF2F8;
        color:#DB2777;
    }

    .card-dark:before{ background:#111827; }
    .card-dark .icon-jidox,
    .card-dark .badge-jidox{
        background:#F3F4F6;
        color:#111827;
    }
</style>
@endsection

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>
                        <h4 class="mb-1">
                            <i class="ri-building-line text-primary"></i>
                            Dashboard por Empresa
                        </h4>

                        <small class="text-muted">
                            Acompanhe vendas, financeiro, plano e cadastro por empresa
                        </small>
                    </div>

                </div>

                <hr class="mt-3">

                <div class="row mt-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">Empresa</label>

                        <select id="inp-empresa" class="form-control">
                        </select>
                    </div>

                    <div class="col-md-3">

                        <label class="form-label">Período rápido</label>

                        <div class="d-flex gap-2 flex-wrap">

                            <button type="button" class="btn btn-primary btn-sm btn-filtro active" data-tipo="dia">
                                Hoje
                            </button>

                            <button type="button" class="btn btn-light btn-sm btn-filtro" data-tipo="semana">
                                Semana
                            </button>

                            <button type="button" class="btn btn-light btn-sm btn-filtro" data-tipo="mes">
                                Mês
                            </button>

                            <button type="button" class="btn btn-light btn-sm btn-filtro" data-tipo="ano">
                                Ano
                            </button>

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

                <div id="loaderDashboard" class="text-center p-4" style="display:none;">
                    <div class="spinner-border text-primary"></div>

                    <div class="mt-2 text-muted">
                        Carregando dados...
                    </div>
                </div>

                <div class="row mt-3" id="cardsDetalhado">

                    <div class="col-md-12">

                        <div class="alert alert-light border text-center mb-0">
                            Selecione uma empresa
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>

    let tipoAtual = 'dia';

    function vazio(msg){

        $('#cardsDetalhado').html(`
            <div class="col-md-12">
            <div class="alert alert-light border text-center mb-0">
            ${msg}
            </div>
            </div>
            `);
    }

    function cardDetalhado(titulo, total, valor, icon, cor){

        return `
        <div class="col-md-4 mb-3">

        <div class="card border-0 shadow-sm h-100 card-jidox card-${cor}">

        <div class="card-body d-flex justify-content-between align-items-start">

        <div>

        <small class="text-muted">
        ${titulo}
        </small>

        <h4 class="fw-bold mb-2 mt-1">
        ${total}
        </h4>

        <span class="badge-jidox">
        ${valor}
        </span>

        </div>

        <div class="icon-jidox">
        <i class="${icon}"></i>
        </div>

        </div>

        </div>

        </div>
        `;
    }

    function carregar(){

        let empresa = $('#inp-empresa').val();

        if(!empresa){
            vazio('Selecione uma empresa');
            return;
        }

        let data = {
            tipo: tipoAtual,
            emp_id: empresa
        };

        if(tipoAtual === 'personalizado'){
            data.data_inicial = $('#data_inicial').val();
            data.data_final = $('#data_final').val();
        }

        $('#loaderDashboard').show();

        $.get("{{ route('superadmin.dashboard-detalhado.ajax') }}", data, function(res){

            $('#periodo').html(`
                <i class="ri-calendar-line"></i>
                Período: <strong>${res.periodo.inicio}</strong> até <strong>${res.periodo.fim}</strong>
                `);

            let html = '';

            res.data.forEach(d => {

                html += cardDetalhado(
                    d.empresa,
                    'Empresa',
                    res.periodo.inicio,
                    'ri-building-line',
                    'primary'
                    );

                html += cardDetalhado(
                    'Pedidos',
                    d.pedido_total,
                    d.pedido_valor,
                    'ri-file-list-3-line',
                    'purple'
                    );

                html += cardDetalhado(
                    'PDV',
                    d.pdv_total,
                    d.pdv_valor,
                    'ri-computer-line',
                    'blue'
                    );

                html += cardDetalhado(
                    'Receber',
                    d.receber_total,
                    d.receber_valor,
                    'ri-money-dollar-circle-line',
                    'green'
                    );

                html += cardDetalhado(
                    'Pagar',
                    d.pagar_total,
                    d.pagar_valor,
                    'ri-bank-card-line',
                    'red'
                    );

                html += cardDetalhado(
                    'Compras',
                    d.compras_total,
                    d.compras_valor,
                    'ri-shopping-bag-3-line',
                    'orange'
                    );

                html += cardDetalhado(
                    'Ordem Serviço',
                    d.os_total,
                    d.os_valor,
                    'ri-tools-line',
                    'pink'
                    );

                html += cardDetalhado(
                    'Plano',
                    d.plano_nome,
                    d.plano_valor,
                    'ri-price-tag-3-line',
                    'dark'
                    );

                html += cardDetalhado(
                    'Cadastro',
                    d.empresa_cadastro,
                    d.contador_nome,
                    'ri-user-line',
                    'primary'
                    );

            });

            $('#cardsDetalhado').html(html);

        }).always(function(){
            $('#loaderDashboard').hide();
        });

    }

    $('.btn-filtro').click(function(){

        $('.btn-filtro')
        .removeClass('btn-primary active')
        .addClass('btn-light');

        $(this)
        .removeClass('btn-light')
        .addClass('btn-primary active');

        tipoAtual = $(this).data('tipo');

        carregar();
    });

    $('#btnPersonalizado').click(function(){

        tipoAtual = 'personalizado';

        carregar();
    });

    $('#inp-empresa').change(carregar);

</script>
@endsection