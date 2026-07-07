@extends('layouts.app', ['title' => 'Ajustar Registro'])

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                {{-- INFORMAÇÕES ATUAIS --}}
                <div class="mb-4">
                    <h5 class="mb-3">Registro Atual</h5>

                    <div style="text-align: right; margin-top: -35px;">
                        <a href="{{ route('ponto-registro.show', $registro->id) }}" class="btn btn-danger btn-sm px-3">
                            <i class="ri-arrow-left-double-fill"></i>Voltar
                        </a>
                    </div>

                    <div class="row g-2">

                        <div class="col-md-3">
                            <strong>Funcionário</strong><br>
                            {{ $registro->funcionario->nome ?? '' }}
                        </div>

                        <div class="col-md-3">
                            <strong>Tipo</strong><br>
                            {{ $registro->tipo }}
                        </div>

                        <div class="col-md-3">
                            <strong>Data/Hora</strong><br>
                            {{ \Carbon\Carbon::parse($registro->data_hora)->format('d/m/Y H:i') }}
                        </div>

                        <div class="col-md-3">
                            <strong>Status</strong><br>
                            {{ $registro->status }}
                        </div>

                    </div>
                </div>

                <hr>

                {{-- FORM --}}
                {!!Form::open()
                ->post()
                ->route('ponto-ajuste.store', [$registro->id])
                !!}

                <div class="row g-2 mt-2">

                    <div class="col-md-3">
                        {!!Form::select('tipo', 'Novo Tipo', [
                        'entrada' => 'Entrada',
                        'intervalo_inicio' => 'Intervalo Início',
                        'intervalo_fim' => 'Intervalo Fim',
                        'saida' => 'Saída'
                        ])
                        ->value($registro->tipo)
                        ->required()
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Nova Data/Hora</label>
                        <input 
                        type="datetime-local" 
                        name="data_hora" 
                        class="form-control" 
                        value="{{ \Carbon\Carbon::parse($registro->data_hora)->format('Y-m-d\TH:i') }}"
                        required
                        >
                    </div>

                    <div class="col-md-3">
                        {!!Form::text('motivo', 'Motivo')
                        ->required()
                        !!}
                    </div>

                    <div class="col-md-12">
                        {!!Form::textarea('justificativa', 'Justificativa')
                        ->attrs(['rows' => 3])
                        !!}
                    </div>

                    <hr class="mt-4">

                    <div class="col-12 text-end">

                        <button type="submit" class="btn btn-success px-5">
                            Salvar Ajuste
                        </button>
                    </div>

                </div>

                {!!Form::close()!!}

            </div>
        </div>
    </div>
</div>
@endsection