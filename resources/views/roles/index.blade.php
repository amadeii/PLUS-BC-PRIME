@extends('layouts.app', ['title' => 'Atribuições'])
@section('css')
<style>
    .permissoes-box{ max-height:360px; overflow:auto; border:1px solid #e5e7eb; border-radius:14px; padding:12px; display:flex; flex-wrap:wrap; gap:8px; background:#f8fafc; }
    .permission-chip{ margin:0; cursor:pointer; user-select:none; }
    .permission-chip input{ display:none; }
    .permission-chip span{ display:block; padding:8px 12px; border-radius:999px; border:1px solid #d9dee8; background:#fff; color:#374151; font-size:13px; font-weight:600; transition:.2s; }
    .permission-chip input:checked + span{ background:#159892; border-color:#159892; color:#fff; box-shadow:0 6px 14px rgba(21,152,146,.25); }
    .permission-chip:hover span{ border-color:#159892; color:#159892; }
    .permission-chip input:checked + span:hover{ color:#fff; }
    .permission-chip.d-none{ display:none!important; }
    .gap-2{ gap:8px; }

    [data-theme="dark"] .modal-content{ background:#151923; color:#e5e7eb; }
    [data-theme="dark"] .card{ background:#1b2130; border-color:#2a3244!important; }
    [data-theme="dark"] .permissoes-box{ background:#111827; border-color:#2a3244; }
    [data-theme="dark"] .permission-chip span{ background:#1b2130; border-color:#374151; color:#d1d5db; }
    [data-theme="dark"] .permission-chip input:checked + span{ background:#159892; border-color:#159892; color:#fff; }
    [data-theme="dark"] .form-control{ background:#111827; border-color:#374151; color:#e5e7eb; }
    [data-theme="dark"] .text-muted{ color:#9ca3af!important; }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">

                    <a href="{{ route('roles.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Atribuição
                    </a>

                    <button type="button" class="btn btn-primary btn-sincronizar-permissoes">
                        <i class="ri-shield-keyhole-line"></i> Sincronizar permissões
                    </button>
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('descricao', 'Pesquisar por descrição')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::select('empresa', 'Pesquisar por empresa')
                            ->options($empresa ? [$empresa->id => $empresa->nome] : [])
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('roles.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                <div class="col-md-12 mt-3 table-responsive">
                    <h5>Total de registros: <strong class="text-success">{{ $data->total() }}</strong></h5>

                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Empresa</th>
                                    <th>Data de cadastro</th>
                                    <th>Data de atualização</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>

                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ $item->empresa ? $item->empresa->nome : '' }}</td>
                                    <td>{{ __data_pt($item->created_at) }}</td>
                                    <td>{{ __data_pt($item->updated_at) }}</td>
                                    <td>
                                        @if($item->name != 'gestor_plataforma')
                                        <form action="{{ route('roles.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            <a class="btn btn-warning btn-sm" href="{{ route('roles.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @csrf
                                            @if($item->empresa)
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif
                                        </form>
                                        @endif
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
                <br>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSincronizarPermissoes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="POST" action="{{ route('roles.sincronizar-permissoes') }}" class="modal-content border-0 shadow-lg">
            @csrf

            <div class="modal-header border-0 px-4 py-4" style="background:linear-gradient(135deg,#159892,#19b8aa); color:#fff;">
                <div>
                    <h4 class="fw-bold mb-1 text-white">Sincronizar permissões</h4>
                    <small class="text-white opacity-75">Adicione permissões em lote para várias atribuições</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4 pt-4">

                <div class="alert alert-info border-0 d-flex align-items-start mb-4">
                    <div class="me-3"><i class="ri-shield-keyhole-line fs-4"></i></div>
                    <div>
                        <div class="fw-semibold mb-1">As permissões serão apenas adicionadas</div>
                        <small>As permissões atuais das atribuições não serão removidas.</small>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-4">Destino da sincronização</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Atribuições </label>
                                <select name="atribuicoes[]" class="form-control select2" multiple required>
                                    <option value="todas">Todas</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Vendedor">Vendedor</option>
                                </select>
                                <small class="text-muted">Escolha quais tipos de atribuição receberão as permissões.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Empresas</label>
                                <select name="empresas[]" class="form-control select2" multiple>
                                    @foreach($empresas as $e)
                                    <option value="{{ $e->id }}">{{ $e->nome }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Se não selecionar empresa, aplica para todas.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">Permissões para adicionar</h6>
                                <small class="text-muted">Use a busca e selecione permissões em lote.</small>
                            </div>

                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <button type="button" class="btn btn-sm btn-light-primary" id="btn-marcar-filtradas">
                                    <i class="ri-check-double-line"></i> Marcar filtradas
                                </button>
                                <button type="button" class="btn btn-sm btn-light-danger" id="btn-limpar-permissoes">
                                    <i class="ri-close-circle-line"></i> Limpar
                                </button>
                            </div>
                        </div>

                        <input type="text" class="form-control mb-3" id="filtro-permissoes-modal" placeholder="Filtrar permissão... Ex: produto, venda, nfe, financeiro">

                        <div class="permissoes-box">
                            @foreach($permissions as $p)
                            <label class="permission-chip" data-text="{{ strtolower(($p->description ?? $p->name)) }}">
                                <input type="checkbox" name="permissoes[]" value="{{ $p->id }}">
                                <span>{{ $p->description ?? $p->name }}</span>
                            </label>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Selecionadas: <strong id="qtd-permissoes-selecionadas">0</strong>
                            </small>
                            <small class="text-muted">
                                Exibindo: <strong id="qtd-permissoes-exibidas">{{ $permissions->count() }}</strong>
                            </small>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn px-4 text-white" style="background:#159892;">
                    <i class="ri-save-line me-1"></i> Sincronizar permissões
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).on('click', '.btn-sincronizar-permissoes', function(){
        $('#modalSincronizarPermissoes').modal('show');
    });

    function atualizarContadorPermissoes(){
        let selecionadas = $('.permission-chip input:checked').length;
        let exibidas = $('.permission-chip:not(.d-none)').length;

        $('#qtd-permissoes-selecionadas').text(selecionadas);
        $('#qtd-permissoes-exibidas').text(exibidas);
    }

    $(document).on('input', '#filtro-permissoes-modal', function(){
        let termo = $(this).val().toLowerCase();

        $('.permission-chip').each(function(){
            let texto = $(this).data('text');

            if(texto.includes(termo)){
                $(this).removeClass('d-none');
            }else{
                $(this).addClass('d-none');
            }
        });

        atualizarContadorPermissoes();
    });

    $(document).on('click', '#btn-marcar-filtradas', function(){
        $('.permission-chip:not(.d-none) input').prop('checked', true);
        atualizarContadorPermissoes();
    });

    $(document).on('click', '#btn-limpar-permissoes', function(){
        $('.permission-chip input').prop('checked', false);
        atualizarContadorPermissoes();
    });

    $(document).on('change', '.permission-chip input', function(){
        atualizarContadorPermissoes();
    });

    $(document).on('shown.bs.modal', '#modalSincronizarPermissoes', function(){
        atualizarContadorPermissoes();
    });

</script>
@endsection