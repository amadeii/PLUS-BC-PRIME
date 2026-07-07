@extends('layouts.app', ['title' => 'Gerar Boletos'])

@section('content')
<div class="mt-1">
    <div class="card">
        <div class="card-body">

            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0">
                        <i class="ri-bank-card-line"></i> Gerar Boletos
                    </h3>
                    <small class="text-muted">
                        Confira os títulos selecionados e ajuste os parâmetros por boleto
                    </small>
                </div>
                <div style="text-align: right; margin-top: -35px;">
                    <a href="{{ route('cobranca-bancaria.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
            </div>

            <hr class="mt-3">

            <form method="POST" action="{{ route('cobranca-bancaria.store') }}" id="form-gerar-boletos">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3 bg-light h-100">
                            <small class="text-muted d-block">Quantidade de títulos</small>
                            <strong style="font-size: 22px">{{ $contas->count() }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 bg-light h-100">
                            <small class="text-muted d-block">Valor total</small>
                            <strong class="text-success" style="font-size: 22px">
                                R$ {{ __moeda($total) }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 bg-light h-100">
                            <small class="text-muted d-block">Primeiro vencimento</small>
                            <strong style="font-size: 18px">
                                {{ __data_pt($contas->min('data_vencimento'), 0) }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 bg-light h-100">
                            <small class="text-muted d-block">Último vencimento</small>
                            <strong style="font-size: 18px">
                                {{ __data_pt($contas->max('data_vencimento'), 0) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="card border">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>Cobranças Selecionados</strong>
                        <small class="text-muted">Edite juros, multa e desconto individualmente</small>
                    </div>

                    <div class="card-body p-1">

                        <div class="row m-1">
                            <div class="col-md-3">
                                {!! Form::select('banco', 'Banco', $contasBancarias)
                                ->required()
                                ->attrs(['class' => 'form-select'])->id('select-banco') !!}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th style="width: 130px;">Juros (%)</th>
                                        <th style="width: 130px;">Multa (%)</th>
                                        <th style="width: 140px;">Número</th>
                                        <th style="min-width: 220px;">Instrução</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contas as $item)
                                    <tr>
                                        <td>
                                            <strong>#{{ $item->numero_sequencial }}</strong>
                                            <input type="hidden" name="boletos[{{ $item->id }}][conta_receber_id]" value="{{ $item->id }}">
                                        </td>

                                        <td>
                                            <strong>{{ $item->cliente->razao_social ?? '--' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $item->cliente->cpf_cnpj ?? '' }}</small>
                                        </td>

                                        <td>
                                            {{ __data_pt($item->data_vencimento, 0) }}
                                        </td>

                                        <td>
                                            <strong class="text-success">
                                                R$ {{ __moeda($item->valor_integral) }}
                                            </strong>
                                        </td>

                                        <td>
                                            <input 
                                            type="tel"
                                            class="form-control form-control-sm percentual campo-juros"
                                            name="boletos[{{ $item->id }}][juros]" 
                                            value="{{ old('boletos.'.$item->id.'.juros', 0) }}"
                                            >
                                        </td>

                                        <td>
                                            <input 
                                            type="tel"
                                            class="form-control form-control-sm percentual campo-multa"
                                            name="boletos[{{ $item->id }}][multa]" 
                                            value="{{ old('boletos.'.$item->id.'.multa', 0) }}"
                                            >
                                        </td>

                                        <td>
                                            <input 
                                            type="tel"
                                            class="form-control form-control-sm campo-numero"
                                            name="boletos[{{ $item->id }}][numero]" 
                                            >
                                        </td>

                                        <td>
                                            <input 
                                            type="text" 
                                            class="form-control form-control-sm campo-instrucao"
                                            name="boletos[{{ $item->id }}][instrucao]" 
                                            value="{{ old('boletos.'.$item->id.'.instrucao') }}"
                                            placeholder="Mensagem opcional"
                                            >
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total geral:</th>
                                        <th class="text-success">R$ {{ __moeda($total) }}</th>
                                        <th colspan="4"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="btn-gerar-boletos">
                        <i class="ri-bank-card-line"></i> Confirmar e gerar boletos
                    </button>

                    <a href="{{ route('cobranca-bancaria.index') }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-gerando-boletos" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="ri-loader-4-line" style="font-size: 42px; animation: girar 1s linear infinite; display: inline-block; color: #28a745;"></i>
                </div>
                <h5 class="mb-2">Gerando boletos</h5>
                <p class="text-muted mb-0">
                    Aguarde, não feche a tela para evitar processamento em duplicidade.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes girar {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('js')
<script>
    $(function(){
        let enviandoFormulario = false;

        function carregarDadosBanco(banco){
            if(!banco){
                return;
            }

            $.get("{{ route('cobranca-bancaria.banco-dados') }}", {
                banco: banco
            }, function(result){
                console.log(result)

                $('.campo-juros').each(function(){
                    $(this).val(result.juros ?? 0);
                });

                $('.campo-multa').each(function(){
                    $(this).val(result.multa ?? 0);
                });

                $('.campo-instrucao').each(function(){
                    $(this).val(result.instrucao ?? '');
                });

                let numeroAtual = parseInt(result.ultimo_numero_boleto || 0);

                $('.campo-numero').each(function(){
                    numeroAtual++;
                    $(this).val(numeroAtual);
                });

            }).fail(function(xhr){
                let msg = "Erro ao buscar os dados do banco.";

                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }

                swal("Erro", msg, "error");
            });
        }

        $('#select-banco').change(function(){
            let banco = $(this).val();
            carregarDadosBanco(banco);
        });

        if($('#select-banco').val()){
            carregarDadosBanco($('#select-banco').val());
        }

        $('#btn-regerar-numeros').click(function(){
            let banco = $('#select-banco').val();

            if(!banco){
                swal("Atenção", "Selecione um banco primeiro.", "warning");
                return;
            }

            $.get("{{ route('cobranca-bancaria.banco-dados') }}", {
                banco: banco
            }, function(result){

                if(!result.status){
                    swal("Atenção", result.message || "Não foi possível carregar a sequência.", "warning");
                    return;
                }

                let numeroAtual = parseInt(result.ultimo_numero_boleto || 0);

                $('.campo-numero').each(function(){
                    numeroAtual++;
                    $(this).val(numeroAtual);
                });

            }).fail(function(xhr){
                let msg = "Erro ao regerar sequência.";

                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }

                swal("Erro", msg, "error");
            });
        });

        $('#form-gerar-boletos').on('submit', function(e){
            if(enviandoFormulario){
                e.preventDefault();
                return false;
            }

            enviandoFormulario = true;

            $('#btn-gerar-boletos')
                .prop('disabled', true)
                .html('<i class="ri-loader-4-line"></i> Gerando...');

            $('#modal-gerando-boletos').modal('show');
        });
    });
</script>
@endsection