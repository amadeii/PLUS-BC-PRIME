<div class="row g-2">
	<div class="col-md-6">
		{!!Form::text('produto_nome', 'Produto')
		->attrs(['readonly' => true])
		->value($produto->nome)
		!!}
	</div>

    <div class="col-md-2">
    	{!!Form::tel('lote_minimo', 'Lote mínimo')
    	->required()
    	->attrs(['data-mask' => '00000'])
    	->value(isset($item) ? number_format($item->lote_minimo, 0) : '1')
    	!!}
    </div>

    <div class="row g-3">

    	{{-- Upload --}}
    	<div class="col-md-6">
    		<div class="upload-box">

    			<div class="upload-content">
    				<i class="ri-upload-cloud-2-line"></i>
    				<h6>Imagem / desenho</h6>
    				<p class="text-muted">Clique ou arraste um JPG/PNG</p>
    			</div>

    			{!! Form::file('imagem')
    			->attrs([
    			'accept' => '.jpg,.jpeg,.png',
    			'class' => 'upload-input',
    			'onchange' => 'previewFileName(this)'
    			])
    			!!}

    			<small class="file-name text-muted">Nenhum arquivo selecionado</small>

    		</div>
    	</div>

    	{{-- Imagem atual --}}
    	<div class="col-md-6">
    		<div class="image-preview-box">

    			<div class="d-flex justify-content-between align-items-center mb-2">
    				<h6 class="mb-0">Imagem atual</h6>
    				@if(isset($item) && $item->imagem)
    				<a href="{{ $item->img }}" 
    					target="_blank"
    					class="btn btn-sm btn-primary btn-soft">
    					<i class="ri-eye-line"></i> Ver
    				</a>
    				@endif
    			</div>

    			@if(isset($item) && $item->imagem)
    			<div class="image-thumb">
    				<img src="{{ $item->img }}">
    			</div>
    			@else
    			<div class="empty-state">
    				<i class="ri-image-line"></i>
    				<span>Nenhuma imagem enviada</span>
    			</div>
    			@endif

    		</div>
    	</div>

    </div>

    <div class="col-md-12 mt-4">
    	<h5>Lista de Materiais</h5>

    	<div class="table-responsive">
    		<table class="table table-striped table-hover">
    			<thead class="table-light">
    				<tr>
    					<th style="width:120px">Código</th>
    					<th>Descrição</th>
    					<th style="width:120px">Qtde</th>
    					<th style="width:120px">Qtde Total</th>
    					<th style="width:80px">Unidade</th>
    					<th style="width:200px">Categoria</th>
    				</tr>
    			</thead>
    			<tbody>
    				@forelse($composicaoRecursiva as $c)
    				@if($c->ingrediente)
    				<tr>
    					<td>
    						<span class="badge bg-dark">
    							{{ $c->ingrediente->numero_sequencial ?? '--' }}
    						</span>
    					</td>

    					<td>
    						<div style="padding-left: {{ $c->nivel * 25 }}px;">
    							@if($c->nivel > 0)
    							<span class="text-muted me-1">↳</span>
    							@endif
    							<strong>{{ $c->ingrediente->nome }}</strong>
    						</div>
    					</td>

    					<td>
    						{{ number_format($c->quantidade, 2, ',', '.') }}
    					</td>

    					<td>
    						<span class="badge bg-primary">
    							{{ number_format($c->quantidade_calculada, 2, ',', '.') }}
    						</span>
    					</td>

    					<td>
    						{{ $c->ingrediente->unidade ?? 'un' }}
    					</td>

    					<td>
    						<span class="badge bg-info">
    							{{ $c->ingrediente->categoria->nome ?? 'Sem categoria' }}
    						</span>
    					</td>
    				</tr>
    				@endif
    				@empty
    				<tr>
    					<td colspan="6" class="text-center text-muted">
    						Nenhum material vinculado (composição do produto)
    					</td>
    				</tr>
    				@endforelse
    			</tbody>
    		</table>
    	</div>
    </div>

    <div class="col-md-12 mt-3">
    	<h5>Roteiro de Produção</h5>
    	<div class="table-responsive">
    		<table class="table table-striped" id="table-operacoes">
    			<thead class="table-light">
    				<tr>
    					<th>Operação</th>
    					<th>Descrição</th>
    					<th>Tempo</th>
    					<th>Setup</th>
    					<th>Setor</th>
    					<th>C. Custo</th>
    					<th></th>
    				</tr>
    			</thead>
    			<tbody>

    				@if(isset($item) && sizeof($item->operacoes) > 0)
    				@foreach($item->operacoes as $op)
    				<tr>

    					<td>
    						<select name="operacao_id[]" class="form-select operacao-select">
    							<option value="">Selecione</option>
    							@foreach($operacoes as $o)
    							<option value="{{ $o->id }}" {{ $op->operacao_id == $o->id ? 'selected' : '' }}>
    								{{ $o->nome }}
    							</option>
    							@endforeach
    						</select>
    					</td>

    					<td>
    						<input type="text" name="descricao[]" class="form-control descricao-input" value="{{ $op->descricao }}">
    					</td>

    					<td>
    						<input type="number" name="tempo_minutos[]" class="form-control tempo-input" value="{{ $op->tempo_minutos }}">
    					</td>

    					<td>
    						<input type="number" name="setup_minutos[]" class="form-control setup-input" value="{{ $op->setup_minutos }}">
    					</td>

    					<td>
    						<select name="setor_id[]" class="form-select setor-select">
    							<option value="">Selecione</option>
    							@foreach($setores as $s)
    							<option value="{{ $s->id }}" {{ $op->setor_id == $s->id ? 'selected' : '' }}>
    								{{ $s->nome }}
    							</option>
    							@endforeach
    						</select>
    					</td>

    					<td>
    						<select name="centro_custo_id[]" class="form-select centro-custo-select">
    							<option value="">Selecione</option>
    							@foreach($centroCustos as $cc)
    							<option value="{{ $cc->id }}" {{ $op->centro_custo_id == $cc->id ? 'selected' : '' }}>
    								{{ $cc->nome }}
    							</option>
    							@endforeach
    						</select>
    					</td>

    					<td>
    						<button type="button" class="btn btn-danger btn-sm remove-row">
    							<i class="ri-close-line"></i>
    						</button>
    					</td>
    				</tr>
    				@endforeach
    				@else
    				<tr>

    					<td>
    						<select name="operacao_id[]" class="form-select operacao-select">
    							<option value="">Selecione</option>
    							@foreach($operacoes as $o)
    							<option value="{{ $o->id }}">{{ $o->nome }}</option>
    							@endforeach
    						</select>
    					</td>

    					<td><input type="text" name="descricao[]" class="form-control descricao-input"></td>
    					<td><input type="number" name="tempo_minutos[]" class="form-control tempo-input" value="0"></td>
    					<td><input type="number" name="setup_minutos[]" class="form-control setup-input" value="0"></td>

    					<td>
    						<select name="setor_id[]" class="form-select setor-select">
    							<option value="">Selecione</option>
    							@foreach($setores as $s)
    							<option value="{{ $s->id }}">{{ $s->nome }}</option>
    							@endforeach
    						</select>
    					</td>

    					<td>
    						<select name="centro_custo_id[]" class="form-select centro-custo-select">
    							<option value="">Selecione</option>
    							@foreach($centroCustos as $cc)
    							<option value="{{ $cc->id }}">{{ $cc->nome }}</option>
    							@endforeach
    						</select>
    					</td>

    					<td>
    						<button type="button" class="btn btn-danger btn-sm remove-row">
    							<i class="ri-close-line"></i>
    						</button>
    					</td>
    				</tr>
    				@endif

    			</tbody>
    		</table>
    	</div>

    	<button type="button" class="btn btn-primary btn-sm" id="add-operacao">
    		<i class="ri-add-line"></i> Adicionar Operação
    	</button>
    </div>

    <div class="col-md-12 mt-4">
    	{!!Form::textarea('instrucoes_especiais', 'Instruções Especiais')
    	->attrs(['rows' => '8', 'class' => 'tiny'])
    	->value(isset($item) ? $item->instrucoes_especiais : '')
    	!!}
    </div>

    <div class="col-md-12 mt-3">
    	{!!Form::textarea('checklist_texto', 'Checklist de Qualidade')
    	->attrs(['rows' => '8', 'class' => 'tiny'])
    	->value(isset($item) ? $item->checklist_texto : '')
    	!!}
    </div>

    <div class="col-md-12 mt-3">
    	{!!Form::textarea('assinaturas', 'Assinaturas')
    	->attrs(['rows' => '8', 'class' => 'tiny'])
    	->value(isset($item) ? $item->assinaturas : '')
    	!!}
    </div>

    <div class="col-12 mt-3" style="text-align: right;">
    	<button type="submit" class="btn btn-success px-5">Salvar</button>
    </div>
