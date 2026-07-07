@extends('layouts.app', ['title' => 'Perfil'])

@section('css')
<style>

    .top-bar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .btn-back {
        /*border-radius: 12px;*/
        font-weight: 700;
        padding: 6px 14px;
    }

    .profile-avatar {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #EEF0F6;
        box-shadow: 0 8px 18px rgba(0,0,0,.08);
    }

    .profile-name {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin-top: 10px;
    }

    .profile-company {
        color: #8A93A3;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .btn-edit {
        border-radius: 12px;
        padding: 6px 16px;
    }

    .info-box {
        margin-top: 20px;
        border: 1px solid #EEF0F6;
        border-radius: 18px;
        margin: 20px;
        padding: 20px;
    }

    .info-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        color: #6B7280;
        margin-bottom: 10px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #F1F3F8;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
    }

    .info-value {
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        text-align: right;
    }

    .badge-env {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .prod {
        background: #EAFBF0;
        color: #16A34A;
    }

    .hom {
        background: #FFF4E5;
        color: #D97706;
    }

    .actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 18px;
        flex-wrap: wrap;
    }

    .actions .btn {
        border-radius: 12px;
        font-weight: 700;
        padding: 6px 14px;
    }

    @media (max-width: 600px) {
        .info-item {
            flex-direction: column;
            gap: 4px;
        }

        .info-value {
            text-align: left;
        }
    }
</style>
@endsection

@section('content')

<div class="card profile-card mt-1">

    <!-- VOLTAR -->
    <div class="top-bar m-3">
        <a href="{{ route('usuarios.index') }}" class="btn btn-danger btn-sm btn-back">
            <i class="ri-arrow-left-line"></i> Voltar
        </a>
    </div>

    <!-- PERFIL -->
    <div class="text-center">
        @if(isset($item) && $item->imagem)
            <img src="{{ $item->img }}" class="profile-avatar">
        @else
            <img src="/imgs/no-image.png" class="profile-avatar">
        @endif

        <div class="profile-name">{{ $item->name }}</div>

        @if($item->empresa)
            <div class="profile-company">{{ $item->empresa->empresa->nome }}</div>
        @endif

        <a href="{{ route('usuarios.edit', $item->id) }}" class="btn btn-warning btn-sm btn-edit">
            <i class="ri-edit-line"></i> Editar
        </a>
    </div>

    <!-- INFO -->
    <div class="info-box">
        <div class="info-title">Sobre</div>

        <div class="info-item">
            <span class="info-label">Nome</span>
            <span class="info-value">{{ $item->name }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value">{{ $item->email }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">Cadastro</span>
            <span class="info-value">{{ __data_pt($item->created_at) }}</span>
        </div>

        @if(Auth::user()->empresa)
        <div class="info-item">
            <span class="info-label">Empresa</span>
            <span class="info-value">{{ Auth::user()->empresa->empresa->nome }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">Ambiente</span>
            <span class="info-value">
                @if(Auth::user()->empresa->empresa->ambiente == 2)
                    <span class="badge-env hom">HOMOLOGAÇÃO</span>
                @else
                    <span class="badge-env prod">PRODUÇÃO</span>
                @endif
            </span>
        </div>
        @endif

        <div class="info-item">
            <span class="info-label">IP</span>
            <span class="info-value">
                {{ Auth::user()->acessos && Auth::user()->acessos->first() ? Auth::user()->acessos->first()->ip : '-' }}
            </span>
        </div>

        @if($item->empresa && $item->empresa->empresa->plano)
        <div class="info-item">
            <span class="info-label">Plano</span>
            <span class="info-value">
                {{ $item->empresa->empresa->plano->plano->nome }}
            </span>
        </div>

        @if($item->empresa->empresa->receber_com_boleto == 0)
        <div class="info-item">
            <span class="info-label">Expiração</span>
            <span class="info-value">
                {{ __data_pt($item->empresa->empresa->plano->data_expiracao, 0) }}
            </span>
        </div>
        @endif
        @endif
    </div>

    <!-- AÇÕES -->
    <div class="actions m-4">
        @if($item->empresa && $item->empresa->empresa->plano && $item->empresa->empresa->receber_com_boleto == 0)
            <a class="btn btn-light btn-sm" href="{{ route('upgrade.index') }}">
                <i class="ri-vip-crown-line"></i> Upgrade
            </a>
        @endif

        @if(__faturaBoleto())
            <a class="btn btn-dark btn-sm" target="_blank" href="{{ __faturaBoleto()->pdf_boleto }}">
                <i class="ri-printer-line"></i> Boleto
            </a>
        @endif
    </div>

</div>

@endsection