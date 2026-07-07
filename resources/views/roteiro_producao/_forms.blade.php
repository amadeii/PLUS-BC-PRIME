<div class="row g-2">

    <div class="col-md-4">
        {!!Form::text('nome', 'Nome do roteiro')->required()!!}
    </div>

    <div class="col-md-4">
        {!!Form::select('produto_id', 'Produto', ['' => 'Selecione'] + $produtos->pluck('nome', 'id')->all())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('ativo', 'Status', [
            1 => 'Ativo',
            0 => 'Inativo'
        ])->required()!!}
    </div>

    <div class="col-md-12">
        {!!Form::textarea('descricao', 'Descrição')
        ->attrs(['rows' => 3])
        !!}
    </div>

    <hr class="mt-4">

    <div class="col-md-12">
        <h5>Operações do Roteiro</h5>
    </div>

    <div class="col-md-12">
        <div class="table-responsive-sm">
            <table class="table table-striped table-centered mb-0" id="table-roteiro">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">Seq.</th>
                        <th width="25%">Operação</th>
                        <th width="25%">Setor</th>
                        <th width="15%">Tempo/min</th>
                        <th>Observação</th>
                        <th width="8%">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($roteiro) && $roteiro->itens->count())
                        @foreach($roteiro->itens as $i => $item)
                        <tr>
                            <td>
                                <input type="number" name="sequencia[]" class="form-control" value="{{ $item->sequencia }}">
                            </td>
                            <td>
                                <select name="operacao_id[]" class="form-select select-operacao">
                                    <option value="">Selecione</option>
                                    @foreach($operacoes as $op)
                                    <option value="{{ $op->id }}" data-nome="{{ $op->nome }}" @if($item->operacao_id == $op->id) selected @endif>
                                        {{ $op->nome }}
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="nome_operacao[]" value="{{ $item->nome_operacao }}">
                            </td>
                            <td>
                                <select name="setor_id[]" class="form-select select-setor">
                                    <option value="">Selecione</option>
                                    @foreach($setores as $setor)
                                    <option value="{{ $setor->id }}" data-nome="{{ $setor->nome }}" @if($item->setor_id == $setor->id) selected @endif>
                                        {{ $setor->nome }}
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="nome_setor[]" value="{{ $item->nome_setor }}">
                            </td>
                            <td>
                                <input type="number" name="tempo_previsto_minutos[]" class="form-control" value="{{ $item->tempo_previsto_minutos }}">
                            </td>
                            <td>
                                <input type="text" name="observacao_item[]" class="form-control" value="{{ $item->observacao }}">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-remove-linha">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                <input type="number" name="sequencia[]" class="form-control" value="10">
                            </td>
                            <td>
                                <select name="operacao_id[]" class="form-select select-operacao">
                                    <option value="">Selecione</option>
                                    @foreach($operacoes as $op)
                                    <option value="{{ $op->id }}" data-nome="{{ $op->nome }}">{{ $op->nome }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="nome_operacao[]" value="">
                            </td>
                            <td>
                                <select name="setor_id[]" class="form-select select-setor">
                                    <option value="">Selecione</option>
                                    @foreach($setores as $setor)
                                    <option value="{{ $setor->id }}" data-nome="{{ $setor->nome }}">{{ $setor->nome }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="nome_setor[]" value="">
                            </td>
                            <td>
                                <input type="number" name="tempo_previsto_minutos[]" class="form-control" value="0">
                            </td>
                            <td>
                                <input type="text" name="observacao_item[]" class="form-control">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-remove-linha">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <button type="button" class="btn btn-dark" id="btn-add-operacao">
            <i class="ri-add-line"></i>
            Adicionar Operação
        </button>
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>

</div>

@section('js')
<script>
    function atualizarNomesLinha(tr){
        let op = tr.find('.select-operacao option:selected');
        let setor = tr.find('.select-setor option:selected');

        tr.find('input[name="nome_operacao[]"]').val(op.data('nome') || '');
        tr.find('input[name="nome_setor[]"]').val(setor.data('nome') || '');
    }

    $(document).on('change', '.select-operacao, .select-setor', function(){
        atualizarNomesLinha($(this).closest('tr'));
    });

    $('#btn-add-operacao').on('click', function(){
        let tbody = $('#table-roteiro tbody');
        let total = tbody.find('tr').length;
        let novaSequencia = (total + 1) * 10;

        let linha = tbody.find('tr:first').clone();

        linha.find('input').val('');
        linha.find('select').val('');
        linha.find('input[name="sequencia[]"]').val(novaSequencia);
        linha.find('input[name="tempo_previsto_minutos[]"]').val(0);

        tbody.append(linha);
    });

    $(document).on('click', '.btn-remove-linha', function(){
        if($('#table-roteiro tbody tr').length > 1){
            $(this).closest('tr').remove();
        }
    });

    $('#table-roteiro tbody tr').each(function(){
        atualizarNomesLinha($(this));
    });
</script>
@endsection