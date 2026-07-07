@extends('layouts.app', ['title' => 'Movimentações conta ' . $item->nome])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
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
                            {!!Form::date('end_date', 'Data Final')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tipo', 'Tipo', ['' => 'Todos', 'entrada' => 'Entrada', 'saida' => 'Saída'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('contas-empresa.show', [$item->id]) }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                <div style="text-align: right; margin-top: -35px;">
                    <form method="get" action="{{ route('contas-empresa.print', [$item->id]) }}">

                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
                        <button class="btn btn-dark btn-sm px-3">
                            <i class="ri-printer-line"></i> Imprimir
                        </button>
                    </form>
                </div>
                <br>
                
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="card-body">

                            <div class="mb-3">
                                <h5 class="mb-0" style="font-weight: 600;">
                                    <i class="ri-bank-line text-primary me-1"></i>
                                    Conta da Empresa
                                </h5>
                                <small class="text-muted">Dados bancários vinculados</small>
                            </div>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="text-muted" style="font-size: 12px;">Conta</div>
                                    <strong class="text-dark">{{ $item->nome }}</strong>
                                </div>

                                <div class="col-md-3">
                                    <div class="text-muted" style="font-size: 12px;">Banco</div>
                                    <strong class="text-dark">{{ $item->banco }}</strong>
                                </div>

                                <div class="col-md-3">
                                    <div class="text-muted" style="font-size: 12px;">Agência</div>
                                    <strong class="text-dark">{{ $item->agencia }}</strong>
                                </div>

                                <div class="col-md-3">
                                    <div class="text-muted" style="font-size: 12px;">Conta corrente</div>
                                    <strong class="text-dark">{{ $item->conta }}</strong>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    @forelse($data as $m)

                    <div class="row py-2 align-items-center" style="border-bottom: 1px solid #f1f1f1;">

                        <div class="col-md-2 text-muted" style="font-size: 13px;">
                            {{ __data_pt($m->created_at) }}
                        </div>

                        <div class="col-md-2 text-dark" style="font-weight: 500;">
                            {{ $m->fornecedor->razao_social ?? $m->cliente->razao_social ?? '--' }}
                        </div>

                        <div class="col-md-4 col-12 text-muted" style="font-size: 13px;">
                            {{ $m->descricao }}

                            @if($m->caixa_id)
                            <br>
                            <small class="text-dark">
                                <i class="ri-inbox-archive-line"></i>
                                Caixa {{ __data_pt($m->caixa->created_at) }}
                            </small>
                            @endif
                        </div>

                        <div class="col-md-2 col-12 text-md-end">
                            <div style="display: inline-block;padding: 6px 10px;border-radius: 10px;font-size: 12px;font-weight: 600;background: @if($m->tipo == 'entrada') #eafff1 @else #fff3f3 @endif;color: @if($m->tipo == 'entrada') #1ea75a @else #d92d20 @endif;border: 1px solid @if($m->tipo == 'entrada') #b6f0c8 @else #ffd6d6 @endif;">
                                {{ $m->tipo_pagamento ? App\Models\Nfce::getTipoPagamento($m->tipo_pagamento) : '--' }}
                            </div>
                        </div>

                        <div class="col-md-2 col-12 @if($m->tipo == 'entrada') text-success @else text-danger @endif text-md-end">
                            <strong style="font-size: 15px;">
                                @if($m->tipo == 'entrada')+@else-@endif 
                                R$ {{ __moeda($m->valor) }}
                            </strong>
                        </div>

                    </div>

                    <div class="row pb-2 mb-2" style="border-bottom: 1px dashed #eaeaea;">

                        <div class="col-md-2">
                            @if($m->categoria)
                            <small class="text-muted">
                                Categoria <strong class="text-dark">{{ $m->categoria->nome }}</strong>
                            </small>
                            @endif
                        </div>

                        <div class="col-md-8">
                            @if($m->numero_documento)
                            <small class="text-muted">
                                Nº Doc <strong class="text-dark">{{ $m->numero_documento }}</strong>
                            </small>
                            @endif
                        </div>

                        <div class="col-md-2 text-md-end">
                            <span style="font-size: 13px;" class="text-muted">Saldo</span><br>
                            <strong class="@if($m->saldo_atual <= 0) text-danger @else text-primary @endif" style="font-size: 16px;">
                                R$ {{ __moeda($m->saldo_atual) }}
                            </strong>
                        </div>

                    </div>

                    @empty

                    <div class="text-center mt-4">
                        <i class="ri-inbox-line" style="font-size: 40px; color: #ccc;"></i>
                        <h5 class="text-muted mt-2">Nenhuma movimentação encontrada</h5>
                    </div>

                    @endforelse
                </div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
