@extends('layouts.app', ['title' => 'Logs dos Crons'])

@section('content')

<div class="mt-1">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8 col-12">
                    <h3 class="mb-0">
                        <i class="ri-file-list-3-line"></i> Logs dos Crons
                    </h3>
                    <small class="text-muted">
                        Relação de execuções e eventos dos crons de cobrança
                    </small>
                </div>

                <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                    <a href="{{ url()->current() }}" class="btn btn-primary btn-sm">
                        <i class="ri-refresh-line"></i> Atualizar
                    </a>
                    <a href="{{ route('cobrancas.index') }}" class="btn btn-sm btn-danger">
                        <i class="ri-arrow-left-line"></i> Voltar
                    </a>
                </div>
            </div>

            <hr class="mt-3">

            <div class="col-lg-12 mb-2">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-3 g-2">
                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('status', 'Status')
                        ->options([
                        '' => 'Todos',
                        'INFO' => 'INFO',
                        'SUCESSO' => 'SUCESSO',
                        'ERRO' => 'ERRO',
                        ])->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('origem', 'Origem', $contasBancarias)
                        ->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-3 col-xl-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i> Pesquisar
                        </button>

                        <a class="btn btn-danger" href="{{ route('cobrancas.logs') }}">
                            <i class="ri-eraser-fill"></i> Limpar
                        </a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            @if($data->count() > 0)

            <div class="table-responsive mt-3">
                <table class="table align-middle table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Executado em</th>
                            <th>Comando</th>
                            <th>Origem</th>
                            <th>Empresa</th>
                            <th>Boleto</th>
                            <th>Status</th>
                            <th>Mensagem</th>
                            <th>Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td>
                                <strong>#{{ $item->id }}</strong>
                            </td>

                            <td>
                                <strong>{{ $item->executado_em ? $item->executado_em->format('d/m/Y H:i:s') : '--' }}</strong>
                            </td>

                            <td>
                                {{ $item->comando ?? '--' }}
                            </td>

                            <td>
                                {{ strtoupper($item->origem ?? '--') }}
                            </td>

                            <td>
                                {{ $item->empresa_id ?? '--' }}
                            </td>

                            <td>
                                {{ $item->boleto_id ?? '--' }}
                            </td>

                            <td>
                                @if($item->status == 'SUCESSO')
                                <span class="badge bg-success">SUCESSO</span>
                                @elseif($item->status == 'ERRO')
                                <span class="badge bg-danger">ERRO</span>
                                @else
                                <span class="badge bg-secondary">INFO</span>
                                @endif
                            </td>

                            <td style="max-width: 350px;">
                                {{ $item->mensagem ?? '--' }}
                            </td>

                            <td>
                                @if($item->payload)
                                <button type="button"
                                class="btn btn-sm btn-outline-info"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPayload{{ $item->id }}">
                                <i class="ri-eye-line"></i> Ver
                            </button>
                            @else
                            --
                            @endif
                        </td>
                    </tr>

                    @if($item->payload)
                    <div class="modal fade" id="modalPayload{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Payload do Log #{{ $item->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($item->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $data->links() }}
        </div>

        @else

        <div class="text-center py-5">
            <i class="ri-file-warning-line text-primary" style="font-size: 40px;"></i>
            <h5 class="mt-3">Nenhum log encontrado</h5>
            <p class="text-muted">Ainda não há logs para os filtros informados.</p>
        </div>

        @endif

    </div>
</div>
</div>

@endsection