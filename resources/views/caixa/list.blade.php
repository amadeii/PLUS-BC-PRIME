@extends('layouts.app', ['title' => 'Lista de Caixa'])
@section('content')

<div class="card mt-1">
    <div class="card-body">
        @if(__isAdmin())
        <a href="{{ route('caixa.abertos-empresa') }}" class="btn btn-dark mb-2">
            <i class="ri-list-indefinite"></i>
            Listar todos os caixas abertos
        </a>
        @endif
        <div class="table-responsive">
            <table class="table table-striped table-centered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Caixa</th>
                        <th>Usuário</th>
                        <th>Data Abertura</th>
                        <th>Data Fechamento</th>
                        <th>Valor Abertura</th>
                        <!-- <th>Valor Fechamento</th> -->
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                    <tr>
                        <td data-label="Caixa">{{ $item->numero_sequencial }}</td>
                        <td data-label="Usuário">{{ $item->usuario ? $item->usuario->name : '--' }}</td>
                        <td data-label="Data Abertura">{{ __data_pt($item->created_at) }}</td>
                        <td data-label="Data Fechamento">{{ $item->data_fechamento ? __data_pt($item->data_fechamento) : '--' }}</td>
                        <td data-label="Valor Abertura">{{ __moeda($item->valor_abertura) }}</td>
                        <!-- <td data-label="Valor Fechamento">{{ __moeda($item->valor_fechamento) }}</td> -->
                        <td>
                            <div style="width: 100px;"> 
                                @if($item->status == 0)
                                <button type="button" onclick="imprimir('{{$item->id}}')" class="btn btn-dark btn-sm" title="Imprimir">
                                    <i class="ri-printer-line"></i>
                                </button>
                                @endif
                                <a class="btn btn-primary btn-sm" href="{{ route('caixa.show' , $item) }}">
                                    <i class="ri-list-indefinite"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Nada encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-print" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">

            <div class="modal-header border-0 pb-2">
                <div>
                    <h5 class="modal-title fw-bold mb-1">
                        <i class="ri-printer-line text-primary me-1"></i>
                        Imprimir relatório
                    </h5>

                    <small class="text-muted">
                        Escolha o modelo de impressão
                    </small>
                </div>

                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2">

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <button type="button" class="btn btn-light border w-100 rounded-4 py-4 d-flex flex-column align-items-center justify-content-center" onclick="print('a4')">

                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                                <i class="ri-file-paper-2-line text-primary" style="font-size:32px;"></i>
                            </div>

                            <span class="fw-bold fs-6">
                                Modelo A4
                            </span>

                            <small class="text-muted mt-1">
                                Impressão completa
                            </small>

                        </button>
                    </div>

                    <div class="col-12 col-md-6">
                        <button type="button" class="btn btn-light border w-100 rounded-4 py-4 d-flex flex-column align-items-center justify-content-center" onclick="print('80')">

                            <div class="bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">

                                <i class="ri-file-list-3-line text-dark" style="font-size:32px;"></i>
                            </div>

                            <span class="fw-bold fs-6">
                                80mm
                            </span>

                            <small class="text-muted mt-1">
                                Cupom térmico
                            </small>

                        </button>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-impressao" tabindex="-1">
    <div class="modal-dialog modal-xl">

        <div class="modal-content border-0">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="ri-printer-line me-1"></i>
                    Visualizar Impressão
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body bg-light p-2" id="modal-impressao-body">

            </div>

        </div>

    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
    var ID = 0
    function imprimir(id){
        ID = id
        $('#modal-print').modal('show')
    }

    function print(tipo){

        let url = '';

        if(tipo == 'a4'){
            url = '/caixa/imprimir/' + ID;
        }else{
            url = '/caixa/imprimir80/' + ID;
        }

        $('#modal-print').modal('hide');

        $('#modal-impressao-body').html(`
            <iframe 
            src="${url}" 
            style="width:100%;height:85vh;border:0;border-radius:14px;background:#fff;">
            </iframe>
            `);

        $('#modal-impressao').modal('show');
    }
</script>
@endsection
