<div class="row g-3">

	<div class="col-md-6">
		<label class="">Funcionário</label>

		<select name="funcionario_id" class="form-select @error('funcionario_id') is-invalid @enderror" required>

			<option value="">Selecione</option>

			@foreach($funcionarios as $f)
			<option value="{{ $f->id }}"
				@if(old('funcionario_id') == $f->id) selected @endif>
				{{ $f->nome }}
			</option>
			@endforeach
		</select>

		@error('funcionario_id')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-md-3">
		<label class="required">Data início</label>

		<input type="datetime-local"
		name="data_inicio"
		value="{{ old('data_inicio') }}"
		class="form-control @error('data_inicio') is-invalid @enderror"
		required>

		@error('data_inicio')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-md-3">
		<label>Data fim</label>

		<input type="datetime-local"
		name="data_fim"
		value="{{ old('data_fim') }}"
		class="form-control @error('data_fim') is-invalid @enderror">

		@error('data_fim')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-md-4">
		<label class="required">Qtd produzida</label>

		<input type="text"
		name="quantidade_produzida"
		value="{{ old('quantidade_produzida') }}"
		class="form-control @error('quantidade_produzida') is-invalid @enderror qtd"
		required>

		@error('quantidade_produzida')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-md-4">
		<label>Qtd refugada</label>

		<input type="text"
		name="quantidade_refugada"
		value="{{ old('quantidade_refugada') }}"
		class="form-control @error('quantidade_refugada') is-invalid @enderror qtd">

		@error('quantidade_refugada')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-md-4">
		<label>Motivo refugo</label>

		<select name="motivo_refugo_id" class="form-select @error('motivo_refugo_id') is-invalid @enderror">

			<option value="">Selecione</option>

			@foreach($motivosRefugo as $m)
			<option value="{{ $m->id }}"
				@if(old('motivo_refugo_id') == $m->id) selected @endif>
				{{ $m->nome }}
			</option>
			@endforeach
		</select>

		@error('motivo_refugo_id')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-12">
		<label>Observação</label>

		<textarea name="observacao"
		rows="3"
		class="form-control @error('observacao') is-invalid @enderror">{{ old('observacao') }}</textarea>

		@error('observacao')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>

	<div class="col-12 text-end">
		<button class="btn btn-success px-4">
			<i class="ri-check-double-line"></i>
			Registrar apontamento
		</button>
	</div>

</div>