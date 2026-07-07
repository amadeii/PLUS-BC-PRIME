@extends('layouts.app', ['title' => 'Teste de Envio de E-mail'])
@section('content')

<div class="card mt-1">
	<div class="card-header">
		<h4>Teste de Envio de E-mail</h4>
		<div style="text-align: right; margin-top: -35px;">
			<a href="{{ url()->previous() }}" class="btn btn-danger btn-sm px-3">
				<i class="ri-arrow-left-double-fill"></i> Voltar
			</a>
		</div>
	</div>

	<div class="card-body">

		@if(session('mensagem_sucesso'))
		<div class="alert alert-success">
			{{ session('mensagem_sucesso') }}
		</div>
		@endif

		@if(session('mensagem_erro'))
		<div class="alert alert-danger">
			{{ session('mensagem_erro') }}
		</div>
		@endif

		@if($errors->any())
		<div class="alert alert-danger">
			@foreach($errors->all() as $erro)
			<div>{{ $erro }}</div>
			@endforeach
		</div>
		@endif

		<div class="row g-2 mb-4">
			<div class="col-md-12">
				<div class="card border">
					<div class="card-header bg-light">
						<h5 class="mb-0">
							<i class="ri-settings-3-line"></i> Configurações atuais do e-mail
						</h5>
					</div>

					<div class="card-body">
						<div class="row g-2">
							<div class="col-md-3">
								<label class="form-label">Mailer</label>
								<input type="text" class="form-control" value="{{ $configEmail['mailer'] }}" disabled>
							</div>

							<div class="col-md-3">
								<label class="form-label">Host</label>
								<input type="text" class="form-control" value="{{ $configEmail['host'] }}" disabled>
							</div>

							<div class="col-md-2">
								<label class="form-label">Porta</label>
								<input type="text" class="form-control" value="{{ $configEmail['port'] }}" disabled>
							</div>

							<div class="col-md-2">
								<label class="form-label">Criptografia</label>
								<input type="text" class="form-control" value="{{ $configEmail['encryption'] }}" disabled>
							</div>

							<div class="col-md-2">
								<label class="form-label">Senha</label>
								<input type="text" class="form-control" value="{{ $configEmail['password'] }}" disabled>
							</div>

							<div class="col-md-4">
								<label class="form-label">Usuário SMTP</label>
								<input type="text" class="form-control" value="{{ $configEmail['username'] }}" disabled>
							</div>

							<div class="col-md-4">
								<label class="form-label">E-mail remetente</label>
								<input type="text" class="form-control" value="{{ $configEmail['from_address'] }}" disabled>
							</div>

							<div class="col-md-4">
								<label class="form-label">Nome remetente</label>
								<input type="text" class="form-control" value="{{ $configEmail['from_name'] }}" disabled>
							</div>
						</div>

						<small class="text-muted d-block mt-3">
							Esses dados vêm do arquivo .env. A senha é ocultada por segurança.
						</small>
					</div>
				</div>
			</div>
		</div>

		{!! Form::open()->post()->route('teste-email.enviar') !!}

		<div class="pl-lg-4">
			<div class="row g-2">

				<div class="col-md-6">
					<label class="form-label">E-mail de destino</label>
					<input type="email" name="email_destino" class="form-control" value="{{ old('email_destino') }}" placeholder="cliente@email.com" required>
				</div>

				<div class="col-md-6">
					<label class="form-label">Assunto</label>
					<input type="text" name="assunto" class="form-control" value="{{ old('assunto', 'Teste de envio de e-mail') }}" placeholder="Assunto do e-mail" required>
				</div>

				<div class="col-md-12">
					<label class="form-label">Texto do e-mail</label>
					<textarea name="texto" class="form-control" rows="6" placeholder="Digite o conteúdo do e-mail" required>{{ old('texto', 'Este é um e-mail de teste enviado pelo sistema.') }}</textarea>
				</div>

				<hr class="mt-4">

				<div class="col-12" style="text-align: right;">
					<button type="submit" class="btn btn-success px-5" id="btn-store">
						<i class="ri-send-plane-fill"></i> Enviar Teste
					</button>
				</div>

			</div>
		</div>

		{!! Form::close() !!}

	</div>
</div>

@endsection