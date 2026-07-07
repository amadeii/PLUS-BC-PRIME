@extends('layouts.app', ['title' => 'Conectar App'])

@section('css')
<style>
    :root{
        --color-main: {{ $config->cor_principal ?? '#8448dc' }};
    }

    .app-connect-page{padding:10px 0 40px;}

    .app-connect-card{
        border:0!important;
        border-radius:28px!important;
        overflow:hidden;
        background:#fff;
        box-shadow:0 20px 60px rgba(15,23,42,.08);
    }

    .app-connect-header{
        background:linear-gradient(135deg,var(--color-main),#5b21b6);
        padding:32px 26px;
        color:#fff;
        position:relative;
        overflow:hidden;
        transition:.2s;
    }

    .app-connect-header:before{
        content:'';
        position:absolute;
        width:260px;
        height:260px;
        border-radius:50%;
        background:rgba(255,255,255,.08);
        top:-120px;
        right:-80px;
    }

    .app-connect-header-content{
        position:relative;
        z-index:2;
        display:flex;
        align-items:center;
        gap:16px;
    }

    .app-connect-icon{
        width:68px;
        height:68px;
        border-radius:22px;
        background:rgba(255,255,255,.14);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:30px;
        backdrop-filter:blur(6px);
    }

    .app-connect-header h3{
        font-size:28px;
        font-weight:900;
        margin-bottom:6px;
    }

    .app-connect-header p{
        margin:0;
        opacity:.92;
        font-size:15px;
        font-weight:500;
    }

    .app-connect-body{
        padding:24px;
        background:#f8fafc;
    }

    .app-connect-alert{
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:18px;
        padding:16px;
        margin-bottom:20px;
        display:flex;
        align-items:center;
        gap:12px;
        color:#475569;
        font-weight:600;
    }

    .app-connect-alert i{
        color:var(--color-main);
        font-size:22px;
    }

    .color-picker-box{
        background:#fff;
        border:1px solid #eef0f6;
        border-radius:24px;
        padding:18px;
        margin-bottom:20px;
    }

    .color-picker-title{
        font-size:15px;
        font-weight:900;
        color:#111827;
        margin-bottom:14px;
    }

    .color-grid{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
    }

    .color-item{
        width:44px;
        height:44px;
        border-radius:16px;
        cursor:pointer;
        border:3px solid #fff;
        box-shadow:0 4px 12px rgba(15,23,42,.10);
        transition:.2s;
        position:relative;
    }

    .color-item:hover{
        transform:scale(1.08);
    }

    .color-item.active:after{
        content:'';
        position:absolute;
        inset:-5px;
        border:2px solid #111827;
        border-radius:20px;
    }

    .qr-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
        gap:18px;
    }

    .qr-card{
        background:#fff;
        border-radius:24px;
        border:1px solid #eef0f6;
        overflow:hidden;
        transition:all .2s ease;
        box-shadow:0 10px 30px rgba(15,23,42,.05);
    }

    .qr-card:hover{
        transform:translateY(-3px);
        box-shadow:0 18px 40px rgba(15,23,42,.10);
    }

    .qr-top{
        padding:18px;
        display:flex;
        align-items:center;
        gap:14px;
        border-bottom:1px solid #f1f5f9;
    }

    .qr-avatar{
        width:56px;
        height:56px;
        border-radius:18px;
        background:linear-gradient(135deg,var(--color-main),#7c3aed);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:22px;
        font-weight:900;
    }

    .qr-user-info h4{
        margin:0;
        font-size:17px;
        font-weight:900;
        color:#111827;
    }

    .qr-user-info span{
        display:block;
        margin-top:4px;
        color:#6b7280;
        font-size:13px;
        font-weight:700;
    }

    .qr-image{
        padding:22px;
        text-align:center;
        background:linear-gradient(180deg,#fff,#f8fafc);
    }

    .qr-image img{
        width:100%;
        max-width:250px;
        background:#fff;
        border-radius:24px;
        padding:14px;
        border:1px solid #eef0f6;
        box-shadow:0 12px 28px rgba(15,23,42,.08);
    }

    .qr-footer{
        padding:18px;
        border-top:1px solid #f1f5f9;
    }

    .qr-info{
        display:flex;
        align-items:center;
        justify-content:space-between;
        background:#f8fafc;
        border:1px solid #eef0f6;
        border-radius:16px;
        padding:12px 14px;
        margin-bottom:10px;
    }

    .qr-info:last-child{
        margin-bottom:0;
    }

    .qr-info small{
        display:block;
        color:#64748b;
        font-size:11px;
        font-weight:700;
        text-transform:uppercase;
        margin-bottom:3px;
    }

    .qr-info strong{
        color:#111827;
        font-size:14px;
        font-weight:800;
    }

    .btn-copy-json{
        width:42px;
        height:42px;
        border-radius:14px;
        border:0;
        background:linear-gradient(135deg,var(--color-main),#7c3aed);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:18px;
        transition:.2s;
    }

    .btn-copy-json:hover{
        transform:scale(1.04);
    }

    .qr-json{
        display:none;
    }

    @media(max-width:768px){

        .app-connect-header{
            padding:24px 18px;
        }

        .app-connect-header-content{
            align-items:flex-start;
        }

        .app-connect-header h3{
            font-size:22px;
        }

        .app-connect-body{
            padding:16px;
        }

        .qr-grid{
            grid-template-columns:1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="app-connect-page">

    <div class="card app-connect-card">

        <div class="app-connect-header" id="appHeader">

            <div class="app-connect-header-content">

                <div class="app-connect-icon">
                    <i class="ri-qr-code-line"></i>
                </div>

                <div>
                    <h3>Conectar aplicativo</h3>

                    <p>
                        Escaneie o QR Code no app React Native para configurar automaticamente o sistema.
                    </p>
                </div>

            </div>

        </div>

        <div class="app-connect-body">

            <div class="app-connect-alert">
                <i class="ri-smartphone-line"></i>

                <div>
                    Abra o app e utilize a opção <strong>Ler QR Code</strong>.
                </div>
            </div>

            <div class="color-picker-box">

                <div class="color-picker-title">
                    <i class="ri-palette-line"></i>
                    Escolha a cor principal do aplicativo
                </div>

                <div class="color-grid">

                    <div class="color-item active" data-color="#8448dc" style="background:#8448dc"></div>
                    <div class="color-item" data-color="#2563eb" style="background:#2563eb"></div>
                    <div class="color-item" data-color="#e11d48" style="background:#e11d48"></div>
                    <div class="color-item" data-color="#16a34a" style="background:#16a34a"></div>
                    <div class="color-item" data-color="#ea580c" style="background:#ea580c"></div>
                    <div class="color-item" data-color="#0891b2" style="background:#0891b2"></div>
                    <div class="color-item" data-color="#111827" style="background:#111827"></div>

                </div>

            </div>

            @if(session()->has('error'))
            <div class="app-connect-alert" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">
                <i class="ri-error-warning-line" style="color:#dc2626;"></i>

                <div>
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <div class="qr-grid">

                @foreach($dados as $key => $item)

                <div class="qr-card">

                    <div class="qr-top">

                        <div class="qr-avatar">
                            {{ mb_substr($item['funcionario']->nome, 0, 1) }}
                        </div>

                        <div class="qr-user-info">
                            <h4>{{ $item['funcionario']->nome }}</h4>

                            <span>
                                Operador #{{ $item['payload']['codigo_operador'] }}
                            </span>
                        </div>

                    </div>

                    <div class="qr-image">

                        <img
                        class="qr-dynamic"
                        data-base='@json($item["payload"])'
                        src="{{ $item['qrcode'] }}">

                    </div>

                    <div class="qr-footer">

                        <div class="qr-info">
                            <div>
                                <small>Servidor</small>
                                <strong>{{ url('/') }}</strong>
                            </div>
                        </div>

                        <div class="qr-info">

                            <div>
                                <small>Token API</small>
                                <strong>{{ Str::limit($item['payload']['token'], 18) }}</strong>
                            </div>

                            <button
                            type="button"
                            class="btn-copy-json"
                            onclick="copyJson('json_{{ $key }}')">
                            <i class="ri-file-copy-line"></i>
                        </button>

                    </div>

                    <textarea
                    id="json_{{ $key }}"
                    class="qr-json">{{ $item['json'] }}</textarea>

                </div>

            </div>

            @endforeach

        </div>

    </div>

</div>
@endsection

@section('js')
<script>

    let corAtual = '#8448dc';

    $('.color-item').on('click', function(){

        $('.color-item').removeClass('active');

        $(this).addClass('active');

        corAtual = $(this).data('color');

        document.documentElement.style.setProperty('--color-main', corAtual);

        $('.qr-dynamic').each(function(index){

            let payload = $(this).data('base');

            payload.cor_principal = corAtual;

            let json = JSON.stringify(payload);

            $('#json_' + index).val(json);

            let novaImagem = "{{ route('app-qrcode-image') }}?json=" + encodeURIComponent(json);

            $(this).attr('src', novaImagem);

        });

    });

    function copyJson(id){

        const input = document.getElementById(id);

        input.style.display = 'block';

        input.select();

        input.setSelectionRange(0, 99999);

        document.execCommand('copy');

        input.style.display = 'none';

        swal(
            "Sucesso",
            "JSON copiado com sucesso!",
            "success"
            );
    }

</script>
@endsection