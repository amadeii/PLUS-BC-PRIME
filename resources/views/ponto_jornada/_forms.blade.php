<div class="row g-2">
    <div class="col-md-4">
        {!!Form::text('descricao', 'Descrição')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('intervalo_minutos', 'Intervalo (min)')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('tolerancia_atraso', 'Tolerância atraso (min)')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('hora_extra_apos_minutos', 'Hora extra após (min)')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('ativo', 'Status', [1 => 'Ativo', 0 => 'Inativo'])
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-12 mt-4">
        <h5>Dias da Jornada</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Dia</th>
                        <th>Entrada</th>
                        <th>Início Intervalo</th>
                        <th>Fim Intervalo</th>
                        <th>Saída</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $diasSemana = [
                    1 => 'Segunda',
                    2 => 'Terça',
                    3 => 'Quarta',
                    4 => 'Quinta',
                    5 => 'Sexta',
                    6 => 'Sábado',
                    0 => 'Domingo'
                    ];

                    $diasExistentes = [];
                    if(isset($item)){
                        foreach($item->dias as $d){
                            $diasExistentes[$d->dia_semana] = $d;
                        }
                    }

                    function horaInput($valor){
                        return !empty($valor) ? substr($valor, 0, 5) : '';
                    }
                    @endphp

                    @foreach($diasSemana as $numero => $dia)
                    <tr>
                        <td>
                            <input type="hidden" name="dia_semana[]" value="{{ $numero }}">
                            <strong>{{ $dia }}</strong>
                        </td>
                        <td>
                            <input 
                            type="time" 
                            name="entrada[]" 
                            class="form-control"
                            value="{{ isset($diasExistentes[$numero]) ? horaInput($diasExistentes[$numero]->entrada) : '' }}">
                        </td>
                        <td>
                            <input 
                            type="time" 
                            name="intervalo_inicio[]" 
                            class="form-control"
                            value="{{ isset($diasExistentes[$numero]) ? horaInput($diasExistentes[$numero]->intervalo_inicio) : '' }}">
                        </td>
                        <td>
                            <input 
                            type="time" 
                            name="intervalo_fim[]" 
                            class="form-control"
                            value="{{ isset($diasExistentes[$numero]) ? horaInput($diasExistentes[$numero]->intervalo_fim) : '' }}">
                        </td>
                        <td>
                            <input 
                            type="time" 
                            name="saida[]" 
                            class="form-control"
                            value="{{ isset($diasExistentes[$numero]) ? horaInput($diasExistentes[$numero]->saida) : '' }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>