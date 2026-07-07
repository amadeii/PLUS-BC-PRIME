@extends('layouts.app', ['title' => 'Agendamentos'])

@section('css')
<style>
    .agenda-page{ margin-top:6px; }
    .agenda-card{ border:0; box-shadow:0 8px 24px rgba(15,23,42,.06); overflow:hidden; }
    .agenda-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding-bottom:16px; border-bottom:1px solid #eef0f4; }
    .agenda-title{ display:flex; align-items:center; gap:12px; }
    .agenda-icon{ width:46px; height:46px; border-radius:14px; background:rgba(var(--bs-primary-rgb),.10); color:var(--bs-primary); display:flex; align-items:center; justify-content:center; font-size:24px; }
    .agenda-title h4{ margin:0; font-weight:800; }
    .agenda-title p{ margin:2px 0 0; color:#8a94a6; font-size:13px; }
    .filter-box{ background:#f8f9fb; border:1px solid #edf0f5; border-radius:16px; padding:14px; margin-top:18px; }
    .calendar-box{ margin-top:18px; background:#fff; border:1px solid #edf0f5; border-radius:18px; padding:14px; }
    .legend-box{ display:flex; flex-wrap:wrap; gap:10px; margin-top:14px; }
    .legend-item{ border-radius:12px; padding:9px 12px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px; }
    .calendario{ min-height:650px; }
    @media(max-width:768px){
        .agenda-header{ align-items:flex-start; }
        .calendar-box{ padding:8px; }
        .calendario{ min-height:520px; }
        .filter-actions{ margin-top:8px; }
        .filter-actions .btn{ width:100%; margin-bottom:6px; }
    }
</style>
@endsection

@section('content')

<div class="agenda-page">

    <input type="hidden" id="agendamentos" value="{{ json_encode($agendamentos) }}">

    <div class="card agenda-card">
        <div class="card-body">

            <div class="agenda-header">
                <div class="agenda-title">
                    <div class="agenda-icon">
                        <i class="ri-calendar-check-line"></i>
                    </div>

                    <div>
                        <h4>Agendamentos</h4>
                        <p>Controle visual dos horários, prioridades e atendimentos</p>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2">
                        <i class="ri-calendar-line me-1"></i>
                        Calendário
                    </span>
                </div>
            </div>

            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="filter-box">
                <div class="row g-2 align-items-end">

                    <div class="col-md-4">
                        {!! Form::select('funcionario_id', 'Atendente', ['' => 'Todos'] + $funcionarios->pluck('nome', 'id')->all())
                        ->id('funcionario')
                        ->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-4 filter-actions">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i> Pesquisar
                        </button>

                        <a id="clear-filter" class="btn btn-danger" href="{{ route('agendamentos.index') }}">
                            <i class="ri-eraser-fill"></i> Limpar
                        </a>
                    </div>

                </div>
            </div>
            {!! Form::close() !!}

            <div class="calendar-box">
                <div id="external-events"></div>
                <div id="calendar" class="calendario"></div>
            </div>

            <div class="legend-box">
                <div class="legend-item bg-success-subtle text-success" data-class="bg-success">
                    <i class="ri-checkbox-circle-line"></i> Finalizado
                </div>

                <div class="legend-item bg-primary-subtle text-primary" data-class="bg-primary">
                    <i class="ri-arrow-down-circle-line"></i> Prioridade baixa
                </div>

                <div class="legend-item bg-warning-subtle text-warning" data-class="bg-warning">
                    <i class="ri-alert-line"></i> Prioridade média
                </div>

                <div class="legend-item bg-danger-subtle text-danger" data-class="bg-danger">
                    <i class="ri-fire-line"></i> Prioridade alta
                </div>
            </div>

        </div>
    </div>
</div>

<input type="hidden" id="create_permission" value="@can('agendamento_create') 1 @else 0 @endcan">

@include('modals._agendamento')

@endsection

@section('js')
<script src="/assets/vendor/fullcalendar/main.min.js"></script>
<script src="/js/calendar.js"></script>
<script src="/js/agendamento.js"></script>
@endsection