@extends('layouts.app', ['title' => 'Ordens de Produção'])

@section('css')
<style>
    .progress{ background:#e9ecef; border-radius:10px; }
    .progress-bar{ border-radius:10px; }
    .table td{ vertical-align:middle; }
    .badge{ font-size:11px; padding:6px 10px; }
    .op-card{ border:1px solid #eef0f4; border-radius:12px; padding:14px; background:#fff; box-shadow:0 4px 14px rgba(0,0,0,.03); }
    .op-card span{ font-size:12px; color:#6c757d; display:block; margin-bottom:4px; }
    .op-card strong{ font-size:22px; line-height:1; }
    .prazo-badge{ display:inline-flex; align-items:center; gap:5px; border-radius:20px; padding:6px 10px; font-size:11px; font-weight:600; }
    .prazo-hoje{ background:#e7f1ff; color:#0d6efd; }
    .prazo-ok{ background:#eef8f0; color:#198754; }
    .prazo-alerta{ background:#fff6df; color:#b7791f; }
    .prazo-atrasado{ background:#fdecec; color:#dc3545; }
</style>
@endsection

@section('content')
<div class="mt-1">

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Ordens de Produção</h4>
                    <span class="text-muted">Gerencie as OPs cadastradas e acompanhe o andamento da produção</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light-primary text-primary px-3 py-2">{{ $data->total() }} registros</span>

                    @can('ordem_producao_create')
                    <a href="{{ route('ordem-producao.create') }}" class="btn btn-primary">
                        <i class="ri-add-circle-fill"></i> Nova OP
                    </a>
                    @endcan
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl col-md-4 col-6">
                    <div class="op-card">
                        <span>Em produção</span>
                        <strong class="text-primary">{{ $cards['em_producao'] ?? 0 }}</strong>
                    </div>
                </div>

                <div class="col-xl col-md-4 col-6">
                    <div class="op-card">
                        <span>Parciais</span>
                        <strong class="text-warning">{{ $cards['parciais'] ?? 0 }}</strong>
                    </div>
                </div>

                <div class="col-xl col-md-4 col-6">
                    <div class="op-card">
                        <span>Atrasadas</span>
                        <strong class="text-danger">{{ $cards['atrasadas'] ?? 0 }}</strong>
                    </div>
                </div>

                <div class="col-xl col-md-4 col-6">
                    <div class="op-card">
                        <span>Finalizadas hoje</span>
                        <strong class="text-success">{{ $cards['finalizadas_hoje'] ?? 0 }}</strong>
                    </div>
                </div>

                <div class="col-xl col-md-4 col-6">
                    <div class="op-card">
                        <span>Refugo médio</span>
                        <strong class="text-dark">{{ number_format($cards['refugo_medio'] ?? 0, 1, ',', '.') }}%</strong>
                    </div>
                </div>
            </div>

            <hr>

            {!! Form::open()->fill(request()->all())->get() !!}

            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-2">
                    {!! Form::date('start_date', 'Data de início') !!}
                </div>

                <div class="col-md-2">
                    {!! Form::date('end_date', 'Data de fim') !!}
                </div>

                <div class="col-md-2">
                    {!! Form::select('estado', 'Estado', ['' => 'Todos'] + \App\Models\OrdemProducao::estados())
                    ->attrs(['class' => 'form-select']) !!}
                </div>

                <div class="col-md-2">
                    {!! Form::select('situacao', 'Situação', [
                    '' => 'Todas',
                    'parcial' => 'Parcial',
                    'atrasada' => 'Atrasada',
                    'finalizada' => 'Finalizada',
                    'com_refugo' => 'Com refugo'
                    ])->attrs(['class' => 'form-select']) !!}
                </div>

                <div class="col-md-2">
                    {!! Form::text('produto', 'Produto')->attrs(['placeholder' => 'Buscar produto']) !!}
                </div>

                <div class="col-md-2">
                    {!! Form::text('cliente', 'Cliente')->attrs(['placeholder' => 'Buscar cliente']) !!}
                </div>

                <div class="col-md-12">
                    <button class="btn btn-primary">
                        <i class="ri-search-line"></i> Pesquisar
                    </button>

                    <a href="{{ route('ordem-producao.index') }}" class="btn btn-danger">
                        <i class="ri-eraser-fill"></i> Limpar
                    </a>
                </div>
            </div>

            {!! Form::close() !!}

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cadastro</th>
                            <th>Entrega Prevista</th>
                            <th>Estado</th>
                            <th>Produto/Cliente</th>
                            <th>Produção</th>
                            <th>Refugo</th>
                            <th width="220">Progresso</th>
                            <th width="210">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $item)

                        @php
                        $planejado = (float) $item->itens->sum('quantidade');
                        $produzido = (float) ($item->quantidade_produzida ?? 0);
                        $refugo = (float) ($item->quantidade_refugada ?? 0);
                        $pctRefugo = ($produzido + $refugo) > 0 ? ($refugo / ($produzido + $refugo)) * 100 : 0;
                        $produtoNome = optional(optional($item->itens->first())->produto)->nome ?? '-';
                        $clienteNome = optional(optional($item->itens->first())->cliente)->razao_social ?? optional(optional($item->itens->first())->cliente)->nome_fantasia ?? 'Não informado';

                        $prazoClasse = 'prazo-ok';
                        $prazoTexto = 'No prazo';

                        if($item->data_prevista_entrega){
                            $dataPrazo = \Carbon\Carbon::parse($item->data_prevista_entrega)->startOfDay();
                            $hoje = now()->startOfDay();

                            if($dataPrazo->lt($hoje) && !in_array($item->estado, ['entregue', 'encerrada'])){
                                $prazoClasse = 'prazo-atrasado';
                                $prazoTexto = 'Atrasada '.$dataPrazo->diffInDays($hoje).'d';
                            }elseif($dataPrazo->equalTo($hoje)){
                                $prazoClasse = 'prazo-hoje';
                                $prazoTexto = 'Hoje';
                            }elseif($dataPrazo->diffInDays($hoje) <= 2){
                                $prazoClasse = 'prazo-alerta';
                                $prazoTexto = '+'.$hoje->diffInDays($dataPrazo).' dias';
                            }
                        }
                        @endphp

                        <tr class="
                        @if($item->estado == 'producao') table-primary
                        @elseif($item->estado == 'parcial') table-warning
                        @elseif($item->estado == 'finalizada') table-success
                        @elseif($item->estado == 'encerrada') table-dark
                        @endif
                        ">
                        <td data-label="Código">
                            <strong>#{{ $item->codigo_sequencial }}</strong>
                            <br>
                            <small class="text-muted">OP {{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</small>
                        </td>

                        <td data-label="Cadastro">
                            {{ __data_pt($item->created_at) }}
                        </td>

                        <td data-label="Entrega Prevista">
                            @if($item->data_prevista_entrega)
                            <div>{{ __data_pt($item->data_prevista_entrega, 0) }}</div>
                            <span class="prazo-badge {{ $prazoClasse }}">{{ $prazoTexto }}</span>
                            @else
                            <span class="text-muted">Sem previsão</span>
                            @endif
                        </td>

                        <td data-label="Estado">

                            @if($item->estado == 'novo')
                            <span class="badge bg-secondary">Novo</span>

                            @elseif($item->estado == 'liberada')
                            <span class="badge bg-info">Liberada</span>

                            @elseif($item->estado == 'producao')
                            <span class="badge bg-primary">Produção</span>

                            @elseif($item->estado == 'parcial')
                            <span class="badge bg-warning text-dark">Parcial</span>

                            @elseif($item->estado == 'finalizada')
                            <span class="badge bg-success">Finalizada</span>

                            @elseif($item->estado == 'encerrada')
                            <span class="badge bg-dark">Encerrada</span>

                            @elseif($item->estado == 'expedicao')
                            <span class="badge bg-warning">Expedição</span>

                            @elseif($item->estado == 'cancelada')
                            <span class="badge bg-danger">Cancelada</span>

                            @else
                            <span class="badge bg-success">Entregue</span>
                            @endif

                        </td>

                        <td data-label="Produto/Cliente">
                            <strong>{{ $produtoNome }}</strong>
                            <br>
                            <small class="text-muted">{{ $clienteNome }}</small>
                        </td>

                        <td data-label="Produção">
                            <strong>{{ number_format($produzido, 3, ',', '.') }}</strong>
                            <span class="text-muted">/ {{ number_format($planejado, 3, ',', '.') }}</span>
                            <br>
                            <small class="text-muted">
                                Pendente: {{ number_format($item->quantidade_pendente ?? 0, 3, ',', '.') }}
                            </small>
                        </td>

                        <td data-label="Refugo">
                            <strong class="{{ $pctRefugo > 5 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($refugo, 3, ',', '.') }}
                            </strong>
                            <br>
                            <small class="{{ $pctRefugo > 5 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($pctRefugo, 1, ',', '.') }}%
                            </small>
                        </td>

                        <td data-label="Progresso">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px; min-width:100px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $item->percentual_progresso }}%"></div>
                                </div>

                                <span class="fw-bold text-primary">
                                    {{ number_format($item->percentual_progresso, 1, ',', '.') }}%
                                </span>
                            </div>

                            @if($item->operacoes && $item->operacoes->count())
                            @php
                            $ultimaOperacao = $item->operacoes->where('id', $item->ultima_operacao_id)->first();
                            @endphp
                            <small class="text-muted d-block mt-1">
                                Última: {{ $ultimaOperacao->nome_operacao ?? '-' }}
                            </small>
                            @endif
                        </td>

                        <td data-label="Ações">
                            <form action="{{ route('ordem-producao.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                @method('delete')
                                @csrf

                                <div class="d-flex gap-1 flex-wrap">
                                    @can('ordem_producao_edit')
                                    @if($item->estado != 'encerrada')
                                    <a class="btn btn-warning btn-sm" title="Editar" href="{{ route('ordem-producao.edit', $item->id) }}">
                                        <i class="ri-pencil-fill"></i>
                                    </a>
                                    @endif
                                    @endcan

                                    <a title="Ver OP" href="{{ route('ordem-producao.show', $item->id) }}" class="btn btn-dark btn-sm">
                                        <i class="ri-file-text-line"></i>
                                    </a>

                                        <!-- <a title="Apontar produção" href="{{ route('ordem-producao.show', $item->id) }}" class="btn btn-info btn-sm">
                                            <i class="ri-play-circle-line"></i>
                                        </a> -->

                                        <a title="Imprimir" target="_blank" href="{{ route('ordem-producao.imprimir', $item->id) }}" class="btn btn-primary btn-sm">
                                            <i class="ri-printer-line"></i>
                                        </a>

                                        @if($item->estado == 'finalizada')
                                        <a  title="Encerrar OP" href="javascript:void(0)" class="btn btn-success btn-sm btn-encerrar-op" data-url="{{ route('ordem-producao.encerrar', $item->id) }}">
                                            <i class="ri-lock-line"></i>
                                        </a>
                                        @endif

                                        @can('ordem_producao_delete')
                                        @if($item->estado != 'encerrada')
                                        <button type="button" title="Excluir" class="btn btn-danger btn-sm btn-delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endif
                                        @endcan

                                        @if($item->estado == 'novo')
                                        <a title="Liberar OP" href="{{ route('ordem-producao.liberar', $item->id) }}" class="btn btn-info btn-sm">
                                            <i class="ri-lock-unlock-line"></i>
                                        </a>
                                        @endif

                                        @if(in_array($item->estado, ['liberada', 'producao', 'parcial']))
                                        <a title="Apontar produção" href="{{ route('apontamento-producao.show', $item->id) }}" class="btn btn-primary btn-sm">
                                            <i class="ri-play-circle-line"></i>
                                        </a>
                                        @endif


                                        @if(in_array($item->estado, ['producao', 'parcial']) && $item->quantidade_pendente <= 0)
                                        <a title="Finalizar OP" href="{{ route('ordem-producao.finalizar', $item->id) }}" class="btn btn-success btn-sm">
                                            <i class="ri-checkbox-circle-line"></i>
                                        </a>
                                        @endif
                                    </div>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ri-inbox-line" style="font-size:42px;"></i>
                                    <div class="mt-2">Nenhuma ordem de produção encontrada</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {!! $data->appends(request()->all())->links() !!}
            </div>

        </div>
    </div>

</div>
@endsection

@section('js')
<script type="text/javascript">
    $(function(){

        $(document).on('click', '.btn-encerrar-op', function(){

            let url = $(this).data('url');

            swal({
                title: "Atenção",
                text: "Deseja realmente encerrar esta ordem de produção?",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancelar",
                        visible: true,
                        className: "btn btn-danger"
                    },
                    confirm: {
                        text: "Sim, encerrar",
                        className: "btn btn-success"
                    }
                }
            }).then((confirmar) => {

                if(confirmar){
                    window.location.href = url;
                }

            });

        });

    });
</script>
@endsection
