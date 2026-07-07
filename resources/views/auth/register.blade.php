@extends('layouts.header_auth', ['title' => 'Cadastre-se'])

@section('css')
    <style type="text/css">
        html {
            height: 100%;
            overflow: hidden;
        }

        body.authentication-bg {
            background: #f8faf7;
            height: 100vh;
            height: 100dvh;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            padding-bottom: 0 !important;
        }

        .register-page {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            height: 100vh;
            height: 100dvh;
            width: 100%;
            overflow: hidden;
            background: #f8fbf7;
        }

        .register-left-bg {
            position: absolute;
            inset: 0 auto 0 0;
            z-index: 0;
            width: 52%;
            overflow: hidden;
            background-image: var(--bcprime-login-bg);
            background-position: center left;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .register-left-bg::before {
            position: absolute;
            inset: 0;
            content: "";
            background:
                linear-gradient(110deg, rgba(20, 83, 45, .38) 0%, rgba(46, 125, 50, .18) 44%, rgba(248, 250, 247, .16) 100%),
                linear-gradient(180deg, rgba(15, 23, 42, .14), rgba(255, 255, 255, .04));
        }

        .register-left-bg::after {
            position: absolute;
            left: 9%;
            bottom: 10%;
            width: 310px;
            height: 310px;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 50%;
            content: "";
            background: radial-gradient(circle, rgba(105, 190, 69, .22), rgba(105, 190, 69, 0) 66%);
            filter: blur(.2px);
        }

        .register-right-area {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48%;
            height: 100%;
            min-height: 100vh;
            min-height: 100dvh;
            margin-left: auto;
            padding: 20px clamp(24px, 4vw, 64px);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 251, 247, 1)),
                #f8fbf7;
            box-shadow: -30px 0 70px rgba(15, 23, 42, .06);
        }

        .register-card {
            width: min(100%, 560px);
            max-height: calc(100vh - 40px);
            max-height: calc(100dvh - 40px);
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 34px 40px;
            border: 1px solid rgba(220, 230, 220, .8);
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .12);
            scrollbar-color: rgba(105, 190, 69, .45) transparent;
            scrollbar-width: thin;
        }

        .register-card::-webkit-scrollbar {
            width: 8px;
        }

        .register-card::-webkit-scrollbar-track {
            background: transparent;
        }

        .register-card::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(105, 190, 69, .38);
        }

        .bcprime-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 72px;
            margin-bottom: 22px;
            color: #34413d;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 22px;
            width: max-content;
            max-width: 100%;
            flex: 0 0 auto;
        }

        .bcprime-wordmark {
            display: flex;
            flex: 0 0 auto;
            align-items: flex-start;
            justify-content: center;
            line-height: 1;
        }

        .bcprime-main {
            flex: 0 0 auto;
            font-family: "Montserrat", "Poppins", "Segoe UI", Arial, sans-serif;
            font-size: 58px;
            font-weight: 300;
            line-height: .86;
            letter-spacing: 0;
            text-transform: lowercase;
            white-space: nowrap;
            text-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }

        .bcprime-main .bc-green {
            color: #6fa85f;
            font-weight: 300;
        }

        .bcprime-main .bc-dark {
            color: #34413d;
            font-weight: 300;
        }

        .brand-toggle {
            --switch-w: 190px;
            --switch-h: 62px;
            --pad: 5px;
            --knob-w: 104px;
            --travel: 76px;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            width: var(--switch-w);
            min-width: var(--switch-w);
            flex: 0 0 var(--switch-w);
            height: var(--switch-h);
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(190, 238, 170, .65);
            border-radius: 999px;
            background: linear-gradient(135deg, #76c94e 0%, #3f9e2f 48%, #2e7d32 100%);
            box-shadow: 0 16px 32px rgba(46, 125, 50, .22), inset 0 1px 0 rgba(255, 255, 255, .34);
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: .07em;
            text-transform: uppercase;
            isolation: isolate;
            pointer-events: none;
            user-select: none;
        }

        .brand-toggle::after {
            position: absolute;
            inset: 0;
            z-index: 0;
            content: "";
            border-radius: inherit;
            background: linear-gradient(90deg, rgba(255, 255, 255, .22), rgba(255, 255, 255, 0) 44%, rgba(255, 255, 255, .2));
            animation: brand-next-sheen 4.8s ease-in-out infinite;
        }

        .brand-toggle-label {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            height: 100%;
            min-width: 0;
            color: rgba(255, 255, 255, .86);
            pointer-events: none;
        }

        .brand-toggle-label-next {
            justify-content: flex-end;
            min-width: 88px;
            padding-right: 18px;
            font-size: 16px;
            opacity: .58;
            animation: brand-next-awake 4.8s ease-in-out infinite;
        }

        .switch-knob {
            position: absolute;
            top: var(--pad);
            left: var(--pad);
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            width: var(--knob-w);
            height: calc(var(--switch-h) - var(--pad) - var(--pad));
            overflow: hidden;
            border-radius: 999px;
            background: linear-gradient(135deg, #ffffff 0%, #f2fbef 100%);
            box-shadow: 0 12px 22px rgba(15, 23, 42, .18), inset 0 1px 0 rgba(255, 255, 255, .95);
            animation: switch-knob-move 4.8s ease-in-out infinite;
        }

        .knob-text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2e7d32;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: .04em;
            line-height: 1;
            white-space: nowrap;
            text-shadow: 0 1px 8px rgba(46, 125, 50, .08);
        }

        .knob-next {
            font-size: 15px;
            animation: knob-text-next 4.8s ease-in-out infinite;
        }

        @keyframes switch-knob-move {
            0%, 36% {
                transform: translateX(0);
            }
            50%, 86% {
                transform: translateX(var(--travel));
            }
            100% {
                transform: translateX(0);
            }
        }

        @keyframes knob-text-next {
            0%, 36% {
                opacity: 0;
                transform: translateX(8px);
            }
            50%, 86% {
                opacity: 1;
                transform: translateX(0);
            }
            100% {
                opacity: 0;
                transform: translateX(8px);
            }
        }

        @keyframes brand-next-awake {
            0%, 36% {
                opacity: .58;
                color: #ffffff;
                text-shadow: none;
            }
            50%, 86% {
                opacity: .14;
                color: #ffffff;
                text-shadow: none;
            }
            100% {
                opacity: .58;
                color: #ffffff;
                text-shadow: none;
            }
        }

        @keyframes brand-next-sheen {
            0%, 38% {
                transform: translateX(-80%);
                opacity: .2;
            }
            58%, 86% {
                transform: translateX(35%);
                opacity: .85;
            }
            100% {
                transform: translateX(-80%);
                opacity: .2;
            }
        }

        .register-title {
            margin-bottom: 6px;
            color: #0f172a;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .register-subtitle {
            margin-bottom: 18px;
            color: #64748b;
            font-size: 14px;
        }

        .register-card .form-label {
            margin-bottom: 8px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
        }

        .field-control {
            position: relative;
        }

        .field-control .field-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            color: #64748b;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .field-control .form-control {
            height: 48px;
            padding-left: 46px;
            border-color: #dde8d8;
            border-radius: 12px;
            color: #0f172a;
            font-size: 14px;
            background: #ffffff;
        }

        .field-control .form-control:focus {
            border-color: #69be45;
            box-shadow: 0 0 0 4px rgba(105, 190, 69, .16);
        }

        .register-submit {
            height: 52px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #76c94e, #3f9e2f);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(63, 158, 47, .28);
        }

        .register-submit:hover,
        .register-submit:focus {
            background: linear-gradient(135deg, #69be45, #2e7d32);
            color: #ffffff;
        }

        .signin-link {
            color: #2e7d32;
            font-size: 13px;
            font-weight: 700;
        }

        .signin-link:hover {
            color: #57a83a;
        }

        .register-footer {
            margin: 24px 0 0;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        .captcha-panel {
            padding: 12px;
            border: 1px solid #dde8d8;
            border-radius: 14px;
            background: #f8faf7;
        }

        .captcha-panel img {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 0 auto 12px;
        }

        .invalid-feedback,
        .alert {
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .auth-brand {
                width: 100%;
                flex-wrap: wrap;
                row-gap: 14px;
            }

            .bcprime-main {
                font-size: 48px;
            }

            .brand-toggle {
                --switch-w: 164px;
                --switch-h: 56px;
                --pad: 5px;
                --knob-w: 92px;
                --travel: 62px;
            }

            .brand-toggle-label {
                min-width: 0;
            }

            .brand-toggle-label-next {
                min-width: 76px;
                padding-right: 15px;
                font-size: 15px;
            }

            .knob-text {
                font-size: 15px;
            }

            .knob-next {
                font-size: 14px;
            }
        }

        @media (max-width: 575.98px) {
            html {
                overflow: auto;
            }

            body.authentication-bg {
                height: auto;
                min-height: 100vh;
                min-height: 100dvh;
                overflow: auto;
            }

            .register-page {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                min-height: 100dvh;
                height: auto;
                padding: 12px;
                overflow: visible;
                background: #f8fbf7;
            }

            .register-left-bg {
                position: absolute;
                inset: 0;
                width: 100%;
                min-height: 100%;
                opacity: .5;
            }

            .register-left-bg::before {
                background: linear-gradient(180deg, rgba(248, 251, 247, .72), rgba(248, 251, 247, .88));
            }

            .register-left-bg::after {
                display: none;
            }

            .register-right-area {
                width: 100%;
                height: auto;
                min-height: 100vh;
                min-height: 100dvh;
                padding: 0;
                background: transparent;
                box-shadow: none;
            }

            .register-card {
                position: relative;
                top: auto;
                right: auto;
                transform: none;
                width: min(94vw, 540px);
                max-height: none;
                overflow-y: visible;
                padding: 24px;
                border-radius: 22px;
            }

            .bcprime-brand {
                min-height: 112px;
                margin-bottom: 18px;
            }

            .auth-brand {
                flex-direction: column;
                gap: 14px;
                width: 100%;
            }

            .bcprime-main {
                font-size: 46px;
            }

            .brand-toggle {
                --switch-w: 138px;
                --switch-h: 44px;
                --pad: 4px;
                --knob-w: 78px;
                --travel: 52px;
                font-size: 10px;
            }

            .brand-toggle-label {
                min-width: 0;
            }

            .brand-toggle-label-next {
                min-width: 62px;
                padding-right: 12px;
                font-size: 12px;
            }

            .knob-text {
                font-size: 13px;
            }

            .knob-next {
                font-size: 12px;
            }

            .register-title {
                font-size: 24px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $captchaa = 0;
        try {
            captcha_img();
            $captchaa = 1;
        } catch (\Exception $e) {
        }

        $bcPrimeBackground = file_exists(public_path('images/auth/bg-bcprimenext-login.png'))
            ? '/images/auth/bg-bcprimenext-login.png'
            : '/imgs/auth-office-bg.png';
    @endphp

    <main class="register-page" style="--bcprime-login-bg: url('{{ $bcPrimeBackground }}');">
        <div class="register-left-bg" aria-hidden="true"></div>
        <div class="register-right-area">
            <section class="register-card" aria-label="Cadastro BCPRIME NEXT">
                <div class="bcprime-brand">
                    <div class="auth-brand" aria-label="bcprime NEXT">
                        <div class="bcprime-wordmark">
                            <div class="bcprime-main">
                                <span class="bc-green">bc</span><span class="bc-dark">prime</span>
                            </div>
                        </div>
                        <span class="brand-toggle next-switch" aria-hidden="true">
                            <span class="brand-toggle-label brand-toggle-label-next">NEXT</span>
                            <span class="switch-knob">
                                <span class="knob-text knob-next">NEXT &rsaquo;</span>
                            </span>
                        </span>
                    </div>
                </div>

                <h1 class="register-title">Cadastre-se</h1>
                <p class="register-subtitle">Crie sua conta para acessar o ambiente BCPRIME NEXT.</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <div class="field-control">
                            <i class="ri-user-line field-icon"></i>
                            <input value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror"
                                type="text" id="name" placeholder="Digite seu nome" required name="name"
                                autocomplete="name">
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="field-control">
                            <i class="ri-mail-line field-icon"></i>
                            <input value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
                                type="email" id="email" placeholder="Digite seu email" required name="email"
                                autocomplete="email">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <div class="field-control">
                            <i class="ri-lock-line field-icon"></i>
                            <input value="{{ old('password') }}"
                                class="form-control @error('password') is-invalid @enderror" type="password" id="password"
                                placeholder="Digite sua senha" required name="password" autocomplete="new-password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirme a senha</label>
                        <div class="field-control">
                            <i class="ri-lock-password-line field-icon"></i>
                            <input class="form-control @error('password_confirmation') is-invalid @enderror" type="password"
                                id="password_confirmation" placeholder="Confirme sua senha"
                                value="{{ old('password_confirmation') }}" required name="password_confirmation"
                                autocomplete="new-password">
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="plano" value="{{ request()->plano }}">
                    <input type="hidden" name="captchaa" value="{{ $captchaa }}">

                    @if ($captchaa == 1)
                        <div class="captcha-panel mb-3">
                            {!! captcha_img() !!}
                            <div class="field-control">
                                <i class="ri-shield-check-line field-icon"></i>
                                <input class="form-control @error('captcha') is-invalid @enderror" type="text"
                                    id="captcha" placeholder="Digite os caracteres" required name="captcha">
                            </div>
                            @error('captcha')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-0 d-grid text-center">
                        <button class="btn btn-primary register-submit" type="submit">
                            <i class="ri-user-add-line"></i> Cadastrar
                        </button>
                    </div>
                </form>

                <p class="register-footer">J&aacute; tem conta?
                    <a href="{{ route('login') }}" class="signin-link">Login</a>
                </p>
            </section>
        </div>
    </main>
@endsection

@section('js')
    <script type="text/javascript">
        $('html').attr('data-bs-theme', '{{ __dataThemeDefault() }}')
    </script>
@endsection
