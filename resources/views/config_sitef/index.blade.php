@extends('layouts.app', ['title' => 'Configuração SITEF'])
@section('css')

@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h4>Configuração SITEF</h4>
                <hr>
                <div class="row mt-3">
                    <div class="col-lg-12">
                        {!!Form::open()->fill($config)
                        ->post()
                        ->route('sitef-config.store')
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

                                    <div class="row">
                                        <div class="form-group validated col-lg-6 col-md-6 col-sm-12">
                                            <label class="col-form-label">IP do SiTef</label>
                                            <input type="text" 
                                            class="form-control @if($errors->has('sitef_ip')) is-invalid @endif" 
                                            name="sitef_ip" 
                                            value="{{ old('sitef_ip', isset($config) ? $config->sitef_ip : '192.168.0.100') }}"
                                            placeholder="192.168.0.100"
                                            required>
                                            @if($errors->has('sitef_ip'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('sitef_ip') }}
                                            </div>
                                            @endif
                                            <small class="form-text text-muted">IP do servidor SiTef na rede</small>
                                        </div>

                                        <div class="form-group validated col-lg-3 col-md-3 col-sm-12">
                                            <label class="col-form-label">Store ID</label>
                                            <input type="text" 
                                            class="form-control @if($errors->has('store_id')) is-invalid @endif" 
                                            name="store_id" 
                                            value="{{ old('store_id', isset($config) ? $config->store_id : '00000000') }}"
                                            placeholder="00000000"
                                            maxlength="20"
                                            required>
                                            @if($errors->has('store_id'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('store_id') }}
                                            </div>
                                            @endif
                                            <small class="form-text text-muted">Código da loja</small>
                                        </div>

                                        <div class="form-group validated col-lg-3 col-md-3 col-sm-12">
                                            <label class="col-form-label">Terminal ID</label>
                                            <input type="text" 
                                            class="form-control @if($errors->has('terminal_id')) is-invalid @endif" 
                                            name="terminal_id" 
                                            value="{{ old('terminal_id', isset($config) ? $config->terminal_id : '00000001') }}"
                                            placeholder="00000001"
                                            maxlength="20"
                                            required>
                                            @if($errors->has('terminal_id'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('terminal_id') }}
                                            </div>
                                            @endif
                                            <small class="form-text text-muted">Código do terminal</small>
                                        </div>
                                    </div>

                                    <hr>

                                    <h5 class="mb-4">Configurações do AgenteCliSiTef</h5>

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
                                            <small class="form-text text-muted">IP onde o AgenteCliSiTef está rodando (geralmente 127.0.0.1)</small>
                                        </div>

                                        <div class="form-group validated col-lg-6 col-md-6 col-sm-12">
                                            <label class="col-form-label">Porta do Agente</label>
                                            <input type="number" 
                                            class="form-control @if($errors->has('agente_porta')) is-invalid @endif" 
                                            name="agente_porta" 
                                            value="{{ old('agente_porta', isset($config) ? $config->agente_porta : '8443') }}"
                                            placeholder="8443"
                                            min="1"
                                            max="65535">
                                            @if($errors->has('agente_porta'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('agente_porta') }}
                                            </div>
                                            @endif
                                            <small class="form-text text-muted">Porta HTTPS do AgenteCliSiTef (padrão: 8443)</small>
                                        </div>
                                    </div>

                                    <input type="hidden" name="usuario_id" value="{{ $usuario_id }}">

                                    <div class="alert alert-info mt-4">
                                        <h6><i class="ri-information-line"></i> Informações importantes:</h6>
                                        <ul class="mb-0">
                                            <li>Certifique-se de que o <strong>AgenteCliSiTef</strong> está instalado e rodando</li>
                                            <li>O agente deve estar configurado para aceitar conexões HTTPS na porta especificada</li>
                                            <li>O <strong>PinPad</strong> deve estar conectado e configurado no agente</li>
                                            <li>Os valores de <strong>Store ID</strong> e <strong>Terminal ID</strong> devem corresponder à configuração do SiTef</li>
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
