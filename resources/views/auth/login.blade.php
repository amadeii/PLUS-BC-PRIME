@extends('layouts.header_auth', ['title' => 'Login'])

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

        .login-page {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            height: 100vh;
            height: 100dvh;
            width: 100%;
            overflow: hidden;
            background: #f8fbf7;
        }

        .login-left-bg {
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

        .login-left-bg::before {
            position: absolute;
            inset: 0;
            content: "";
            background:
                linear-gradient(110deg, rgba(20, 83, 45, .38) 0%, rgba(46, 125, 50, .18) 44%, rgba(248, 250, 247, .16) 100%),
                linear-gradient(180deg, rgba(15, 23, 42, .14), rgba(255, 255, 255, .04));
        }

        .login-left-bg::after {
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

        .login-right-area {
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

        .login-card {
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

        .login-card::-webkit-scrollbar {
            width: 8px;
        }

        .login-card::-webkit-scrollbar-track {
            background: transparent;
        }

        .login-card::-webkit-scrollbar-thumb {
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
            }

            .brand-toggle-label-next {
                font-size: 15px;
            }

            .knob-text {
                font-size: 15px;
            }

            .knob-next {
                font-size: 14px;
            }
        }

        .finance-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .finance-card {
            min-height: 110px;
            padding: 14px;
            border: 1px solid #dde8d8;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .finance-card small {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .finance-card strong {
            display: block;
            margin-top: 7px;
            color: #0f172a;
            font-size: clamp(17px, 2.4vw, 20px);
            line-height: 1.1;
        }

        .finance-card span {
            display: block;
            margin-top: 6px;
            color: #2e7d32;
            font-size: 12px;
            font-weight: 700;
        }

        .mini-chart {
            width: 100%;
            height: 34px;
            margin-top: 10px;
        }

        .mini-chart path,
        .mini-chart rect {
            vector-effect: non-scaling-stroke;
        }

        .login-title {
            margin-bottom: 6px;
            color: #0f172a;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .login-subtitle {
            margin-bottom: 18px;
            color: #64748b;
            font-size: 14px;
        }

        .login-card .form-label {
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

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #64748b;
            transform: translateY(-50%);
        }

        .password-toggle:hover,
        .password-toggle:focus {
            background: #eaf7e6;
            color: #2e7d32;
        }

        .password-control .form-control {
            padding-right: 52px;
        }

        .forgot-link,
        .support-link,
        .signup-link {
            color: #2e7d32;
            font-size: 11px;
            font-weight: 700;
        }

        .forgot-link:hover,
        .support-link:hover,
        .signup-link:hover {
            color: #57a83a;
        }

        .form-check-label {
            color: #64748b;
            font-size: 13px;
        }

        .form-check-input {
            border-color: #dde8d8;
        }

        .form-check-input:checked {
            border-color: #69be45;
            background-color: #69be45;
        }

        .login-submit {
            height: 52px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #76c94e, #3f9e2f);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(63, 158, 47, .28);
        }

        .login-submit:hover,
        .login-submit:focus {
            background: linear-gradient(135deg, #69be45, #2e7d32);
            color: #ffffff;
        }

        .support-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
        }

        .login-footer {
            margin: 24px 0 0;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        .demo-access {
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #dde8d8;
            border-radius: 14px;
            background: #f8faf7;
        }

        .demo-access p {
            margin-bottom: 10px;
            color: #64748b;
            font-size: 12px;
        }

        .invalid-feedback,
        .alert {
            font-size: 13px;
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

            .login-page {
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

            .login-left-bg {
                position: absolute;
                inset: 0;
                width: 100%;
                min-height: 100%;
                opacity: .5;
            }

            .login-left-bg::before {
                background: linear-gradient(180deg, rgba(248, 251, 247, .72), rgba(248, 251, 247, .88));
            }

            .login-left-bg::after {
                display: none;
            }

            .login-right-area {
                width: 100%;
                height: auto;
                min-height: 100vh;
                min-height: 100dvh;
                padding: 0;
                background: transparent;
                box-shadow: none;
            }

            .login-card {
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
            }

            .brand-toggle-label-next {
                font-size: 12px;
            }

            .knob-text {
                font-size: 13px;
            }

            .knob-next {
                font-size: 12px;
            }

            .finance-cards {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .finance-card {
                min-height: auto;
            }

            .login-title {
                font-size: 24px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $login = isset($_COOKIE['ckLogin']) ? base64_decode($_COOKIE['ckLogin']) : '';
        $pass = isset($_COOKIE['ckPass']) ? base64_decode($_COOKIE['ckPass']) : '';
        $remember = isset($_COOKIE['ckRemember']) ? $_COOKIE['ckRemember'] : '';
        $bcPrimeBackground = file_exists(public_path('images/auth/bg-bcprimenext-login.png'))
            ? '/images/auth/bg-bcprimenext-login.png'
            : '/imgs/auth-office-bg.png';
    @endphp

    <main class="login-page" style="--bcprime-login-bg: url('{{ $bcPrimeBackground }}');">
        <div class="login-left-bg" aria-hidden="true"></div>
        <div class="login-right-area">
            <section class="login-card" aria-label="Login BCPRIME NEXT">
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

            <div class="finance-cards" aria-hidden="true">
                <div class="finance-card">
                    <small>Receita Total</small>
                    <strong>R$ 1.250.780,00</strong>
                    <span>&uarr; 12,5% vs. m&ecirc;s anterior</span>
                    <svg class="mini-chart" viewBox="0 0 120 34" role="img">
                        <path d="M2 29 L15 24 L28 25 L40 18 L53 20 L66 12 L78 15 L92 8 L105 11 L118 3" fill="none" stroke="#69be45" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="118" cy="3" r="3" fill="#2e7d32" />
                    </svg>
                </div>
                <div class="finance-card">
                    <small>Despesas Totais</small>
                    <strong>R$ 842.560,00</strong>
                    <span>&darr; 8,3% vs. m&ecirc;s anterior</span>
                    <svg class="mini-chart" viewBox="0 0 120 34" role="img">
                        <rect x="7" y="13" width="9" height="18" rx="3" fill="#d6ded2" />
                        <rect x="26" y="8" width="9" height="23" rx="3" fill="#9fb09a" />
                        <rect x="45" y="17" width="9" height="14" rx="3" fill="#d6ded2" />
                        <rect x="64" y="5" width="9" height="26" rx="3" fill="#69be45" />
                        <rect x="83" y="15" width="9" height="16" rx="3" fill="#9fb09a" />
                        <rect x="102" y="10" width="9" height="21" rx="3" fill="#2e7d32" />
                    </svg>
                </div>
            </div>

            @if (env('APP_ENV') == 'demo')
                <div class="demo-access">
                    <p>Clique nos bot&otilde;es abaixo para acessar os usu&aacute;rios pr&eacute; configurados.</p>
                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <button class="btn btn-success w-100 btn-sm" type="button" onclick="login('slym@slym.com', '123456')">
                                SUPERADMIN
                            </button>
                        </div>
                        <div class="col-12 col-sm-6">
                            <button class="btn btn-dark w-100 btn-sm" type="button" onclick="login('teste@teste.com', '123456')">
                                EMPRESA TESTE
                            </button>
                        </div>
                    </div>
                    <a class="support-link" href="https://api.whatsapp.com/send/?phone=5541985117177&text&type=phone_number&app_absent=0" target="_blank">
                        WhatsApp <strong>43920004769</strong>
                    </a>
                </div>
            @endif

            <h1 class="login-title">Login</h1>
            <p class="login-subtitle">Digite seu endere&ccedil;o de email e senha para acessar a conta.</p>

            <form method="POST" action="{{ route('login') }}" id="form-login">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="field-control">
                        <i class="ri-mail-line field-icon"></i>
                        <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" id="email" required
                            value="{{ $login }}" placeholder="Digite seu email" autocomplete="email">
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <label for="password" class="form-label">Senha</label>
                        <a href="{{ route('password.request') }}" class="forgot-link">Esqueceu sua senha?</a>
                    </div>
                    <div class="field-control password-control">
                        <i class="ri-lock-line field-icon"></i>
                        <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" required value="{{ $pass }}"
                            id="password" placeholder="Digite sua senha" autocomplete="current-password">
                        <button class="password-toggle" type="button" id="toggle-password" aria-label="Mostrar senha">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input name="remember" type="checkbox" {{ $remember ? 'checked' : '' }}
                            class="form-check-input" id="checkbox-signin">
                        <label class="form-check-label" for="checkbox-signin">Lembrar-me</label>
                    </div>
                </div>

                @if (Session::has('error'))
                    <div class="alert alert-danger py-2">{{ Session::get('error') }}</div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-success py-2">{{ Session::get('success') }}</div>
                @endif

                <div class="d-grid mb-0 text-center">
                    <button class="btn btn-primary login-submit" type="submit">
                        <i class="ri-lock-line"></i> Acessar
                    </button>
                </div>

                <a target="_blank" href="https://wa.me/55{{ env('APP_FONE') }}" class="support-link">
                    <i class="ri-customer-service-2-line"></i> Suporte
                </a>
            </form>

            @if (request()->auto_cadastro)
                <p class="login-footer">N&atilde;o tem uma conta?
                    <a href="{{ route('register') }}" class="signup-link">Inscrever-se</a>
                </p>
            @endif
            </section>
        </div>
    </main>
@endsection

@section('js')
<script type="text/javascript">
    function login(email, senha) {
        $('#email').val(email)
        $('#password').val(senha)
        $('#form-login').submit()
    }

    $('#toggle-password').on('click', function () {
        const input = $('#password')
        const icon = $(this).find('i')
        const show = input.attr('type') === 'password'

        input.attr('type', show ? 'text' : 'password')
        icon.toggleClass('ri-eye-line', !show)
        icon.toggleClass('ri-eye-off-line', show)
        $(this).attr('aria-label', show ? 'Ocultar senha' : 'Mostrar senha')
    })

    $('html').attr('data-bs-theme', '{{ __dataThemeDefault() }}')
</script>
@endsection
