@extends('layouts.app', ['title' => 'Configuração SCOPE'])
@section('css')

@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h4>Configuração SCOPE</h4>
                <hr>
                <div class="row mt-3">
                    <div class="col-lg-12">
                        {!!Form::open()->fill($config)
                        ->post()
                        ->route('scope-config.store')
                        ->multipart()
                        !!}
                        <div class="m-2">

                            <div class="card-body">

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
                                    <div class="form-group validated col-lg-2">
                                        <label class="col-form-label">Habilitar TEF</label>
                                        <select class="form-select" name="habilitado" id="habilitado">
                                            <option value="0" @if(isset($config) && $config->habilitado == 0) selected @endif>Não</option>
                                            <option value="1" @if(isset($config) && $config->habilitado == 1) selected @endif>Sim</option>
                                        </select>
                                        <small class="form-text text-muted">Ativa o uso de TEF no PDV</small>
                                    </div>


                                    <hr>

                                    <h5 class="mb-4">Configurações do SCOPE</h5>

                                    <div class="row">
                                        <div class="form-group validated col-lg-6 col-md-6 col-sm-12">
                                            <label class="col-form-label">IP do Agente</label>
                                            <input type="text" 
                                            class="form-control @if($errors->has('agente_ip')) is-invalid @endif" 
                                            name="agente_ip" 
                                            value="{{ old('agente_ip', isset($config) ? $config->agente_ip : '127.0.0.1') }}"
                                            placeholder="127.0.0.1">
                                            @if($errors->has('agente_ip'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('agente_ip') }}
                                            </div>
                                            @endif
                                            <small class="form-text text-muted">IP onde o AgenteScope está rodando (geralmente 127.0.0.1)</small>
                                        </div>

                                        <div class="form-group validated col-lg-6 col-md-6 col-sm-12">
                                            <label class="col-form-label">Porta do Agente</label>
                                            <input type="number" 
                                            class="form-control @if($errors->has('agente_porta')) is-invalid @endif" 
                                            name="agente_porta" 
                                            value="{{ old('agente_porta', isset($config) ? $config->agente_porta : '8000') }}"
                                            placeholder="8443"
                                            min="1"
                                            max="65535">
                                            @if($errors->has('agente_porta'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('agente_porta') }}
                                            </div>
                                            @endif
                                            <small class="form-text text-muted">Porta HTTPS do AgenteScope (padrão: 8443)</small>
                                        </div>
                                    </div>

                                    <input type="hidden" name="usuario_id" value="{{ $usuario_id }}">

                                    <div class="alert alert-info mt-4">
                                        <h6><i class="la la-info-circle"></i> Informações importantes:</h6>
                                        <ul class="mb-0">
                                            <li>Certifique-se de que o <strong>AgenteScope</strong> está instalado e rodando</li>
                                            <li>O <strong>PinPad</strong> deve estar conectado e configurado no agente</li>

                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-2">
                            <div class="col-12" style="text-align: right;">
                                <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                            </div>
                        </div>
                        {!!Form::close()!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">

</script>
@endsection
