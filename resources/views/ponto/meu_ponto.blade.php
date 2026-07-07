@extends('layouts.app', ['title' => 'Meu Ponto'])

@section('css')
<style>
.meu-ponto-page{
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 25px;
}

.ponto-box{
    width: 100%;
    max-width: 760px;
    background: #fff;
    border-radius: 22px;
    padding: 32px;
    box-shadow: 0 18px 45px rgba(0,0,0,.10);
}

.ponto-top{
    text-align: center;
    margin-bottom: 25px;
}

.ponto-top h2{
    font-size: 30px;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 5px;
}

.ponto-relogio{
    font-size: 18px;
    color: #5B5BD6;
    font-weight: 700;
}

.ponto-status{
    background: #f5f6ff;
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ponto-status span{
    color: #6b7280;
}

.ponto-status strong{
    color: #111827;
    font-size: 18px;
}

.ponto-actions{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 28px;
}

.btn-ponto{
    height: 82px;
    border: none;
    border-radius: 18px;
    color: #fff;
    font-size: 19px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 10px 22px rgba(0,0,0,.12);
    transition: .2s;
}

.btn-ponto:hover{
    transform: translateY(-2px);
}

.btn-ponto:disabled{
    opacity: .35;
    cursor: not-allowed;
    transform: none;
    filter: grayscale(1);
}

.btn-entrada{
    background: linear-gradient(45deg, #12B886, #5B5BD6);
}

.btn-saida{
    background: linear-gradient(45deg, #EF4444, #B91C1C);
}

.ponto-lista h4{
    font-weight: 800;
    margin-bottom: 12px;
}

.ponto-item{
    background: #f8f9fc;
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.ponto-tipo{
    font-weight: 800;
    color: #374151;
}

.ponto-data{
    color: #6b7280;
    font-weight: 600;
}

.ponto-badge{
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 800;
    display: inline-block;
}

.ponto-badge-entrada{
    background: #ecfdf5;
    color: #059669;
}

.ponto-badge-saida{
    background: #fef2f2;
    color: #dc2626;
}

@media(max-width: 768px){
    .ponto-actions{
        grid-template-columns: 1fr;
    }

    .ponto-status{
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
}
</style>
@endsection

@section('content')
<div class="meu-ponto-page">
    <div class="ponto-box">

        <div class="ponto-top">
            <h2>Meu Ponto</h2>
            <div class="ponto-relogio" id="relogioPonto">
                {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>

        <div class="ponto-status">
            <div>
                <span>Funcionário</span><br>
                <strong>{{ auth()->user()->funcionario ? auth()->user()->funcionario->nome : auth()->user()->name }}</strong>
            </div>

            <div class="text-right">
                <span>Status</span><br>
                <strong>{{ $status ?? 'Pronto para entrada' }}</strong>

                @if(($proximoTipo ?? 'entrada') == 'entrada')
                    <div class="mt-1">
                        <span class="ponto-badge ponto-badge-entrada">Próximo: Entrada</span>
                    </div>
                @else
                    <div class="mt-1">
                        <span class="ponto-badge ponto-badge-saida">Próximo: Saída</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="ponto-actions">
            <button
                type="button"
                class="btn-ponto btn-entrada"
                onclick="baterPonto('entrada')"
                @if(($proximoTipo ?? 'entrada') != 'entrada') disabled @endif
            >
                <i class="fa fa-sign-in"></i> Entrada
            </button>

            <button
                type="button"
                class="btn-ponto btn-saida"
                onclick="baterPonto('saida')"
                @if(($proximoTipo ?? 'entrada') != 'saida') disabled @endif
            >
                <i class="fa fa-sign-out"></i> Saída
            </button>
        </div>

        <div class="ponto-lista">
            <h4>Últimos registros</h4>

            @forelse($registros as $r)
                <div class="ponto-item">
                    <div class="ponto-tipo">
                        {{ ucfirst(str_replace('_', ' ', $r->tipo)) }}
                    </div>
                    <div class="ponto-data">
                        {{ \Carbon\Carbon::parse($r->data_hora)->format('d/m/Y H:i') }}
                    </div>
                </div>
            @empty
                <div class="ponto-item">
                    <div class="ponto-tipo">Nenhum registro encontrado</div>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection

@section('js')
<script>
setInterval(() => {
    let d = new Date();
    $('#relogioPonto').text(d.toLocaleString('pt-BR'));
}, 1000);

function baterPonto(tipo){

    if(!navigator.geolocation){
        swal("Erro", "Geolocalização não suportada", "error");
        return;
    }

    swal({
        title: "Aguarde",
        text: "Obtendo localização...",
        icon: "info",
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });

    navigator.geolocation.getCurrentPosition(function(pos){

        $.post("{{ route('meu-ponto.bater') }}", {
            _token: "{{ csrf_token() }}",
            tipo: tipo,
            latitude: pos.coords.latitude,
            longitude: pos.coords.longitude
        }, function(res){

            swal("Sucesso", res.message, "success")
            .then(() => {
                location.reload();
            });

        }).fail(function(err){

            swal(
                "Erro",
                err.responseJSON?.message || "Erro ao registrar ponto",
                "error"
            );

        });

    }, function(){

        swal("Erro", "Não foi possível obter localização", "error");

    }, {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0
    });
}
</script>
@endsection