@extends('layouts.app', ['title' => 'XML para Contador'])

@section('css')
@endsection

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <h4>XML para Contador</h4>
                <hr>

                <div class="row mt-3">
                    <div class="col-lg-12">

                        {!! Form::open()->fill($config)->post()->route('relatorio-xml-contador.store') !!}

                        <div class="m-2">

                            @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="row">

                                <div class="form-group validated col-lg-2 col-md-3 col-sm-12">
                                    <label class="col-form-label">Ativo</label>
                                    <select class="form-select @if($errors->has('ativo')) is-invalid @endif" name="ativo">
                                        <option value="0" @if(old('ativo', $config->ativo ?? 0) == 0) selected @endif>Não</option>
                                        <option value="1" @if(old('ativo', $config->ativo ?? 0) == 1) selected @endif>Sim</option>
                                    </select>
                                    @if($errors->has('ativo'))
                                    <div class="invalid-feedback">{{ $errors->first('ativo') }}</div>
                                    @endif
                                    <small class="form-text text-muted">Habilita o envio automático</small>
                                </div>

                                <div class="form-group validated col-lg-2 col-md-3 col-sm-12">
                                    <label class="col-form-label">Dia do envio</label>
                                    <input type="tel" data-mask="00" class="form-control @if($errors->has('dia_envio')) is-invalid @endif" name="dia_envio" min="1" max="28" value="{{ old('dia_envio', $config->dia_envio ?? 5) }}" required>
                                    @if($errors->has('dia_envio'))
                                    <div class="invalid-feedback">{{ $errors->first('dia_envio') }}</div>
                                    @endif
                                    <small class="form-text text-muted">Dia do mês, de 1 até 28</small>
                                </div>

                                <div class="form-group validated col-lg-5 col-md-6 col-sm-12">
                                    <label class="col-form-label">E-mail do contador</label>
                                    <input type="email" class="form-control @if($errors->has('email_contador')) is-invalid @endif" name="email_contador" value="{{ old('email_contador', $config->email_contador ?? '') }}" placeholder="contador@empresa.com.br" required>
                                    @if($errors->has('email_contador'))
                                    <div class="invalid-feedback">{{ $errors->first('email_contador') }}</div>
                                    @endif
                                    <small class="form-text text-muted">Destinatário dos XMLs e relatório</small>
                                </div>

                            </div>

                            <hr>

                            <div class="form-group validated">
                                <label class="col-form-label">Mensagem adicional</label>
                                <textarea class="form-control @if($errors->has('mensagem_email')) is-invalid @endif" rows="4" name="mensagem_email" placeholder="Mensagem opcional para enviar junto ao e-mail">{{ old('mensagem_email', $config->mensagem_email ?? '') }}</textarea>
                                @if($errors->has('mensagem_email'))
                                <div class="invalid-feedback">{{ $errors->first('mensagem_email') }}</div>
                                @endif
                                <small class="form-text text-muted">Texto opcional que será enviado no corpo do e-mail</small>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h6><i class="ri-information-line"></i> O que será enviado?</h6>

                                <ul class="mb-3">
                                    <li>XML de NFe aprovadas</li>
                                    <li>XML de NFe canceladas</li>
                                    <li>XML de NFC-e aprovadas</li>
                                    <li>XML de NFC-e canceladas</li>
                                    <li>Relatório PDF resumido do período</li>
                                    <li>Arquivos XML compactados em ZIP separados por NFe e NFC-e</li>
                                </ul>

                                <hr>

                                <h6><i class="ri-alert-line"></i> Atenção, Requisitos para funcionamento</h6>

                                <ul class="mb-0">
                                    <li>O servidor deve possuir a extensão <strong>PHP Fileinfo</strong> habilitada.</li>
                                    <li>O <strong>Cron Job</strong> do servidor deve estar configurado e ativo.</li>
                                    <!-- <li>O comando de envio automático deve estar agendado no Laravel Scheduler.</li> -->
                                    <li>O e-mail do contador deve estar configurado corretamente.</li>
                                    <li>Os XMLs da competência informada devem estar disponíveis no sistema.</li>
                                </ul>
                            </div>

                            <hr>


                            <div class="col-12 text-end">
                                <a href="{{ route('relatorio-xml-contador.logs', ['empresa_id' => request()->empresa_id]) }}" class="btn btn-primary me-2">
                                    <i class="fa fa-history"></i>
                                    Ver Logs
                                </a>

                                <button type="submit" class="btn btn-success px-5" id="btn-store">
                                    <i class="fa fa-save"></i>
                                    Salvar
                                </button>
                            </div>

                        </div>

                        {!! Form::close() !!}

                        @if(session('xml_contador_output'))
                        <div class="alert alert-secondary mt-3">
                            <strong>Retorno do teste:</strong>
                            <pre class="mb-0 mt-2">{{ session('xml_contador_output') }}</pre>
                        </div>
                        @endif

                        <form id="form-testar-envio" method="POST" action="{{ route('relatorio-xml-contador.testar-envio') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="empresa_id" value="{{ request()->empresa_id }}">
                            <button type="button" class="btn btn-warning me-2 btn-testar-envio">
                                <i class="fa fa-paper-plane"></i>
                                Testar envio agora
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).on('click', '.btn-testar-envio', function(){

        swal({
            title: "Testar envio?",
            text: "Será gerado o relatório e enviado um e-mail de teste para o contador configurado.",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "Cancelar",
                    visible: true
                },
                confirm: {
                    text: "Sim, enviar",
                    closeModal: false
                }
            }
        }).then((confirmado) => {

            if(!confirmado){
                return;
            }

            $('#form-testar-envio').submit();
        });

    });
</script>
@endsection