@extends('layouts.app', ['title' => 'MDFe - Documentos não encerrados'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h4>MDFe - Documentos não encerrados</h4>
                <div style="text-align: right; margin-top: -35px;">

                    <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modal-importar-xml">
                        <i class="ri-upload-2-line"></i> Importar XML para Encerrar
                    </button>
                    <a href="{{ route('mdfe.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="col-lg-12">

                    <div class="table-responsive">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Chave</th>
                                    <th>Protocolo</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if(sizeof($data) == 0)
                                <tr>
                                    <td colspan="3" class="center-align">
                                        <h5 class="text-center">Nada Encontrado</h5>
                                    </td>
                                </tr>
                                @endif
                                @foreach($data as $m)

                                <tr class="datatable-row">

                                    <td class="datatable-cell">
                                        <span class="codigo" style="width: 250px;" id="chave">
                                            {{$m['chave']}}
                                        </span>
                                    </td>

                                    <td class="datatable-cell">
                                        <span class="codigo" style="width: 150px;" id="protocolo">
                                            {{$m['protocolo']}}
                                        </span>
                                    </td>

                                    <td class="datatable-cell">
                                        <form action="{{ route('mdfe.encerrar') }}" method="get" id="form-{{$m['chave']}}">
                                            <input type="hidden" value="{{$m['chave']}}" name="chave">
                                            <input type="hidden" value="{{$m['protocolo']}}" name="protocolo">
                                            <button class="btn btn-sm btn-danger btn-confirm">Encerrar</button>
                                        </form>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-importar-xml" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mdfe.importarXmlEncerrar') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importar XML MDF-e para Encerrar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Selecione o XML do MDF-e</label>
                        <input type="file" name="xml" class="form-control" accept=".xml" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Importar e Encerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
