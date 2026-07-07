@extends('layouts.app', ['title' => 'Visualizando Boleto'])

@section('css')
<style>
.boleto-show-card{border:none;border-radius:22px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,.06);}
.boleto-header{padding:20px 24px;border-bottom:1px solid #eef0f6;background:#fbfcff;}
.boleto-title{font-size:22px;font-weight:800;color:#1e293b;margin:0;}
.boleto-subtitle{font-size:13px;color:#94a3b8;margin-top:2px;}
.boleto-body{padding:24px;}
.boleto-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;}
.info-box{border:1px solid #eef0f6;border-radius:18px;padding:18px;background:#fff;}
.info-label{font-size:12px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:6px;}
.info-value{font-size:18px;font-weight:800;color:#1e293b;line-height:1.3;}
.info-value.success{color:#16a34a;}
.boleto-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;}
</style>
@endsection
@section('content')
<div class="card boleto-show-card mt-2">
    
    <div class="boleto-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="boleto-title">Visualizando Boleto</h4>
            <div class="boleto-subtitle">
                Informações completas do boleto gerado
            </div>
        </div>

        <a href="{{ route('boleto.index') }}" class="btn btn-danger px-4">
            <i class="ri-arrow-left-line"></i>
            Voltar
        </a>
    </div>

    <div class="boleto-body">

        <div class="boleto-grid">

            <div class="info-box">
                <div class="info-label">Cliente</div>
                <div class="info-value">
                    {{ $item->contaReceber->cliente->info ?? 'Sem cliente' }}
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">Valor</div>
                <div class="info-value success">
                    R$ {{ __moeda($item->valor) }}
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">Vencimento</div>
                <div class="info-value">
                    {{ __data_pt($item->vencimento, 0) }}
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">Data de registro</div>
                <div class="info-value">
                    {{ __data_pt($item->created_at) }}
                </div>
            </div>

        </div>

        <div class="boleto-actions">
            <a target="_blank" href="{{ route('boleto.print', [$item->id]) }}" class="btn btn-dark px-4">
                <i class="ri-printer-line"></i>
                Imprimir boleto
            </a>
        </div>

    </div>
</div>
@endsection