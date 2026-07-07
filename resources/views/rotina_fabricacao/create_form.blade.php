@extends('layouts.app', ['title' => 'Nova Rotina de Fabricação'])
@section('css')
<style type="text/css">
	/* UPLOAD */
	.upload-box {
		position: relative;
		border: 2px dashed #CBD5E1;
		border-radius: 14px;
		padding: 30px 20px;
		text-align: center;
		background: #F9FAFB;
		transition: all 0.25s ease;
		cursor: pointer;
		height: 100%;
	}

	.upload-box:hover {
		border-color: #5B5BD6;
		background: #F4F5FF;
		transform: translateY(-2px);
	}

	.upload-content i {
		font-size: 34px;
		color: #5B5BD6;
		margin-bottom: 8px;
	}

	.upload-content h6 {
		font-weight: 600;
		margin-bottom: 2px;
	}

	.upload-content p {
		font-size: 13px;
		margin: 0;
	}

	.upload-input {
		position: absolute;
		inset: 0;
		opacity: 0;
		cursor: pointer;
	}

	.file-name {
		display: block;
		margin-top: 10px;
		font-size: 12px;
	}

/* PREVIEW */
.image-preview-box {
	border: 1px solid #E5E7EB;
	border-radius: 14px;
	padding: 15px;
	background: #fff;
	height: 100%;
}

.image-thumb {
	width: 100%;
	height: 180px;
	border-radius: 10px;
	overflow: hidden;
	border: 1px solid #E5E7EB;
}

.image-thumb img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

/* EMPTY */
.empty-state {
	height: 180px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
	color: #94A3B8;
}

.empty-state i {
	font-size: 30px;
	margin-bottom: 5px;
}

/* BOTÃO SUAVE */
.btn-soft {
	background: #EEF2FF;
	color: #5B5BD6;
	border: none;
}

.btn-soft:hover {
	background: #5B5BD6;
	color: #fff;
}
</style>
@endsection
@section('content')

<div class="card mt-1">
	<div class="card-header">
		<h4>Nova Rotina de Fabricação</h4>

		<div style="text-align: right; margin-top: -35px;">
			<a href="{{ route('rotina-fabricacao.create') }}" class="btn btn-danger btn-sm px-3">
				<i class="ri-arrow-left-double-fill"></i>Voltar
			</a>
		</div>
	</div>
	<div class="card-body">

		{!!Form::open()->post()->route('rotina-fabricacao.store')->multipart()!!}
		<input type="hidden" name="produto_id" value="{{ $produto->id }}">
		<div class="pl-lg-4">
			@include('rotina_fabricacao._forms')
		</div>
		{!!Form::close()!!}

	</div>
</div>
@endsection