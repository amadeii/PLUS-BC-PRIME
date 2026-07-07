@extends('layouts.app', ['title' => 'Logs XML Contador'])

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Logs de Envio XML Contador</h4>

                    <a href="{{ route('relatorio-xml-contador.index', ['empresa_id' => request()->empresa_id]) }}" class="btn btn-danger">
                        <i class="fa fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>

                <hr>

                <form method="GET">
                    <input type="hidden" name="empresa_id" value="{{ request()->empresa_id }}">

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>E-mail</label>
                            <input type="text" name="email" class="form-control" value="{{ request('email') }}">
                        </div>

                        <div class="col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="sucesso" @selected(request('status') == 'sucesso')>Sucesso</option>
                                <option value="erro" @selected(request('status') == 'erro')>Erro</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Competência</label>
                            <input type="month" data-mask="00/0000" name="competencia" class="form-control" value="{{ request('competencia') }}">
                        </div>

                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('relatorio-xml-contador.logs') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Enviado em</th>
                                <th>Competência</th>
                                <th>E-mail</th>
                                <th>NFe</th>
                                <th>NFC-e</th>
                                <th>Arquivos</th>
                                <th>Status</th>
                                <th>Mensagem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    {{ $log->enviado_em ? \Carbon\Carbon::parse($log->enviado_em)->format('d/m/Y H:i') : '-' }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($log->competencia)->format('m/Y') }}
                                </td>

                                <td>{{ $log->email_contador }}</td>

                                <td>
                                    <span class="badge bg-success">Aprovadas: {{ $log->total_nfe_aprovada }}</span>
                                    <br>
                                    <span class="badge bg-danger mt-1">Canceladas: {{ $log->total_nfe_cancelada }}</span>
                                </td>

                                <td>
                                    <span class="badge bg-success">Aprovadas: {{ $log->total_nfce_aprovada }}</span>
                                    <br>
                                    <span class="badge bg-danger mt-1">Canceladas: {{ $log->total_nfce_cancelada }}</span>
                                </td>

                                <td>
                                    @if($log->arquivo_zip_nfe)
                                        <div><span class="badge bg-primary">ZIP NFe</span></div>
                                    @endif

                                    @if($log->arquivo_zip_nfce)
                                        <div class="mt-1"><span class="badge bg-primary">ZIP NFC-e</span></div>
                                    @endif

                                    @if($log->arquivo_pdf)
                                        <div class="mt-1"><span class="badge bg-secondary">PDF</span></div>
                                    @endif

                                    @if(!$log->arquivo_zip_nfe && !$log->arquivo_zip_nfce && !$log->arquivo_pdf)
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($log->status == 'sucesso')
                                        <span class="badge bg-success">Sucesso</span>
                                    @else
                                        <span class="badge bg-danger">Erro</span>
                                    @endif
                                </td>

                                <td>{{ $log->mensagem ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Nenhum log encontrado</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $logs->appends(request()->all())->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection