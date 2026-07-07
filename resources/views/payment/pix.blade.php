@extends('layouts.app', ['title' => 'Pagamento'])

@section('css')
<style type="text/css">
    :root{
        --pix-primary: #2563eb;
        --pix-primary-dark: #1d4ed8;
        --pix-success: #10b981;
        --pix-text: #0f172a;
        --pix-muted: #64748b;
        --pix-line: #e2e8f0;
        --pix-bg: #f8fafc;
        --pix-card: #ffffff;
    }

    body{
        background: linear-gradient(180deg, #eef4ff 0%, #f8fafc 35%, #ffffff 100%);
    }

    .pix-page{
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 15px;
    }

    .pix-shell{
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
    }

    .pix-card{
        background: var(--pix-card);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.10);
        border: 1px solid rgba(226,232,240,0.9);
    }

    .pix-grid{
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        min-height: 720px;
    }

    .pix-left{
        position: relative;
        padding: 48px;
        background:
            radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 32%),
            radial-gradient(circle at bottom right, rgba(16,185,129,.10), transparent 28%),
            linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #1d4ed8 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .pix-badge{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.14);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        border-radius: 999px;
        padding: 10px 16px;
        width: fit-content;
        backdrop-filter: blur(8px);
    }

    .pix-badge-dot{
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 0 6px rgba(52,211,153,.12);
        animation: pixPulse 1.6s infinite;
    }

    @keyframes pixPulse{
        0%{ transform: scale(1); opacity: 1; }
        50%{ transform: scale(1.18); opacity: .8; }
        100%{ transform: scale(1); opacity: 1; }
    }

    .pix-title{
        margin: 22px 0 14px;
        font-size: 44px;
        line-height: 1.08;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .pix-subtitle{
        max-width: 520px;
        font-size: 17px;
        line-height: 1.7;
        color: rgba(255,255,255,.82);
        margin: 0;
    }

    .pix-features{
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-top: 34px;
    }

    .pix-feature{
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.10);
        border-radius: 18px;
        padding: 16px;
        backdrop-filter: blur(10px);
    }

    .pix-feature strong{
        display: block;
        font-size: 14px;
        margin-bottom: 6px;
    }

    .pix-feature span{
        display: block;
        font-size: 12px;
        line-height: 1.5;
        color: rgba(255,255,255,.72);
    }

    .pix-steps{
        margin-top: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .pix-step{
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .pix-step-num{
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
    }

    .pix-step-text{
        color: rgba(255,255,255,.84);
        font-size: 14px;
        line-height: 1.6;
        padding-top: 4px;
    }

    .pix-right{
        background: #fff;
        padding: 38px 34px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pix-paybox{
        width: 100%;
        max-width: 470px;
    }

    .pix-paybox-head{
        text-align: center;
        margin-bottom: 22px;
    }

    .pix-mini{
        display: inline-block;
        padding: 7px 12px;
        background: #eff6ff;
        color: var(--pix-primary);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    .pix-paybox-head h2{
        margin: 0;
        font-size: 30px;
        line-height: 1.15;
        color: var(--pix-text);
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .pix-paybox-head p{
        margin: 10px 0 0;
        color: var(--pix-muted);
        font-size: 14px;
        line-height: 1.7;
    }

    .pix-qr-wrap{
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid var(--pix-line);
        border-radius: 24px;
        padding: 24px;
        text-align: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }

    .pix-qr-frame{
        width: 100%;
        max-width: 330px;
        margin: 0 auto;
        padding: 16px;
        border-radius: 22px;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 14px 30px rgba(37,99,235,.08);
    }

    .pix-qr-frame img{
        width: 100%;
        height: auto;
        display: block;
        border-radius: 14px;
    }

    .pix-qr-note{
        margin-top: 16px;
        font-size: 13px;
        color: var(--pix-muted);
        line-height: 1.6;
    }

    .pix-code-box{
        margin-top: 20px;
    }

    .pix-code-label{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .pix-code-label span{
        font-size: 13px;
        font-weight: 700;
        color: var(--pix-text);
    }

    .pix-code-label small{
        font-size: 12px;
        color: var(--pix-muted);
    }

    .pix-copy-group{
        display: flex;
        align-items: stretch;
        border: 1px solid var(--pix-line);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15,23,42,.05);
    }

    .pix-copy-input{
        border: 0 !important;
        box-shadow: none !important;
        height: 58px;
        font-size: 13px;
        color: var(--pix-text);
        padding: 0 18px;
        background: transparent;
    }

    .pix-copy-btn{
        min-width: 64px;
        border: 0;
        background: linear-gradient(135deg, var(--pix-primary) 0%, var(--pix-primary-dark) 100%);
        color: #fff;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: .2s ease;
        cursor: pointer;
    }

    .pix-copy-btn:hover{
        transform: scale(1.02);
        filter: brightness(1.03);
    }

    .pix-copy-btn:active{
        transform: scale(.98);
    }

    .pix-status-card{
        margin-top: 18px;
        border: 1px solid #dbeafe;
        background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
        border-radius: 18px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pix-status-icon{
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #dbeafe;
        color: var(--pix-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .pix-status-text strong{
        display: block;
        color: var(--pix-text);
        font-size: 14px;
        margin-bottom: 3px;
    }

    .pix-status-text span{
        display: block;
        color: var(--pix-muted);
        font-size: 13px;
    }

    .pix-footer-note{
        margin-top: 18px;
        text-align: center;
        font-size: 12px;
        color: var(--pix-muted);
        line-height: 1.7;
    }

    .pix-safe{
        margin-top: 22px;
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pix-safe-item{
        padding: 8px 12px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid var(--pix-line);
        color: var(--pix-muted);
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 991px){
        .pix-grid{
            grid-template-columns: 1fr;
        }

        .pix-left{
            padding: 32px 24px;
        }

        .pix-right{
            padding: 28px 20px 32px;
        }

        .pix-title{
            font-size: 34px;
        }

        .pix-features{
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px){
        .pix-page{
            padding: 14px;
        }

        .pix-card{
            border-radius: 22px;
        }

        .pix-left{
            padding: 24px 18px;
        }

        .pix-right{
            padding: 22px 14px 24px;
        }

        .pix-title{
            font-size: 28px;
        }

        .pix-paybox-head h2{
            font-size: 24px;
        }

        .pix-copy-input{
            font-size: 12px;
            height: 54px;
        }

        .pix-copy-btn{
            min-width: 58px;
        }
    }
</style>
@endsection

@section('content')
<div class="card">
<div class="pix-page">
    <div class="pix-shell">
        <div class="pix-card">
            <div class="pix-grid">

                <div class="pix-left">
                    <div>
                        <div class="pix-badge">
                            <span class="pix-badge-dot"></span>
                            Recebimento via PIX
                        </div>

                        <h1 class="pix-title">
                            Finalize seu pagamento<br>de forma rápida e segura
                        </h1>

                        <p class="pix-subtitle">
                            Escaneie o QR Code com o aplicativo do seu banco ou copie o código PIX.
                            Após a aprovação, a confirmação acontece automaticamente nesta tela.
                        </p>

                        <div class="pix-features">
                            <div class="pix-feature">
                                <strong>Confirmação automática</strong>
                                <span>Assim que o pagamento for aprovado, a tela atualiza sozinha.</span>
                            </div>

                            <div class="pix-feature">
                                <strong>Pagamento instantâneo</strong>
                                <span>PIX processado de forma ágil, sem precisar enviar comprovante.</span>
                            </div>

                            <div class="pix-feature">
                                <strong>Ambiente seguro</strong>
                                <span>Checkout pensado para uma experiência clara e confiável.</span>
                            </div>
                        </div>
                    </div>

                    <div class="pix-steps">
                        <div class="pix-step">
                            <div class="pix-step-num">1</div>
                            <div class="pix-step-text">Abra o app do seu banco e escolha a opção de pagar com PIX.</div>
                        </div>
                        <div class="pix-step">
                            <div class="pix-step-num">2</div>
                            <div class="pix-step-text">Escaneie o QR Code ou copie o código PIX logo ao lado.</div>
                        </div>
                        <div class="pix-step">
                            <div class="pix-step-num">3</div>
                            <div class="pix-step-text">Conclua o pagamento e aguarde a aprovação automática nesta página.</div>
                        </div>
                    </div>
                </div>

                <div class="pix-right">
                    <div class="pix-paybox">

                        <div class="pix-paybox-head">
                            <span class="pix-mini">PIX QR Code</span>
                            <h2>Escaneie para pagar</h2>
                            <p>
                                Você também pode copiar o código PIX abaixo e colar no aplicativo do seu banco.
                            </p>
                        </div>

                        <div class="pix-qr-wrap">
                            <div class="pix-qr-frame">
                                <img src="data:image/jpeg;base64,{{$item->qr_code_base64}}" alt="QR Code PIX">
                            </div>

                            <div class="pix-qr-note">
                                Mantenha esta tela aberta até a confirmação do pagamento.
                            </div>
                        </div>

                        <div class="pix-code-box">
                            <div class="pix-code-label">
                                <span>Código PIX copia e cola</span>
                                <small>Toque no botão para copiar</small>
                            </div>

                            <div class="pix-copy-group">
                                <input
                                    type="text"
                                    class="form-control pix-copy-input"
                                    value="{{$item->qr_code}}"
                                    id="qrcode_input"
                                    readonly
                                >

                                <button type="button" class="pix-copy-btn" onclick="copy()" title="Copiar código PIX">
                                    <i class="ri-file-copy-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pix-status-card">
                            <div class="pix-status-icon">
                                <i class="ri-loader-4-line"></i>
                            </div>
                            <div class="pix-status-text">
                                <strong>Aguardando confirmação</strong>
                                <span>Verificando automaticamente o status do pagamento.</span>
                            </div>
                        </div>

                        <div class="pix-safe">
                            <span class="pix-safe-item">PIX instantâneo</span>
                            <span class="pix-safe-item">Sem comprovante manual</span>
                            <span class="pix-safe-item">Atualização automática</span>
                        </div>

                        <div class="pix-footer-note">
                            Após a aprovação, você será redirecionado automaticamente.
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('js')
<script type="text/javascript">
    var myInterval;

    function copy(){
        const input = document.querySelector('#qrcode_input');

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(() => {
                swal("", "Código PIX copiado com sucesso!", "success");
            }).catch(() => {
                input.select();
                document.execCommand('copy');
                swal("", "Código PIX copiado com sucesso!", "success");
            });
        } else {
            input.removeAttribute('readonly');
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');
            input.setAttribute('readonly', true);
            swal("", "Código PIX copiado com sucesso!", "success");
        }
    }

    myInterval = setInterval(() => {
        $.ajax({
            url: path_url + 'api/paymentStatus/' + '{{$item->transacao_id}}',
            method: "GET",
            global: false,
            data: {empresa_id: $('#empresa_id').val()}
        }).done((success) => {
            if(success == "approved"){
                clearInterval(myInterval);
                swal("Sucesso", "Pagamento aprovado!", "success").then(() => {
                    location.href = path_url;
                });
            }
        }).fail((err) => {
        });
    }, 3000);
</script>
@endsection