</div>

@section('js')
<script src="/tinymce/tinymce.min.js"></script>

<script>
	$(function(){

		tinymce.init({ selector: 'textarea.tiny', language: 'pt_BR' });

		setTimeout(() => {
			$('.tox-promotion, .tox-statusbar__right-container').addClass('d-none');
		}, 500);


		$('#add-operacao').on('click', function(){
			let linha = `
			<tr>

			<td>
			<select name="operacao_id[]" class="form-select operacao-select">
			<option value="">Selecione</option>
			@foreach($operacoes as $o)
			<option value="{{ $o->id }}">{{ $o->nome }}</option>
			@endforeach
			</select>
			</td>

			<td>
			<input type="text" name="descricao[]" class="form-control descricao-input">
			</td>

			<td>
			<input type="number" name="tempo_minutos[]" class="form-control tempo-input" value="0">
			</td>

			<td>
			<input type="number" name="setup_minutos[]" class="form-control setup-input" value="0">
			</td>

			<td>
			<select name="setor_id[]" class="form-select setor-select">
			<option value="">Selecione</option>
			@foreach($setores as $s)
			<option value="{{ $s->id }}">{{ $s->nome }}</option>
			@endforeach
			</select>
			</td>

			<td>
			<select name="centro_custo_id[]" class="form-select centro-custo-select">
			<option value="">Selecione</option>
			@foreach($centroCustos as $cc)
			<option value="{{ $cc->id }}">{{ $cc->nome }}</option>
			@endforeach
			</select>
			</td>

			<td>
			<button type="button" class="btn btn-danger btn-sm remove-row">
			<i class="ri-close-line"></i>
			</button>
			</td>

			</tr>
			`;
			$('#table-operacoes tbody').append(linha);
		});

		$(document).on('click', '.remove-row', function(){
			$(this).closest('tr').remove();
		});

		$(document).on('change', '.operacao-select', function(){

			let operacaoId = $(this).val();
			let tr = $(this).closest('tr');

			if(!operacaoId){
				tr.find('.descricao-input').val('');
				tr.find('.tempo-input').val(0);
				tr.find('.setor-select').val('');
				tr.find('.centro-custo-select').val('');
				return;
			}

			$.get(path_url + 'api/operacoes/find/' + operacaoId, function(res){

				if(res.success){

					let item = res.item;

					tr.find('.descricao-input').val(item.descricao ?? '');
					tr.find('.tempo-input').val(item.tempo_padrao ?? 0);

					tr.find('.setor-select').val(item.setor_id).trigger('change');
					tr.find('.centro-custo-select').val(item.centro_custo_id).trigger('change');

				}else{
					swal("Erro", "Operação não encontrada", "error");
				}

			}).fail(function(){
				swal("Erro", "Erro ao buscar operação", "error");
			});

		});
	});


	function previewFileName(input) {
		const fileName = input.files[0]?.name || 'Nenhum arquivo selecionado';
		input.closest('.upload-box')
		.querySelector('.file-name')
		.innerText = fileName;
	}
</script>
@endsection