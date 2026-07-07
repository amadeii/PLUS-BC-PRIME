@extends('layouts.app', ['title' => 'Manifesto'])
@section('css')
<style type="text/css">
    .acoes-grid{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        min-width: 220px;
    }

    .acoes-grid .btn{
        font-size: 12px;
        padding: 6px 8px;
        border-radius: 6px;
        text-align: center;
        color: #fff;
        border: none;
    }

    .btn-completa{
        background: linear-gradient(135deg,#1fa87a,#2bbf8a);
    }

    .btn-imprimir{
        background: linear-gradient(135deg,#4b6ed6,#5f7ef0);
    }

    .btn-manifestar{
        grid-column: span 2;
        background: linear-gradient(135deg,#2d8fd6,#3aa0ea);
    }

    .btn-desconhecida{
        grid-column: span 2;
        background: #e74c3c;
    }

    .btn-nao-realizada{
        grid-column: span 2;
        background: #f39c12;
    }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="row align-items-center mb-3">

                    <div class="col-md-4">
                        <a href="{{ route('manifesto.novaConsulta') }}" class="btn btn-dark">
                            <i class="ri-refresh-line"></i>
                            Nova Consulta de Documentos
                        </a>
                    </div>

                    <div class="col-md-8">
                        <div class="d-flex justify-content-end gap-3">

                            <div class="card shadow-sm border-0 px-3 py-2">
                                <small class="text-muted">Total de Importações</small>
                                <h5 class="mb-0 text-success fw-bold">
                                    R$ {{ __moeda($totalGeral) }}
                                </h5>
                            </div>

                            <div class="card shadow-sm border-0 px-3 py-2">
                                <small class="text-muted">Total da Página</small>
                                <h5 class="mb-0 text-primary fw-bold">
                                    R$ {{ __moeda($totalPagina) }}
                                </h5>
                            </div>

                        </div>
                    </div>

                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data final')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado',
                            [
                            '' => 'Todos',
                            1 => 'Ciência',
                            2 => 'Confirmada',
                            3 => 'Desconhecido',
                            4 => 'Op. não Realizada'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tpNf', 'Tipo da NFe',
                            [
                            '' => 'Todos',
                            0 => 'Devolução',
                            1 => 'Compra',
                            ])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        @if(__countLocalAtivo() > 1)
                        <div class="col-md-2">
                            {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        @endif
                        <div class="col-lg-2 col-12">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('manifesto.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <table class="table table-centered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>Documento</th>
                                    @if(__countLocalAtivo() > 1)
                                    <th>Local</th>
                                    @endif
                                    <th>Valor</th>
                                    <th>Data de Cadastro</th>
                                    <th>Data de Emissão</th>
                                    <th>Num. Protocolo</th>
                                    <th>Chave</th>
                                    <th>Estado</th>
                                    <th>Tipo da NFe</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr>
                                    <td data-label="Nome">{{ $item->nome }}</td>
                                    <td data-label="Documento">{{ $item->documento }}</td>
                                    @if(__countLocalAtivo() > 1)
                                    <td data-label="Local">{{ optional($item->localizacao)->descricao ?? '--' }}</td>
                                    @endif
                                    <td data-label="Valor">{{ __moeda($item->valor) }}</td>
                                    <td data-label="Data de Cadastro">{{ __data_pt($item->created_at) }}</td>
                                    <td data-label="Data de Emissão">{{ __data_pt($item->data_emissao) }}</td>
                                    <td data-label="Num. Protocolo">{{ $item->num_prot }}</td>
                                    <td data-label="Chave">{{ $item->chave }}</td>
                                    <td data-label="Estado">{{ $item->estado() }}</td>
                                    <td data-label="Tipo da NFe">{{ $item->tpNF != null ? ($item->tpNF == 0 ? 'Devolução' : 'Compra') : '' }}</td>
                                    <td>
                                        <div class="acoes-grid">

                                            @if($item->tipo == 1 || $item->tipo == 2)
                                            <a href="{{ route('manifesto.download', [$item->id]) }}" class="btn btn-completa">
                                                <i class="ri-file-download-line"></i> Completa
                                            </a>

                                            <a target="_blank" href="{{ route('manifesto.danfe', [$item->id]) }}" class="btn btn-imprimir">
                                                <i class="ri-printer-line"></i> Imprimir
                                            </a>
                                            @elseif($item->tipo == 3)
                                            <span class="btn btn-desconhecida">
                                                <i class="ri-error-warning-line"></i> Desconhecida
                                            </span>
                                            @elseif($item->tipo == 4)
                                            <span class="btn btn-nao-realizada">
                                                <i class="ri-close-circle-line"></i> Não realizada
                                            </span>
                                            @endif

                                            @if($item->tipo != 2)
                                            <a class="btn btn-manifestar"
                                            onclick="setChave('{{$item->chave}}')"
                                            data-toggle="modal"
                                            data-target="#modal-evento">
                                            <i class="ri-send-plane-line"></i> Manifestar
                                        </a>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="mt-2">
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="modal-evento" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" action="{{ route('manifesto.manifestar') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Manifestação NFe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="chave" id="chave">
                <div class="col-md-6">
                    {!! Form::select('tipo', 'Tipo', [1 => "Ciencia", 2 => "Confirmação", 3 => "Desconhecimento", 4 => "Operação não realizada"])
                    ->attrs(['class' => 'form-select']) !!}
                </div>

                <div class="col-md-12 just d-none mt-3">
                    {!! Form::text('justificativa', 'Justificativa') !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info px-5">Manifestar</button>
            </div>
        </form>
    </div>
</div>

@section('js')
<script type="text/javascript">
    function setChave(chave) {
        $('#chave').val(chave)
        $('#modal-evento').modal('show')
    }

    $(document).on("change", "#inp-tipo", function() {
        if ($(this).val() > 2) {
            $('.just').removeClass('d-none')
        } else {
            $('.just').addClass('d-none')
        }
    })

</script>
@endsection

@endsection
