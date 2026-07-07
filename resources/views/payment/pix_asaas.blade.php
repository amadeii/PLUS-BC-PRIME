@extends('layouts.app', ['title' => 'Pagamento'])

@section('css')
<style type="text/css">
    .payment-page{
        padding: 10px 0 40px;
    }

    .payment-card{
        border: 0 !important;
        border-radius: 24px !important;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
    }

    .payment-header{
        background: linear-gradient(135deg, #0d6efd, #2563eb);
        color: #fff;
        padding: 28px 24px;
        text-align: center;
    }

    .payment-header h3{
        font-weight: 800;
        margin-bottom: 8px;
    }

    .payment-header p{
        margin-bottom: 0;
        opacity: .95;
        font-size: 15px;
    }

    .payment-body{
        padding: 30px 20px;
    }

    .payment-price{
        text-align: center;
        margin-bottom: 18px;
    }

    .payment-price .label{
        font-size: 14px;
        color: #64748b;
        margin-bottom: 6px;
    }

    .payment-price .value{
        font-size: 2rem;
        font-weight: 800;
        color: #0d6efd;
        line-height: 1;
    }

    .payment-alert{
        max-width: 760px;
        margin: 0 auto 24px auto;
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 12px 16px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }

    .payment-qr-box{
        background: linear-gradient(180deg, #f8fafc, #ffffff);
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        padding: 20px;
        text-align: center;
        max-width: 520px;
        margin: 0 auto 24px auto;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }

    .payment-qr-box img{
        width: 100%;
        max-width: 320px;
        height: auto;
        border-radius: 18px;
        background: #fff;
        padding: 12px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }

    .payment-qr-title{
        font-size: 15px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 16px;
    }

    .payment-copy-area{
        max-width: 760px;
        margin: 0 auto;
    }

    .payment-copy-label{
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .payment-input-group{
        display: flex;
        align-items: stretch;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #dbeafe;
        background: #fff;
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.07);
    }

    .payment-input-group input{
        border: 0 !important;
        box-shadow: none !important;
        font-size: 14px;
        padding: 14px 16px;
        height: 54px;
    }

    .payment-copy-btn{
        min-width: 64px;
        border: 0;
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s ease;
        font-size: 20px;
    }

    .payment-copy-btn:hover{
        filter: brightness(1.05);
    }

    .payment-copy-btn:active{
        transform: scale(.98);
    }

    .payment-tips{
        max-width: 760px;
        margin: 24px auto 0 auto;
        padding: 18px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .payment-tips h6{
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .payment-tips ul{
        margin: 0;
        padding-left: 18px;
        color: #475569;
        font-size: 14px;
    }

    .payment-tips li + li{
        margin-top: 6px;
    }

    @media (max-width: 768px){
        .payment-header{
            padding: 24px 16px;
        }

        .payment-header h3{
            font-size: 22px;
        }

        .payment-body{
            padding: 20px 14px;
        }

        .payment-price .value{
            font-size: 1.7rem;
        }

        .payment-qr-box{
            padding: 16px;
            border-radius: 18px;
        }

        .payment-input-group input{
            font-size: 13px;
            padding: 12px 14px;
            height: 50px;
        }

        .payment-copy-btn{
            min-width: 56px;
        }
    }
</style>
@endsection

@section('content')
<div class="card mt-1">
    <div class="row m-3">
        <div class="payment-page">
            <div class="card payment-card">
                <div class="payment-header">
                    <h3><i class="ri-qr-code-line"></i> Finalizar pagamento</h3>
                    <p>Escaneie o QR Code ou copie o código PIX para concluir a contratação do seu plano.</p>
                </div>

                <div class="payment-body">
                    <div class="payment-price">
                        <div class="label">Valor do plano</div>
                        <div class="value">R$ {{ __moeda($item->valor) }}</div>
                    </div>

                    <div class="payment-alert">
                        Após realizar o pagamento, permaneça nesta tela até a confirmação automática.
                    </div>

                    <div class="payment-qr-box">
                        <div class="payment-qr-title">Escaneie com o app do seu banco</div>
                        <img src="data:image/jpeg;base64,{{$data['encodedImage']}}" alt="QR Code PIX">
                    </div>

                    <div class="payment-copy-area">
                        <div class="payment-copy-label">Ou copie o código PIX</div>

                        <div class="payment-input-group">
                            <input type="text" class="form-control" value="{{$data['payload']}}" id="qrcode_input" readonly />
                            <button type="button" class="payment-copy-btn" onclick="copy()">
                                <i class="ri-file-copy-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="payment-tips">
                        <h6><i class="ri-information-line"></i> Instruções</h6>
                        <ul>
                            <li>Abra o aplicativo do seu banco e escolha a opção pagar com PIX.</li>
                            <li>Escaneie o QR Code ou cole o código copiado.</li>
                            <li>Após o pagamento, a confirmação será feita automaticamente nesta página.</li>
                        </ul>
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
        const input = document.querySelector("#qrcode_input");
        input.removeAttribute('readonly');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        input.setAttribute('readonly', true);

        swal("Sucesso", "Código PIX copiado com sucesso!", "success");
    }

    myInterval = setInterval(() => {
        console.clear();

        let json = {
            id: '{{ $data["id"] }}',
            plano_id: '{{ $item->id }}',
            empresa_id: $('#empresa_id').val()
        };

        $.ajax({
            url: path_url + 'api/payment-status-asaas',
            method: 'GET',
            global: false,
            data: json
        })
        .done((success) => {
            console.log(success);

            if (success === 'pago') {
                clearInterval(myInterval);

                swal("Sucesso", "Pagamento aprovado!", "success")
                .then(() => {
                    location.href = path_url;
                });
            }
        })
        .fail((err) => {
            console.log(err);
        });
    }, 3000);
</script>
@endsection