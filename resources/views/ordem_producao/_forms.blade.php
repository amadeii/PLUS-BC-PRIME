@section('css')
<style>
    .cost-card{ position:relative; overflow:hidden; border-radius:18px; padding:20px; min-height:140px; display:flex; align-items:flex-start; gap:16px; color:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08); }
    .cost-card::after{ content:''; position:absolute; right:-30px; top:-30px; width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,.08); }
    .cost-material{ background:linear-gradient(135deg,#4f46e5,#6366f1); }
    .cost-labor{ background:linear-gradient(135deg,#059669,#10b981); }
    .cost-process{ background:linear-gradient(135deg,#d97706,#f59e0b); }
    .cost-total{ background:linear-gradient(135deg,#111827,#1f2937); }
    .cost-icon{ width:58px; height:58px; border-radius:16px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0; }
    .cost-content{ position:relative; z-index:2; }
    .cost-content span{ display:block; font-size:13px; opacity:.9; margin-bottom:5px; }
    .cost-content h3{ margin:0; font-size:28px; font-weight:700; line-height:1.2; }
    .cost-footer{ margin-top:10px; opacity:.85; }
    .indicator-box{ display:flex; align-items:center; justify-content:space-between; padding:14px; border-radius:14px; border:1px solid #edf0f5; margin-bottom:14px; }
    .indicator-icon{ width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; }

    .op-alerts{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; margin-bottom:15px; }
    .op-alert{ border:1px solid #eef0f6; border-radius:14px; padding:12px; display:flex; align-items:center; gap:10px; background:#fff; }
    .op-alert i{ width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px; }
    .op-alert strong{ display:block; font-size:13px; margin-bottom:1px; }
    .op-alert span{ font-size:12px; color:#6b7280; }
    .op-alert.ok i{ background:#10b981; }
    .op-alert.warn i{ background:#f59e0b; }
    .op-alert.danger i{ background:#ef4444; }
    .op-footer-actions{ display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap; }

    #opTabs{
        gap: 12px;
        flex-wrap: wrap;
    }

    #opTabs .nav-item{
        margin-right: 8px;
        margin-bottom: 8px;
    }

    #opTabs .nav-link{
        padding-left: 18px;
        padding-right: 18px;
    }
</style>
@endsection

<div class="row g-3">

    @if(isset($orcamento) && $orcamento != null)
    <input type="hidden" name="orcamento_id" value="{{ $orcamento->id }}">
    @endif

    <div class="col-12">
        <ul class="nav nav-tabs" id="opTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-geral" type="button"><i class="ri-file-list-3-line"></i> Geral</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-roteiro" type="button"><i class="ri-route-line"></i> Roteiro</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-materiais" type="button"><i class="ri-stack-line"></i> Materiais</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-custos" type="button"><i class="ri-money-dollar-circle-line"></i> Custos</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-observacoes" type="button"><i class="ri-chat-3-line"></i> Observações</button></li>
        </ul>
    </div>

    <div class="col-12">
        <div class="tab-content border border-top-0 p-3 bg-white">

            <div class="tab-pane fade show active" id="tab-geral">
                <div class="row g-3">

                    @if(isset($item) && isset($validacao))
                    <div class="col-12">
                        <div class="op-alerts">

                            <div class="op-alert {{ ($validacao['estrutura_ok'] ?? $item->estrutura_ok) ? 'ok' : 'danger' }}">
                                <i class="ri-node-tree"></i>
                                <div>
                                    <strong>Estrutura</strong>
                                    <span>{{ ($validacao['estrutura_ok'] ?? $item->estrutura_ok) ? 'Estrutura OK' : 'Produto sem estrutura' }}</span>
                                </div>
                            </div>

                            <div class="op-alert {{ ($validacao['roteiro_ok'] ?? $item->roteiro_ok) ? 'ok' : 'danger' }}">
                                <i class="ri-route-line"></i>
                                <div>
                                    <strong>Roteiro</strong>
                                    <span>{{ ($validacao['roteiro_ok'] ?? $item->roteiro_ok) ? 'Roteiro OK' : 'Produto sem roteiro' }}</span>
                                </div>
                            </div>

                            <div class="op-alert {{ ($validacao['estoque_ok'] ?? $item->estoque_ok) ? 'ok' : 'warn' }}">
                                <i class="ri-archive-line"></i>
                                <div>
                                    <strong>Estoque</strong>
                                    <span>{{ ($validacao['estoque_ok'] ?? $item->estoque_ok) ? 'Estoque suficiente' : 'Estoque insuficiente' }}</span>
                                </div>
                            </div>

                            <div class="op-alert {{ ($validacao['custos_ok'] ?? $item->custos_ok) ? 'ok' : 'warn' }}">
                                <i class="ri-money-dollar-circle-line"></i>
                                <div>
                                    <strong>Custos</strong>
                                    <span>{{ ($validacao['custos_ok'] ?? $item->custos_ok) ? 'Custos calculados' : 'Custos não calculados' }}</span>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif

                    <div class="col-12">
                        <div class="card border">
                            <div class="card-body py-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-2"><small class="text-muted d-block">Status da OP</small><span class="badge bg-secondary px-3 py-2">{{ isset($item) ? $item->estado_texto : 'Nova' }}</span></div>
                                    <div class="col-md-2"><small class="text-muted d-block">Nº OP</small><strong>{{ isset($item) && $item->codigo_sequencial ? '#'.$item->codigo_sequencial : 'Automático' }}</strong></div>
                                    <div class="col-md-2"><small class="text-muted d-block">Prioridade</small><strong>{{ isset($item) && $item->prioridade ? $item->prioridade_texto : 'Média' }}</strong></div>
                                    <div class="col-md-2"><small class="text-muted d-block">Tipo</small><strong>{{ isset($item) && $item->tipo_producao ? $item->tipo_producao_texto : 'Produção' }}</strong></div>
                                    <div class="col-md-2"><small class="text-muted d-block">Progresso</small><strong class="text-primary">{{ isset($item) ? number_format($item->percentual_progresso, 1, ',', '.') : '0,0' }}%</strong></div>
                                    <div class="col-md-2"><small class="text-muted d-block">Data criação</small><strong>{{ isset($item) ? __data_pt($item->created_at) : date('d/m/Y H:i') }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">{!! Form::date('data_prevista_entrega', 'Data prevista de entrega') !!}</div>

                    <div class="col-md-3">
                        {!! Form::select('funcionario_id', 'Responsável PCP')->options(isset($item) && $item->funcionario_id ? [$item->funcionario_id => $item->funcionario->nome] : []) !!}
                    </div>

                    <div class="col-md-2">{!! Form::select('estado', 'Estado', App\Models\OrdemProducao::estados())->attrs(['class' => 'form-select']) !!}</div>
                    <div class="col-md-2">{!! Form::select('prioridade', 'Prioridade', App\Models\OrdemProducao::prioridades())->attrs(['class' => 'form-select']) !!}</div>
                    <div class="col-md-2">{!! Form::select('tipo_producao', 'Tipo produção', App\Models\OrdemProducao::tiposProducao())->attrs(['class' => 'form-select']) !!}</div>

                    <div class="col-12 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Produtos da Ordem</h5>
                            <button type="button" class="btn btn-dark btn-sm btn-add px-3"><i class="ri-add-fill"></i> Adicionar Produto</button>
                        </div>

                        @include('ordem_producao._tabela_produtos')
                    </div>

                </div>
            </div>

            <div class="tab-pane fade" id="tab-roteiro">
                <div class="alert alert-light border">
                    <strong>Resumo do roteiro</strong><br>
                    <span class="text-muted">Após selecionar o produto, aqui será exibido o roteiro previsto da produção.</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Seq.</th>
                                <th>Operação</th>
                                <th>Setor</th>
                                <th>Tempo previsto</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-roteiro">
                            @if(isset($item) && $item->operacoes->count())
                            @foreach($item->operacoes->sortBy('sequencia') as $op)
                            <tr data-tempo="{{ $op->tempo_previsto_minutos ?? 0 }}">
                                <td>{{ $op->sequencia }}</td>
                                <td>{{ $op->nome_operacao }}</td>
                                <td>{{ $op->nome_setor ?? '-' }}</td>
                                <td>{{ $op->tempo_previsto_minutos ?? 0 }} min</td>
                            </tr>
                            @endforeach
                            @else
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum roteiro carregado</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-materiais">
                <div class="alert alert-light border">
                    <strong>Materiais previstos</strong><br>
                    <span class="text-muted">Aqui será exibida a estrutura do produto e a disponibilidade de estoque.</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Material</th>
                                <th>Necessário</th>
                                <th>Disponível</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-materiais">
                            @if(isset($item) && isset($item->materiais) && $item->materiais->count())
                            @foreach($item->materiais as $m)
                            @php
                            $estoqueAtual = $m->material && method_exists($m->material, 'estoqueAtual') ? $m->material->estoqueAtual() : 0;
                            @endphp
                            <tr data-custo-total="{{ $m->custo_total_previsto ?? 0 }}">
                                <td>{{ $m->material->nome ?? '-' }}</td>
                                <td>{{ __moeda($m->quantidade_prevista) }}</td>
                                <td>{{ __moeda($estoqueAtual) }}</td>
                                <td>
                                    @if($estoqueAtual >= $m->quantidade_prevista)
                                    <span class="badge bg-success">Disponível</span>
                                    @else
                                    <span class="badge bg-danger">Insuficiente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum material carregado</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-custos">

                @php
                $total = (float) (isset($item) ? ($item->custo_total ?? 0) : 0);
                $custoMaterial = (float) (isset($item) ? ($item->custo_material ?? 0) : 0);
                $custoMaoObra = (float) (isset($item) ? ($item->custo_mao_obra ?? 0) : 0);
                $custoProcesso = (float) (isset($item) ? ($item->custo_processo ?? 0) : 0);

                $pctMaterial = $total > 0 ? ($custoMaterial / $total) * 100 : 0;
                $pctMaoObra = $total > 0 ? ($custoMaoObra / $total) * 100 : 0;
                $pctProcesso = $total > 0 ? ($custoProcesso / $total) * 100 : 0;
                $qtdProduzir = isset($item) ? (float) $item->itens->sum('quantidade') : 0;
                $custoUnitario = $qtdProduzir > 0 ? ($total / $qtdProduzir) : 0;
                $tempoPrevisto = isset($item) ? (int) $item->operacoes->sum('tempo_previsto_minutos') : 0;
                $horas = floor($tempoPrevisto / 60);
                $minutos = $tempoPrevisto % 60;
                @endphp

                <input type="hidden" name="custo_material" class="input-custo-material" value="{{ $custoMaterial }}">
                <input type="hidden" name="custo_mao_obra" class="input-custo-mao-obra" value="{{ $custoMaoObra }}">
                <input type="hidden" name="custo_processo" class="input-custo-processo" value="{{ $custoProcesso }}">
                <input type="hidden" name="custo_total" class="input-custo-total" value="{{ $total }}">

                <div class="row g-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="cost-card cost-material">
                            <div class="cost-icon"><i class="ri-stack-fill"></i></div>
                            <div class="cost-content">
                                <span>Custo material</span>
                                <h3>R$ <span class="js-custo-material">{{ __moeda($custoMaterial) }}</span></h3>
                                <div class="cost-footer"><small>Matéria-prima consumida</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="cost-card cost-labor">
                            <div class="cost-icon"><i class="ri-user-star-fill"></i></div>
                            <div class="cost-content">
                                <span>Mão de obra</span>
                                <h3>R$ <span class="js-custo-mao-obra">{{ __moeda($custoMaoObra) }}</span></h3>
                                <div class="cost-footer"><small>Tempo operacional</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="cost-card cost-process">
                            <div class="cost-icon"><i class="ri-settings-3-fill"></i></div>
                            <div class="cost-content">
                                <span>Custos indiretos</span>
                                <h3>R$ <span class="js-custo-processo">{{ __moeda($custoProcesso) }}</span></h3>
                                <div class="cost-footer"><small>Energia, setup e processo</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="cost-card cost-total">
                            <div class="cost-icon"><i class="ri-money-dollar-circle-fill"></i></div>
                            <div class="cost-content">
                                <span>Custo total previsto</span>
                                <h3>R$ <span class="js-custo-total">{{ __moeda($total) }}</span></h3>
                                <div class="cost-footer"><small>Custo consolidado da OP</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-1">Composição de custos</h5>
                                        <small class="text-muted">Detalhamento financeiro da ordem de produção</small>
                                    </div>
                                    <span class="badge badge-pill badge-primary px-3 py-2">R$ <span class="js-table-custo-total">{{ __moeda($total) }}</span></span>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Categoria</th>
                                                <th width="220">Representatividade</th>
                                                <th class="text-end">Valor</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-primary mr-3" style="width:12px;height:12px;"></div><div><div class="fw-bold">Materiais</div><small class="text-muted">Matéria-prima e insumos</small></div></div></td>
                                                <td><div class="d-flex align-items-center"><div class="progress flex-grow-1" style="height:10px;"><div class="progress-bar bg-primary js-pct-material-bar" style="width: {{ $pctMaterial }}%"></div></div><span class="ml-2 fw-bold text-primary js-pct-material-text">{{ number_format($pctMaterial, 1, ',', '.') }}%</span></div></td>
                                                <td class="text-end"><div class="fw-bold text-primary">R$ <span class="js-table-custo-material">{{ __moeda($custoMaterial) }}</span></div></td>
                                            </tr>

                                            <tr>
                                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-success mr-3" style="width:12px;height:12px;"></div><div><div class="fw-bold">Mão de obra</div><small class="text-muted">Operadores e execução</small></div></div></td>
                                                <td><div class="d-flex align-items-center"><div class="progress flex-grow-1" style="height:10px;"><div class="progress-bar bg-success js-pct-mao-obra-bar" style="width: {{ $pctMaoObra }}%"></div></div><span class="ml-2 fw-bold text-success js-pct-mao-obra-text">{{ number_format($pctMaoObra, 1, ',', '.') }}%</span></div></td>
                                                <td class="text-end"><div class="fw-bold text-success">R$ <span class="js-table-custo-mao-obra">{{ __moeda($custoMaoObra) }}</span></div></td>
                                            </tr>

                                            <tr>
                                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-warning mr-3" style="width:12px;height:12px;"></div><div><div class="fw-bold">Custos indiretos</div><small class="text-muted">Energia, setup e processo</small></div></div></td>
                                                <td><div class="d-flex align-items-center"><div class="progress flex-grow-1" style="height:10px;"><div class="progress-bar bg-warning js-pct-processo-bar" style="width: {{ $pctProcesso }}%"></div></div><span class="ml-2 fw-bold text-warning js-pct-processo-text">{{ number_format($pctProcesso, 1, ',', '.') }}%</span></div></td>
                                                <td class="text-end"><div class="fw-bold text-warning">R$ <span class="js-table-custo-processo">{{ __moeda($custoProcesso) }}</span></div></td>
                                            </tr>
                                        </tbody>

                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="2"><div class="d-flex align-items-center"><i class="ri-money-dollar-circle-fill text-primary mr-2"></i> Total previsto da produção</div></th>
                                                <th class="text-end"><span class="fs-4 fw-bold text-primary">R$ <span class="js-table-footer-total">{{ __moeda($total) }}</span></span></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-1">Indicadores da produção</h5>
                                        <small class="text-muted">Resumo operacional da OP</small>
                                    </div>
                                    <i class="ri-bar-chart-box-line fs-2 text-primary"></i>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="indicator-box">
                                    <div><small class="text-muted d-block">Tempo previsto</small><strong class="js-tempo-previsto">{{ str_pad($horas, 2, '0', STR_PAD_LEFT) }}h {{ str_pad($minutos, 2, '0', STR_PAD_LEFT) }}min</strong></div>
                                    <div class="indicator-icon bg-primary"><i class="ri-time-line"></i></div>
                                </div>

                                <div class="indicator-box">
                                    <div><small class="text-muted d-block">Quantidade produzir</small><strong class="js-qtd-produzir">{{ __moeda($qtdProduzir) }}</strong></div>
                                    <div class="indicator-icon bg-success"><i class="ri-box-3-line"></i></div>
                                </div>

                                <div class="indicator-box">
                                    <div><small class="text-muted d-block">Custo por unidade</small><strong>R$ <span class="js-custo-unitario">{{ __moeda($custoUnitario) }}</span></strong></div>
                                    <div class="indicator-icon bg-warning"><i class="ri-price-tag-3-line"></i></div>
                                </div>

                                <div class="indicator-box mb-0">
                                    <div><small class="text-muted d-block">Status dos custos</small><strong class="js-status-custos {{ $total > 0 ? 'text-success' : 'text-danger' }}">{{ $total > 0 ? 'Calculado' : 'Não calculado' }}</strong></div>
                                    <div class="indicator-icon bg-dark"><i class="ri-line-chart-line"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-observacoes">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::textarea('observacao', 'Observação')->attrs(['rows' => 8, 'class' => 'tiny']) !!}
                    </div>
                </div>
            </div>

        </div>
    </div>

    @isset($item)
    <input type="hidden" id="_edit" value="1">
    @endisset

    <div class="col-12">
        <hr>
        <div class="op-footer-actions">

            @if(isset($item))
            <a href="{{ route('ordem-producao.simular-custos', $item->id) }}" class="btn btn-info">
                <i class="ri-calculator-line"></i> Simular Custos
            </a>

            @if(!$item->data_liberacao)
            <a href="{{ route('ordem-producao.liberar', $item->id) }}" class="btn btn-primary">
                <i class="ri-checkbox-circle-line"></i> Liberar
            </a>
            @endif

            @if($item->data_liberacao && !$item->data_inicio)
            <a href="{{ route('ordem-producao.iniciar', $item->id) }}" class="btn btn-warning">
                <i class="ri-play-circle-line"></i> Iniciar
            </a>
            @endif

            @if($item->data_inicio && !$item->data_finalizacao)
            <a href="{{ route('ordem-producao.finalizar', $item->id) }}" class="btn btn-success">
                <i class="ri-flag-line"></i> Finalizar
            </a>
            @endif

            @if($item->data_finalizacao && !$item->data_encerramento)
            <a href="{{ route('ordem-producao.encerrar', $item->id) }}" class="btn btn-dark">
                <i class="ri-lock-line"></i> Encerrar
            </a>
            @endif
            @endif

            <button type="submit" class="btn btn-success px-5" id="btn-store">
               Salvar
           </button>
       </div>
   </div>

</div>

@section('js')
<script src="/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({ selector:'textarea.tiny', language:'pt_BR' });

    setTimeout(() => {
        $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none');
    }, 1000);

    $("#select-all-checkbox").on("click", function () {
        $('.check-button').prop('checked', $(this).is(':checked'));
    });
</script>

<script src="/js/ordemProducao.js"></script>
@endsection