<div class="leftside-menu">
    <!-- Brand Logo Light -->
    <a href="/" class="logo logo-light">
        <span class="logo-lg">
            <span class="bcprime-app-brand" aria-label="bcprime NEXT">
                <span class="bcprime-app-wordmark">
                    <span class="bcprime-app-green">bc</span><span class="bcprime-app-dark">prime</span>
                </span>
                <span class="bcprime-app-switch" aria-hidden="true">
                    <span class="bcprime-app-switch-label bcprime-app-switch-label-next">NEXT</span>
                    <span class="bcprime-app-knob">
                        <span class="bcprime-app-knob-text bcprime-app-knob-next">NEXT &rsaquo;</span>
                    </span>
                </span>
            </span>
        </span>
        <span class="logo-sm">
            <img src="/favicon.ico" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="/" class="logo logo-dark">
        <span class="logo-lg">
            <span class="bcprime-app-brand" aria-label="bcprime NEXT">
                <span class="bcprime-app-wordmark">
                    <span class="bcprime-app-green">bc</span><span class="bcprime-app-dark">prime</span>
                </span>
                <span class="bcprime-app-switch" aria-hidden="true">
                    <span class="bcprime-app-switch-label bcprime-app-switch-label-next">NEXT</span>
                    <span class="bcprime-app-knob">
                        <span class="bcprime-app-knob-text bcprime-app-knob-next">NEXT &rsaquo;</span>
                    </span>
                </span>
            </span>
        </span>
        <span class="logo-sm">
            <img src="/favicon.ico" alt="small logo">
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!-- Leftbar User -->
        <div class="leftbar-user p-3 text-white">
            <a @if(!__isContador()) href="{{ route('usuarios.profile', Auth::user()->id) }}" @endif class="d-flex align-items-center text-reset">
                <div class="flex-shrink-0">
                    @if(Auth::user()->imagem != null)
                    <img src="{{ Auth::user()->img }}" height="42" class="rounded-circle shadow">
                    @else
                    <img src="/assets/images/users/avatar-4.jpg" height="42" class="rounded-circle shadow">
                    @endif
                </div>
                <div class="flex-grow-1 ms-2">
                    <span class="fw-semibold fs-15 d-block"> {{ Auth::user()->name }}</span>
                    <span class="fs-13">{{ Auth::user()->tipo }}</span>
                </div>
                <div class="ms-auto">
                    <i class="ri-arrow-right-s-fill fs-20"></i>
                </div>
            </a>
        </div>

        <!--- Sidemenu -->
        <ul class="side-nav" id="step4">

            <li class="side-nav-title mt-1"> Menu</li>

            <li class="side-nav-item">
                <a href="{{ route('home') }}" class="side-nav-link">
                    <i class="ri-dashboard-2-fill"></i>
                    <span class="badge bg-success float-end"></span>
                    <span> Home </span>
                </a>
            </li>

            @if(__isSuporte())
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPages2" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-broadcast-line"></i>
                    <span> Suporte </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPages2">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('empresas.index') }}">Empresas</a>
                        </li>

                        <li>
                            <a href="{{ route('segmentos.index') }}">Segmentos</a>
                        </li>

                        <li>
                            <a href="{{ route('cidades.index') }}">Cidades</a>
                        </li>
                        <li>
                            <a href="{{ route('usuario-super.index') }}">Usuários</a>
                        </li>
                        <li>
                            <a href="{{ route('ncm.index') }}">NCM</a>
                        </li>

                        <li>
                            <a href="{{ route('logs.index') }}">Logs</a>
                        </li>

                        <li>
                            <a href="{{ route('ibpt.index') }}">IBPT</a>
                        </li>

                        <li>
                            <a href="{{ route('ticket-super.index') }}">Ticket</a>
                        </li>
                        <li>
                            <a href="{{ route('configuracao-super.index') }}">Configuração</a>
                        </li>

                        <li>
                            <a href="{{ route('notificacao-super.index') }}">Notificações</a>
                        </li>
                        <li>
                            <a href="{{ route('padroes-etiqueta.index') }}">Padrões para etiqueta</a>
                        </li>

                        <li>
                            <a href="{{ route('video-suporte.index') }}">Videos de suporte</a>
                        </li>
                        <li>
                            <a href="{{ route('relatorios-adm.index') }}">Relatórios</a>
                        </li>

                    </ul>

                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPermissao" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-rotate-lock-line"></i>
                    <span> Controle de acesso </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPermissao">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('permissions.index') }}">Permissões</a>
                        </li>
                        <li>
                            <a href="{{ route('roles.index') }}">Atribuições</a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPages1" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-file-mark-fill"></i>
                    <span> Emissões </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPages1">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('nfe-all') }}">NFe</a>
                        </li>
                        <li>
                            <a href="{{ route('nfce-all') }}">NFCe</a>
                        </li>
                        <li>
                            <a href="{{ route('cte-all') }}">CTe</a>
                        </li>
                        <li>
                            <a href="{{ route('mdfe-all') }}">MDFe</a>
                        </li>
                    </ul>
                </div>
            </li>

            @endif
            @if(__isMaster())

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPages2" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-stack-fill"></i>
                    <span> SuperAdmin </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPages2">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('empresas.index') }}">Empresas</a>
                        </li>

                        <li>
                            <a href="{{ route('planos.index') }}">Planos</a>
                        </li>
                        <li>
                            <a href="{{ route('segmentos.index') }}">Segmentos</a>
                        </li>
                        <li>
                            <a href="{{ route('superadmin.dashboard-totais') }}">Dashboard</a>
                        </li>
                        <li>
                            <a href="{{ route('cidades.index') }}">Cidades</a>
                        </li>
                        <li>
                            <a href="{{ route('usuario-super.index') }}">Usuários</a>
                        </li>

                        @if(app('router')->has('configuracao-fatura.index'))
                        <li>
                            <a href="{{ route('configuracao-fatura.index') }}">Configuração fatura</a>
                        </li>
                        @endif
                        @if(app('router')->has('plano-faturas.index'))
                        <li>
                            <a href="{{ route('plano-faturas.index') }}">Gerenciar faturas</a>
                        </li>
                        @endif

                        <li>
                            <a href="{{ route('gerenciar-planos.index') }}">Gerenciar planos</a>
                        </li>
                        <li>
                            <a href="{{ route('financeiro-plano.index') }}">Financeiro planos</a>
                        </li>
                        <li>
                            <a href="{{ route('planos-pendentes.index') }}">Planos pendentes</a>
                        </li>
                        <li>
                            <a href="{{ route('ncm.index') }}">NCM</a>
                        </li>
                        <li>
                            <a href="{{ route('nbs.index') }}">NBS</a>
                        </li>
                        <li>
                            <a href="{{ route('natureza-operacao-super.index') }}">Naturezas de operação</a>
                        </li>
                        <li>
                            <a href="{{ route('padrao-tributacao-produto-super.index') }}">Padrões de tributação</a>
                        </li>
                        <li>
                            <a href="{{ route('logs.index') }}">Logs</a>
                        </li>

                        <li>
                            <a href="{{ route('ibpt.index') }}">IBPT</a>
                        </li>

                        <li>
                            <a href="{{ route('ticket-super.index') }}">Ticket</a>
                        </li>
                        <li>
                            <a href="{{ route('configuracao-super.index') }}">Configuração</a>
                        </li>

                        @if(app('router')->has('config-super.identidade-visual'))
                        <li>
                            <a href="{{ route('config-super.identidade-visual') }}">Identidade visual</a>
                        </li>
                        @endif

                        <li>
                            <a href="{{ route('notificacao-super.index') }}">Notificações</a>
                        </li>
                        <li>
                            <a href="{{ route('padroes-etiqueta.index') }}">Padrões para etiqueta</a>
                        </li>

                        <li>
                            <a href="{{ route('video-suporte.index') }}">Videos de suporte</a>
                        </li>
                        <li>
                            <a href="{{ route('relatorios-adm.index') }}">Relatórios</a>
                        </li>
                        <li>
                            <a href="{{ route('contrato-config.index') }}">Configuração de contrato</a>
                        </li>

                        <li>
                            <a href="{{ route('contrato-config.list') }}">Lista de contratos</a>
                        </li>

                        @routeHas('upload-monitorado')
                        <li>
                            <a href="{{ route('upload-monitorado') }}">Upload monitorado</a>
                        </li>
                        @endrouteHas

                        <li>
                            <a href="{{ route('teste-email.index') }}">Teste de email</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPermissao" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-rotate-lock-line"></i>
                    <span> Controle de acesso </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPermissao">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('permissions.index') }}">Permissões</a>
                        </li>
                        <li>
                            <a href="{{ route('roles.index') }}">Atribuições</a>
                        </li>

                    </ul>
                </div>
            </li>

            @if(env("CONTADOR") == 1)
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarContador" aria-expanded="false" aria-controls="sidebarContador" class="side-nav-link">
                    <i class="ri-team-fill"></i>
                    <span> Contadores </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse" id="sidebarContador">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('contadores.index') }}">
                                Cadastro de Contadores
                            </a>
                        </li>

                        @if(env("WHITELABEL") == 1)
                        <li>
                            <a href="{{ route('planos-white-label.index') }}">
                                Planos White Label
                            </a>
                        </li>

                        @if(app('router')->has('contador-plano-white-label.index'))
                        <li>
                            <a href="{{ route('contador-plano-white-label.index') }}">
                                Gerenciar Planos
                            </a>
                        </li>
                        @endif
                        @endif

                    </ul>
                </div>
            </li>
            @endif

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPages1" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-file-mark-fill"></i>
                    <span> Emissões </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPages1">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('nfe-all') }}">NFe</a>
                        </li>
                        <li>
                            <a href="{{ route('nfce-all') }}">NFCe</a>
                        </li>
                        <li>
                            <a href="{{ route('cte-all') }}">CTe</a>
                        </li>
                        <li>
                            <a href="{{ route('mdfe-all') }}">MDFe</a>
                        </li>
                    </ul>
                </div>
            </li>

            @if(env("MARKETPLACE") == 1)
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarMarketPlace" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-store-2-line"></i>
                    <span>Delivery/Marketplace</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarMarketPlace">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('bairros-super.index') }}">Bairros</a>
                        </li>

                    </ul>
                </div>
            </li>
            @endif

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarFinanceiroBoleto" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-coins-line"></i>
                    <span>Financeiro Boletos</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarFinanceiroBoleto">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('financeiro-boleto.index') }}">Listar</a>
                        </li>

                        <li>
                            <a href="{{ route('financeiro-boleto.gerar') }}">Gerador</a>
                        </li>

                        <li>
                            <a href="{{ route('financeiro-boleto.logs') }}">Logs</a>
                        </li>

                    </ul>
                </div>
            </li>

            @if(env("APP_ENV") != "demo")
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAtualizacao" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-refresh-fill"></i>
                    <span>Atualização </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarAtualizacao">
                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('update-sql.index') }}">Banco de dados</a>
                        </li>
                        <li>
                            <a href="{{ route('update-file.index') }}">Diretórios</a>
                        </li>

                        <li>
                            <a href="{{ route('sistema') }}">Ambiente do Servidor</a>
                        </li>

                        @if(app('router')->has('custom-update.index'))
                        <li>
                            <a href="{{ route('custom-update.index') }}">Diretório customizado</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>

            @endif

            <li class="side-nav-item">
                <a href="{{ route('registro') }}" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-bookmark-3-fill"></i>
                    <span>Registro de Software</span>
                </a>
            </li>

           <!--  <li class="side-nav-item">
                <a href="{{ route('sugestao.index') }}" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-code-s-slash-line"></i>
                    <span>Susgestão e Desenvolvimento</span>
                </a>
            </li> -->

            @endif

            @if(!__isMaster())

            @if(__hasProdutos(Auth::user()->empresa) && !__isContador())
            <li class="side-nav-title">PRODUTOS</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'Produtos'))
            @canany(['produtos_view', 'categoria_produtos_view', 'inventario_view', 'lista_preco_view'])

            <li class="side-nav-item {{ request()->routeIs(
                'categoria-produtos.*',
                'produtos.*',
                'estoque.*',
                'inventarios.*',
                'variacoes.*',
                'lista-preco.*',
                'promocao-produtos.*',
                'produtopadrao-tributacao.*',
                'marcas.*',
                'garantias.*',
                'modelo-etiquetas.*',
                'etiquetas.*',
                'produto-consulta-codigo.*',
                'transferencia-estoque.*',
                'unidades-medida.*',
                'custo-configuracao.*'
                ) ? 'menuitem-active mm-active' : '' }}" id="step7">
                <a data-bs-toggle="collapse" href="#sidebarExtendedProd" aria-expanded="{{ request()->routeIs(
                    'categoria-produtos.*',
                    'produtos.*',
                    'estoque.*',
                    'inventarios.*',
                    'variacoes.*',
                    'lista-preco.*',
                    'promocao-produtos.*',
                    'produtopadrao-tributacao.*',
                    'marcas.*',
                    'garantias.*',
                    'modelo-etiquetas.*',
                    'etiqueta-modelos.*',
                    'produto-consulta-codigo.*',
                    'transferencia-estoque.*',
                    'unidades-medida.*',
                    'custo-configuracao.*'
                    ) ? 'true' : 'false' }}" aria-controls="sidebarExtendedUI" class="side-nav-link">
                    <i class="ri-box-2-line"></i>
                    <span> Produtos </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs(
                    'categoria-produtos.*',
                    'produtos.*',
                    'estoque.*',
                    'inventarios.*',
                    'variacoes.*',
                    'lista-preco.*',
                    'promocao-produtos.*',
                    'produtopadrao-tributacao.*',
                    'marcas.*',
                    'garantias.*',
                    'modelo-etiquetas.*',
                    'etiqueta-modelos.*',
                    'produto-consulta-codigo.*',
                    'transferencia-estoque.*',
                    'unidades-medida.*',
                    'custo-configuracao.*'
                    ) ? 'show' : '' }}" id="sidebarExtendedProd">
                    <ul class="side-nav-second-level">

                        @can('categoria_produtos_view')
                        <li>
                            <a href="{{ route('categoria-produtos.index') }}" class="{{ request()->routeIs('categoria-produtos.*') ? 'active' : '' }}">Categorias</a>
                        </li>
                        @endcan

                        @can('produtos_view')
                        <li>
                            <a href="{{ route('produtos.index', ['status=1']) }}" class="{{ request()->routeIs('produtos.index') ? 'active' : '' }}">Listar</a>
                        </li>
                        @endcan

                        @can('produtos_create')
                        <li>
                            <a href="{{ route('produtos.create') }}" class="{{ request()->routeIs('produtos.create') ? 'active' : '' }}">Novo Produto</a>
                        </li>
                        @endcan

                        @can('estoque_view')
                        <li>
                            <a href="{{ route('estoque.index') }}" class="{{ request()->routeIs('estoque.*') ? 'active' : '' }}">Estoque</a>
                        </li>
                        @endcan

                        @can('inventario_view')
                        <li>
                            <a href="{{ route('inventarios.index') }}" class="{{ request()->routeIs('inventarios.*') ? 'active' : '' }}">Inventário</a>
                        </li>
                        @endcan

                        @can('variacao_view')
                        <li>
                            <a href="{{ route('variacoes.index') }}" class="{{ request()->routeIs('variacoes.*') ? 'active' : '' }}">Variações</a>
                        </li>
                        @endcan

                        @can('lista_preco_view')
                        <li>
                            <a href="{{ route('lista-preco.index') }}" class="{{ request()->routeIs('lista-preco.*') ? 'active' : '' }}">Lista de preços</a>
                        </li>
                        @endcan

                        @can('promocao_produtos_view')
                        <li>
                            <a href="{{ route('promocao-produtos.index') }}" class="{{ request()->routeIs('promocao-produtos.*') ? 'active' : '' }}">Promoção</a>
                        </li>
                        @endcan

                        @if(__isPlanoFiscal())
                        @can('config_produto_fiscal_view')
                        <li>
                            <a href="{{ route('produtopadrao-tributacao.index') }}" class="{{ request()->routeIs('produtopadrao-tributacao.*') ? 'active' : '' }}">Configuração Padrão Fiscal</a>
                        </li>
                        @endcan
                        @endif

                        @can('marcas_view')
                        <li>
                            <a href="{{ route('marcas.index') }}" class="{{ request()->routeIs('marcas.*') ? 'active' : '' }}">Marcas</a>
                        </li>
                        @endcan

                        @can('garantias_view')
                        <li>
                            <a href="{{ route('garantias.index') }}" class="{{ request()->routeIs('garantias.*') ? 'active' : '' }}">Garantias</a>
                        </li>
                        @endcan

                        <li>
                            <a href="{{ route('modelo-etiquetas.index') }}" class="{{ request()->routeIs('modelo-etiquetas.*') ? 'active' : '' }}">Modelos de Etiqueta</a>
                        </li>

                        <li>
                            <a href="{{ route('etiqueta-modelos.index') }}" class="{{ request()->routeIs('etiquetas.*') ? 'active' : '' }}">
                                Gerador de Etiquetas
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('produto-consulta-codigo.index') }}" class="{{ request()->routeIs('produto-consulta-codigo.*') ? 'active' : '' }}">Consulta código</a>
                        </li>

                        @can('transferencia_estoque_view')
                        <li>
                            <a href="{{ route('transferencia-estoque.index') }}" class="{{ request()->routeIs('transferencia-estoque.*') ? 'active' : '' }}">Transferência de estoque</a>
                        </li>
                        @endcan

                        @can('unidade_medida_view')
                        <li>
                            <a href="{{ route('unidades-medida.index') }}" class="{{ request()->routeIs('unidades-medida.*') ? 'active' : '' }}">Unidades de medida</a>
                        </li>
                        @endcan

                        @can('unidade_medida_view')
                        <li>
                            <a href="{{ route('custo-configuracao.index') }}" class="{{ request()->routeIs('custo-configuracao.*') ? 'active' : '' }}">Configuração de custo</a>
                        </li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endcanany
            @endif


            @if(__hasAtendimentoServicos(Auth::user()->empresa))
            <li class="side-nav-title">ATENDIMENTO / SERVIÇOS</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'Agendamentos'))
            @canany(['agendamento_view'])

            <li class="side-nav-item {{ request()->routeIs('agendamentos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarAgendamentos" aria-expanded="{{ request()->routeIs('agendamentos.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-calendar-event-fill"></i>
                    <span>Agendamentos</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('agendamentos.*') ? 'show' : '' }}" id="sidebarAgendamentos">
                    <ul class="side-nav-second-level">
                        <li><a href="{{ route('agendamentos.index') }}" class="{{ request()->routeIs('agendamentos.*') ? 'active' : '' }}">Listar</a></li>
                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Serviços'))
            @canany(['ordem_servico_view'])

            <li class="side-nav-item {{ request()->routeIs('ordem-servico.*', 'convenios.*', 'medicos.*', 'laboratorios.*', 'tratamentos-otica.*', 'formato-armacao.*', 'tipo-armacao.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarOs" aria-expanded="{{ request()->routeIs('ordem-servico.*', 'convenios.*', 'medicos.*', 'laboratorios.*', 'tratamentos-otica.*', 'formato-armacao.*', 'tipo-armacao.*') ? 'true' : 'false' }}" aria-controls="sidebarExtendedSer" class="side-nav-link">
                    <i class="ri-ruler-2-line"></i>
                    <span> Ordem de Serviço </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('ordem-servico.*', 'convenios.*', 'medicos.*', 'laboratorios.*', 'tratamentos-otica.*', 'formato-armacao.*', 'tipo-armacao.*') ? 'show' : '' }}" id="sidebarOs">

                    <ul class="side-nav-second-level">

                        @can('ordem_servico_view')
                        <li><a href="{{ route('ordem-servico.index') }}" class="{{ request()->routeIs('ordem-servico.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('ordem_servico_create')
                        <li><a href="{{ route('ordem-servico.create') }}" class="{{ request()->routeIs('ordem-servico.create') ? 'active' : '' }}">Nova OS</a></li>
                        @endcan

                        @if(__isSegmentoPlanoOtica())

                        @can('convenio_view')
                        <li><a href="{{ route('convenios.index') }}" class="{{ request()->routeIs('convenios.*') ? 'active' : '' }}">Convênios</a></li>
                        @endcan

                        @can('medico_view')
                        <li><a href="{{ route('medicos.index') }}" class="{{ request()->routeIs('medicos.*') ? 'active' : '' }}">Médicos</a></li>
                        @endcan

                        @can('laboratorio_view')
                        <li><a href="{{ route('laboratorios.index') }}" class="{{ request()->routeIs('laboratorios.*') ? 'active' : '' }}">Laboratórios</a></li>
                        @endcan

                        @can('tratamento_otica_view')
                        <li><a href="{{ route('tratamentos-otica.index') }}" class="{{ request()->routeIs('tratamentos-otica.*') ? 'active' : '' }}">Tratamentos ótica</a></li>
                        @endcan

                        @can('formato_armacao_view')
                        <li><a href="{{ route('formato-armacao.index') }}" class="{{ request()->routeIs('formato-armacao.*') ? 'active' : '' }}">Formatos de armação</a></li>
                        @endcan

                        <li><a href="{{ route('tipo-armacao.index') }}" class="{{ request()->routeIs('tipo-armacao.*') ? 'active' : '' }}">Tipos de armação</a></li>

                        @endif

                        @can('metas_view')
                        <li><a href="{{ route('ordem-servico.metas') }}" class="{{ request()->routeIs('ordem-servico.metas') ? 'active' : '' }}">Metas</a></li>
                        @endcan

                    </ul>

                </div>

            </li>
            @endcanany
            @endif

            @canany(['servico_view', 'categoria_servico_view', 'ordem_servico_view'])
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarExtendedServ" aria-expanded="false" aria-controls="sidebarExtendedSer" class="side-nav-link">
                    <i class="ri-tools-fill"></i>
                    <span> Serviços </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarExtendedServ">
                    <ul class="side-nav-second-level">

                        @can('categoria_servico_view')
                        <li>
                            <a href="{{ route('categoria-servico.index') }}">Categorias</a>
                        </li>
                        @endcan

                        @can('servico_view')
                        <li>
                            <a href="{{ route('servicos.index') }}">Listar</a>
                        </li>
                        @endcan

                        @can('servico_create')
                        <li>
                            <a href="{{ route('servicos.create') }}">Novo serviço</a>
                        </li>
                        @endcan

                        @can('garantias_view')
                        <li>
                            <a href="{{ route('garantias.index') }}">Garantias</a>
                        </li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endcanany

            @if(__isActivePlan(Auth::user()->empresa, 'NFSe'))
            @canany(['nfse_view'])

            <li class="side-nav-item {{ request()->routeIs('nota-servico.*', 'nota-servico-config.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarNfse" aria-expanded="{{ request()->routeIs('nota-servico.*', 'nota-servico-config.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-file-code-line"></i>
                    <span>NFSe</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('nota-servico.*', 'nota-servico-config.*') ? 'show' : '' }}" id="sidebarNfse">

                    <ul class="side-nav-second-level">

                        @can('nfse_view')
                        <li><a href="{{ route('nota-servico.index') }}" class="{{ request()->routeIs('nota-servico.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('nfse_create')
                        <li><a href="{{ route('nota-servico.create') }}" class="{{ request()->routeIs('nota-servico.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                        <li>
                            <a href="{{ route('nota-servico-config.index') }}" class="{{ request()->routeIs('nota-servico-config.index') ? 'active' : '' }}">
                                Emitente Integranotas
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Locação'))
            @canany(['locacao_view', 'veiculo_view'])

            <li class="side-nav-item {{ request()->routeIs('locacoes.*', 'veiculos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarLocacao" aria-expanded="{{ request()->routeIs('locacoes.*', 'veiculos.*') ? 'true' : 'false' }}" aria-controls="sidebarLocacao" class="side-nav-link">

                    <i class="ri-roadster-line"></i>
                    <span> Locação </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('locacoes.*', 'veiculos.*') ? 'show' : '' }}"
                    id="sidebarLocacao">

                    <ul class="side-nav-second-level">

                        @can('locacao_view')
                        @if(app('router')->has('locacoes.index'))
                        <li>
                            <a href="{{ route('locacoes.index') }}" class="{{ request()->routeIs('locacoes.*') ? 'active' : '' }}">
                                Locações
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('locacao_create')
                        @if(app('router')->has('locacoes.create'))
                        <li>
                            <a href="{{ route('locacoes.create') }}" class="{{ request()->routeIs('locacoes.create') ? 'active' : '' }}">
                                Nova Locação
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('veiculo_view')
                        <li>
                            <a href="{{ route('veiculos.index') }}" class="{{ request()->routeIs('veiculos.*') ? 'active' : '' }}">
                                Veículos
                            </a>
                        </li>
                        @endcan

                    </ul>

                </div>

            </li>

            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Cobranças Recorrentes'))

            <li class="side-nav-item {{ request()->routeIs('recorrencias.*', 'recorrencia-regra-comunicacao.*', 'recorrencia-contrato-modelos.*', 'recorrencia-contratos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarRecorrencia" aria-expanded="{{ request()->routeIs('recorrencias.*', 'recorrencia-regra-comunicacao.*', 'recorrencia-contrato-modelos.*', 'recorrencia-contratos.*') ? 'true' : 'false' }}" aria-controls="sidebarRecorrencia" class="side-nav-link"
                    >
                    <i class="ri-repeat-line"></i>
                    <span> Cobranças Recorrentes </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('recorrencias.*', 'recorrencia-regra-comunicacao.*', 'recorrencia-contrato-modelos.*', 'recorrencia-contratos.*') ? 'show' : '' }}" id="sidebarRecorrencia">

                    <ul class="side-nav-second-level">

                        @can('recorrencia_view')
                        <li><a href="{{ route('recorrencias.index') }}" class="{{ request()->routeIs('recorrencias.*') ? 'active' : '' }}">Cobranças</a></li>
                        <li><a href="{{ route('recorrencia-regra-comunicacao.index') }}" class="{{ request()->routeIs('recorrencia-regra-comunicacao.*') ? 'active' : '' }}">Régra de Comunicação</a></li>
                        @endcan
                        @can('recorrencia_contrato_modelo_view')
                        <li><a href="{{ route('recorrencia-contrato-modelos.index') }}" class="{{ request()->routeIs('recorrencia-contrato-modelos.*') ? 'active' : '' }}">Modelos de Contrato</a></li>
                        @endcan

                        @can('recorrencia_view')
                        @if(app('router')->has('recorrencia-contratos.index'))
                        <li><a href="{{ route('recorrencia-contratos.index') }}" class="{{ request()->routeIs('recorrencia-contratos.*') ? 'active' : '' }}">Contratos</a></li>
                        @endif
                        @endcan

                    </ul>

                </div>

            </li>
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Atendimento'))
            @canany(['atendimentos_view'])

            <li class="side-nav-item {{ request()->routeIs(
                'atendimentos.*',
                'interrupcoes.*',
                'funcionamentos.*'
                ) ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarAtendimento" aria-expanded="{{ request()->routeIs(
                    'atendimentos.*',
                    'interrupcoes.*',
                    'funcionamentos.*'
                    ) ? 'true' : 'false' }}"
                    aria-controls="sidebarAtendimento"
                    class="side-nav-link">

                    <i class="ri-store-2-line"></i>
                    <span> Atendimento </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('atendimentos.*','interrupcoes.*','funcionamentos.*') ? 'show' : '' }}" id="sidebarAtendimento">

                    <ul class="side-nav-second-level">

                        <li>
                            <a href="{{ route('atendimentos.index') }}" class="{{ request()->routeIs('atendimentos.*') ? 'active' : '' }}">
                                Dias de Atendimento
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('interrupcoes.index') }}" class="{{ request()->routeIs('interrupcoes.*') ? 'active' : '' }}">
                                Interrupções
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('funcionamentos.index') }}" class="{{ request()->routeIs('funcionamentos.*') ? 'active' : '' }}">
                                Horário de Funcionamento
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(env("RESERVAS") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'Reservas'))
            @canany(['categoria_acomodacao_view', 'config_reserva_view', 'reserva_view'])

            <li class="side-nav-item {{ request()->routeIs('config-reserva.*', 'categoria-acomodacao.*', 'acomodacao.*', 'frigobar.*', 'reservas.*', 'produtos-reserva.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarReservas" aria-expanded="{{ request()->routeIs('config-reserva.*', 'categoria-acomodacao.*', 'acomodacao.*', 'frigobar.*', 'reservas.*', 'produtos-reserva.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-hotel-line"></i>
                    <span> Reservas </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('config-reserva.*', 'categoria-acomodacao.*', 'acomodacao.*', 'frigobar.*', 'reservas.*', 'produtos-reserva.*') ? 'show' : '' }}" id="sidebarReservas">

                    <ul class="side-nav-second-level">

                        @can('config_reserva_view')
                        <li><a href="{{ route('config-reserva.index') }}" class="{{ request()->routeIs('config-reserva.*') ? 'active' : '' }}">Configuração</a></li>
                        @endcan

                        @can('categoria_acomodacao_view')
                        <li><a href="{{ route('categoria-acomodacao.index') }}" class="{{ request()->routeIs('categoria-acomodacao.*') ? 'active' : '' }}">Categorias de acomodação</a></li>
                        @endcan

                        @can('acomodacao_view')
                        <li><a href="{{ route('acomodacao.index') }}" class="{{ request()->routeIs('acomodacao.*') ? 'active' : '' }}">Acomodações</a></li>
                        @endcan

                        @can('frigobar_view')
                        <li><a href="{{ route('frigobar.index') }}" class="{{ request()->routeIs('frigobar.*') ? 'active' : '' }}">Frigobares</a></li>
                        @endcan

                        @can('reserva_view')
                        <li><a href="{{ route('reservas.index') }}" class="{{ request()->routeIs('reservas.*') ? 'active' : '' }}">Reservas</a></li>
                        @endcan

                        <li><a href="{{ route('produtos-reserva.index') }}" class="{{ request()->routeIs('produtos-reserva.*') ? 'active' : '' }}">Produtos</a></li>

                    </ul>

                </div>

            </li>

            @endcan
            @endif
            @endif

            <!--  fim atendimento/serviços -->


            @if(__hasPessoasUsuarios(Auth::user()->empresa))
            <li class="side-nav-title">PESSOAS E USUÁRIOS</li>
            @endif
            @canany(['usuarios_view', 'controle_acesso_view'])

            <li class="side-nav-item {{ request()->routeIs('usuarios.*', 'controle-acesso.*', 'config-fiscal-usuario.*') ? 'menuitem-active mm-active' : '' }}" id="step6">
                <a data-bs-toggle="collapse" href="#sidebarUsuarios" aria-expanded="{{ request()->routeIs('usuarios.*', 'controle-acesso.*', 'config-fiscal-usuario.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-user-fill"></i>
                    <span>Usuários</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('usuarios.*', 'controle-acesso.*', 'config-fiscal-usuario.*') ? 'show' : '' }}" id="sidebarUsuarios">

                    <ul class="side-nav-second-level">

                        @can('usuarios_view')
                        <li><a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('controle_acesso_view')
                        <li><a href="{{ route('controle-acesso.index') }}" class="{{ request()->routeIs('controle-acesso.*') ? 'active' : '' }}">Controle de acesso</a></li>

                        <li><a href="{{ route('usuarios.historico-acesso') }}" class="{{ request()->routeIs('usuarios.historico-acesso') ? 'active' : '' }}">Histórico de acesso</a></li>
                        @endcan

                        @can('config_fiscal_usuario_view')
                        <li><a href="{{ route('config-fiscal-usuario.index') }}" class="{{ request()->routeIs('config-fiscal-usuario.*') ? 'active' : '' }}">Configuração fiscal</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany


            @canany(['clientes_view', 'fornecedores_view', 'transportadoras_view'])
            <li class="side-nav-item {{ request()->routeIs('clientes.*', 'fornecedores.*', 'transportadoras.*', 'motoristas.*') ? 'menuitem-active mm-active' : '' }}" id="step6">

                <a data-bs-toggle="collapse" href="#sidebarPessoas" aria-expanded="{{ request()->routeIs('clientes.*', 'fornecedores.*', 'transportadoras.*', 'motoristas.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-group-2-fill"></i>
                    <span>Pessoas</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('clientes.*', 'fornecedores.*', 'transportadoras.*', 'motoristas.*') ? 'show' : '' }}" id="sidebarPessoas">
                    <ul class="side-nav-second-level">

                        @can('clientes_view')
                        <li><a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">Clientes</a></li>
                        @endcan

                        @can('fornecedores_view')
                        <li><a href="{{ route('fornecedores.index') }}" class="{{ request()->routeIs('fornecedores.*') ? 'active' : '' }}">Fornecedores</a></li>
                        @endcan

                        @can('transportadoras_view')
                        <li><a href="{{ route('transportadoras.index') }}" class="{{ request()->routeIs('transportadoras.*') ? 'active' : '' }}">Transportadoras</a></li>
                        @endcan

                        @can('motorista_view')
                        <li><a href="{{ route('motoristas.index') }}" class="{{ request()->routeIs('motoristas.*') ? 'active' : '' }}">Motoristas</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            @canany(['funcionario_view', 'apuracao_mensal_view', 'ponto_jornada_view', 'ponto_funcionario_view', 'ponto_registro_view', 'ponto_ajuste_view', 'ponto_configuracao_view', 'comissao_margem_view'])
            <li class="side-nav-item {{ request()->routeIs('funcionarios.*', 'evento-funcionarios.*', 'ponto-jornada.*', 'ponto-configuracao.*', 'funcionario-eventos.*', 'ponto-funcionario.*', 'apuracao-mensal.*', 'ponto-registro.*', 'ponto-ajuste.*', 'ponto-relatorio.*', 'comissao-margem.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarGestaoPessoal" aria-expanded="{{ request()->routeIs('funcionarios.*', 'evento-funcionarios.*', 'ponto-jornada.*', 'ponto-configuracao.*', 'funcionario-eventos.*', 'ponto-funcionario.*', 'apuracao-mensal.*', 'ponto-registro.*', 'ponto-ajuste.*', 'ponto-relatorio.*', 'comissao-margem.*') ? 'true' : 'false' }}" aria-controls="sidebarGestaoPessoal" class="side-nav-link">
                    <i class="ri-folder-user-line"></i>
                    <span>Gestão Pessoal</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('funcionarios.*', 'evento-funcionarios.*', 'ponto-jornada.*', 'ponto-configuracao.*', 'funcionario-eventos.*', 'ponto-funcionario.*', 'apuracao-mensal.*', 'ponto-registro.*', 'ponto-ajuste.*', 'ponto-relatorio.*', 'comissao-margem.*') ? 'show' : '' }}" id="sidebarGestaoPessoal">

                    <ul class="side-nav-second-level">

                        @canany(['funcionario_view', 'apuracao_mensal_view', 'ponto_jornada_view', 'ponto_configuracao_view'])
                        <li>

                            <a data-bs-toggle="collapse" href="#gestaoPessoalCadastros" aria-expanded="{{ request()->routeIs('funcionarios.*', 'evento-funcionarios.*', 'ponto-jornada.*', 'ponto-configuracao.*') ? 'true' : 'false' }}">
                                <span> Cadastros </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('funcionarios.*', 'evento-funcionarios.*', 'ponto-jornada.*', 'ponto-configuracao.*') ? 'show' : '' }}" id="gestaoPessoalCadastros">

                                <ul class="side-nav-third-level">

                                    @can('funcionario_view')
                                    <li><a href="{{ route('funcionarios.index') }}" class="{{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">Funcionários</a></li>
                                    @endcan

                                    @can('apuracao_mensal_view')
                                    <li><a href="{{ route('evento-funcionarios.index') }}" class="{{ request()->routeIs('evento-funcionarios.*') ? 'active' : '' }}">Eventos</a></li>
                                    @endcan

                                    @can('ponto_jornada_view')
                                    <li><a href="{{ route('ponto-jornada.index') }}" class="{{ request()->routeIs('ponto-jornada.*') ? 'active' : '' }}">Jornadas</a></li>
                                    @endcan

                                    @can('ponto_configuracao_view')
                                    <li><a href="{{ route('ponto-configuracao.index') }}" class="{{ request()->routeIs('ponto-configuracao.*') ? 'active' : '' }}">Configuração de Ponto</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcanany

                        @canany(['apuracao_mensal_view', 'ponto_funcionario_view'])
                        <li>

                            <a data-bs-toggle="collapse" href="#gestaoPessoalVinculos" aria-expanded="{{ request()->routeIs('funcionario-eventos.*', 'ponto-funcionario.*') ? 'true' : 'false' }}">
                                <span> Vínculos </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('funcionario-eventos.*', 'ponto-funcionario.*') ? 'show' : '' }}" id="gestaoPessoalVinculos">

                                <ul class="side-nav-third-level">

                                    @can('apuracao_mensal_view')
                                    <li><a href="{{ route('funcionario-eventos.index') }}" class="{{ request()->routeIs('funcionario-eventos.*') ? 'active' : '' }}">Funcionários x Eventos</a></li>
                                    @endcan

                                    @can('ponto_funcionario_view')
                                    <li><a href="{{ route('ponto-funcionario.index') }}" class="{{ request()->routeIs('ponto-funcionario.*') ? 'active' : '' }}">Funcionários x Jornada</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcanany

                        @canany(['apuracao_mensal_view', 'ponto_registro_view', 'ponto_ajuste_view'])
                        <li>
                            <a data-bs-toggle="collapse" href="#gestaoPessoalOperacao" aria-expanded="{{ request()->routeIs('apuracao-mensal.*', 'ponto-registro.*', 'ponto-ajuste.*') ? 'true' : 'false' }}">
                                <span> Operação </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('apuracao-mensal.*', 'ponto-registro.*', 'ponto-ajuste.*') ? 'show' : '' }}" id="gestaoPessoalOperacao">

                                <ul class="side-nav-third-level">

                                    @can('apuracao_mensal_view')
                                    <li><a href="{{ route('apuracao-mensal.index') }}" class="{{ request()->routeIs('apuracao-mensal.*') ? 'active' : '' }}">Apuração Mensal</a></li>
                                    @endcan

                                    @if(__isAdmin())
                                    @can('ponto_registro_view')
                                    <li><a href="{{ route('ponto-registro.index') }}" class="{{ request()->routeIs('ponto-registro.*') ? 'active' : '' }}">Registros de Ponto</a></li>
                                    @endcan
                                    @endif

                                    @can('ponto_ajuste_view')
                                    <li><a href="{{ route('ponto-ajuste.index') }}" class="{{ request()->routeIs('ponto-ajuste.*') ? 'active' : '' }}">Ajustes de Ponto</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcanany

                        @canany(['ponto_registro_view', 'comissao_margem_view'])
                        <li>

                            <a data-bs-toggle="collapse" href="#gestaoPessoalRelatorios" aria-expanded="{{ request()->routeIs('ponto-relatorio.*', 'comissao-margem.*') ? 'true' : 'false' }}">
                                <span> Relatórios </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('ponto-relatorio.*', 'comissao-margem.*') ? 'show' : '' }}" id="gestaoPessoalRelatorios">

                                <ul class="side-nav-third-level">

                                    @if(__isAdmin())
                                    @can('ponto_registro_view')
                                    <li><a href="{{ route('ponto-relatorio.index') }}" class="{{ request()->routeIs('ponto-relatorio.*') ? 'active' : '' }}">Relatório de Ponto</a></li>
                                    @endcan
                                    @endif

                                    @can('comissao_margem_view')
                                    <li><a href="{{ route('comissao-margem.index') }}" class="{{ request()->routeIs('comissao-margem.*') ? 'active' : '' }}">Comissão por margem</a></li>
                                    @endcan

                                </ul>

                            </div>

                        </li>
                        @endcanany
                    </ul>
                </div>
            </li>
            @endcanany


            @if(__hasProducao(Auth::user()->empresa))
            <li class="side-nav-title">PRODUÇÃO</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'Ordem de Produção'))
            @canany([
            'ordem_producao_view',
            'ordem_producao_create',
            'almoxarifado_view'
            ])

            @php
            $opActive =
            request()->routeIs('ordem-producao.*') ||
            request()->routeIs('roteiro-producao.*') ||
            request()->routeIs('apontamento-producao.*') ||
            request()->routeIs('motivo-refugo.*') ||
            request()->routeIs('ordem-producao-requisicao.*');

            $almoxarifadoActive = request()->routeIs('ordem-producao-requisicao.*');
            @endphp

            <li class="side-nav-item {{ $opActive ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarOrdemProducao" aria-expanded="{{ $opActive ? 'true' : 'false' }}" aria-controls="sidebarOrdemProducao" class="side-nav-link">
                    <i class="ri-pencil-ruler-line"></i>
                    <span>Ordem de Produção</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ $opActive ? 'show' : '' }}" id="sidebarOrdemProducao">
                    <ul class="side-nav-second-level">

                        @can('ordem_producao_view')
                        <li>
                            <a href="{{ route('ordem-producao.index') }}" class="{{ request()->routeIs('ordem-producao.index') ? 'active' : '' }}" >
                                Listar
                            </a>
                        </li>
                        @endcan

                        @can('ordem_producao_create')
                        <li>
                            <a href="{{ route('ordem-producao.create') }}" class="{{ request()->routeIs('ordem-producao.create') ? 'active' : '' }}">
                                Nova Ordem
                            </a>
                        </li>
                        @endcan

                        @can('ordem_producao_view')
                        <li>
                            <a href="{{ route('roteiro-producao.index') }}" class="{{ request()->routeIs('roteiro-producao.*') ? 'active' : '' }}">
                                Roteiros
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('apontamento-producao.index') }}" class="{{ request()->routeIs('apontamento-producao.*') ? 'active' : '' }}">
                                Apontamentos
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('motivo-refugo.index') }}" class="{{ request()->routeIs('motivo-refugo.*') ? 'active' : '' }}">
                                Motivos de Refugo
                            </a>
                        </li>
                        @endcan

                        @can('almoxarifado_view')
                        <li class="{{ $almoxarifadoActive ? 'mm-active' : '' }}">

                            <a data-bs-toggle="collapse" href="#almoxarifado" aria-expanded="{{ $almoxarifadoActive ? 'true' : 'false' }}" aria-controls="almoxarifado">
                                <span>Almoxarifado</span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ $almoxarifadoActive ? 'show' : '' }}" id="almoxarifado" >
                                <ul class="side-nav-third-level">
                                    @if(app('router')->has('ordem-producao-requisicao.index'))
                                    <li>
                                        <a href="{{ route('ordem-producao-requisicao.index') }}" class="{{ request()->routeIs('ordem-producao-requisicao.*') ? 'active' : '' }}">
                                            Requisições OP
                                        </a>
                                    </li>
                                    @endif

                                </ul>
                            </div>

                        </li>
                        @endcan

                    </ul>
                </div>
            </li>

            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Gestão de Produção'))
            @canany(['gestao_producao_view'])
            <li class="side-nav-item {{ request()->routeIs('gestao-producao.*', 'centro-custo.*', 'setor.*', 'operacao.*', 'rotina-fabricacao.*', 'programacao-producao.*', 'maquina-producao.*', 'grupo-maquina-producao.*') ? 'menuitem-active mm-active' : '' }}">
                <a data-bs-toggle="collapse" href="#sidebarGestaoProducao" aria-expanded="{{ request()->routeIs('gestao-producao.*', 'centro-custo.*', 'setor.*', 'operacao.*', 'rotina-fabricacao.*', 'programacao-producao.*', 'maquina-producao.*', 'grupo-maquina-producao.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-compasses-fill"></i>
                    <span>Gestão de Produção</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('gestao-producao.*', 'centro-custo.*', 'setor.*', 'operacao.*', 'rotina-fabricacao.*', 'programacao-producao.*', 'maquina-producao.*', 'grupo-maquina-producao.*') ? 'show' : '' }}" id="sidebarGestaoProducao">
                    <ul class="side-nav-second-level">

                        @can('gestao_producao_view')
                        <li><a href="{{ route('gestao-producao.index') }}" class="{{ request()->routeIs('gestao-producao.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('gestao_producao_create')
                        <li><a href="{{ route('gestao-producao.create') }}" class="{{ request()->routeIs('gestao-producao.create') ? 'active' : '' }}">Nova Produção</a></li>
                        @endcan

                        @can('setor_view')
                        <li><a href="{{ route('centro-custo.index') }}" class="{{ request()->routeIs('centro-custo.*') ? 'active' : '' }}">Centro de Custos</a></li>
                        @endcan

                        @can('setor_view')
                        <li><a href="{{ route('setor.index') }}" class="{{ request()->routeIs('setor.*') ? 'active' : '' }}">Setores</a></li>
                        @endcan

                        @if(app('router')->has('grupo-maquina-producao.index'))
                        <li><a href="{{ route('grupo-maquina-producao.index') }}" class="{{ request()->routeIs('grupo-maquina-producao.index') ? 'active' : '' }}">Grupo de Máquinas</a></li>
                        @endif
                        @if(app('router')->has('maquina-producao.index'))
                        <li><a href="{{ route('maquina-producao.index') }}" class="{{ request()->routeIs('maquina-producao.index') ? 'active' : '' }}">Máquinas</a></li>
                        @endif

                        @can('setor_view')
                        <li><a href="{{ route('operacao.index') }}" class="{{ request()->routeIs('operacao.*') ? 'active' : '' }}">Operações</a></li>
                        @endcan

                        @can('rotina_fabricacao_view')
                        <li><a href="{{ route('rotina-fabricacao.index') }}" class="{{ request()->routeIs('rotina-fabricacao.*') ? 'active' : '' }}">Rotina de Fabricação</a></li>
                        <li><a href="{{ route('programacao-producao.index') }}" class="{{ request()->routeIs('programacao-producao.*') ? 'active' : '' }}">Programação de Produção</a></li>
                        @endcan



                    </ul>
                </div>
            </li>
            @endcanany
            @endif
            <!-- fim produção -->

            @if(__hasVendasComprasComercial(Auth::user()->empresa) && !__isContador())
            <li class="side-nav-title">VENDAS/COMPRAS COMERCIAL</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'PDV'))
            @canany(['pdv_view'])

            <li class="side-nav-item {{ request()->routeIs('frontbox.*', 'venda-temporaria.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarPDV" aria-expanded="{{ request()->routeIs('frontbox.*', 'venda-temporaria.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-shopping-cart-fill"></i>
                    <span>PDV</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('frontbox.*', 'venda-temporaria.*') ? 'show' : '' }}" id="sidebarPDV">

                    <ul class="side-nav-second-level">

                        @can('pdv_view')
                        <li><a href="{{ route('frontbox.index') }}" class="{{ request()->routeIs('frontbox.index') ? 'active' : '' }}">Listar</a></li>

                        @if(__isAdmin())
                        <li><a href="{{ route('venda-temporaria.index') }}" class="{{ request()->routeIs('venda-temporaria.*') ? 'active' : '' }}">Vendas temporárias</a></li>
                        @endif
                        @endcan

                        @can('pdv_create')
                        <li class="d-none d-sm-inline-block"><a href="{{ route('frontbox.create') }}" class="{{ request()->routeIs('frontbox.create') ? 'active' : '' }}">PDV</a></li>
                        @endcan

                        @if(env("PDVCOMANDA") == 1)
                        @can('pdv_create')
                        <li><a href="{{ route('frontbox.mesas') }}" class="{{ request()->routeIs('frontbox.mesas') ? 'active' : '' }}">Mesas/Comandas</a></li>
                        @endcan
                        @endif

                        @if(__isAdmin())
                        <li><a href="{{ route('frontbox.logs') }}" class="{{ request()->routeIs('frontbox.logs') ? 'active' : '' }}">Logs de PDV</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Vendas'))
            @canany(['nfe_view', 'orcamento_view'])

            <li class="side-nav-item {{ request()->routeIs('vendas.*', 'nfe.*', 'orcamentos.*', 'nfe-xml.*', 'dashboard-fiscal.*', 'faturamento-nfe.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarNfe" aria-expanded="{{ request()->routeIs('vendas.*', 'nfe.*', 'orcamentos.*', 'nfe-xml.*', 'dashboard-fiscal.*', 'faturamento-nfe.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-file-list-fill"></i>
                    <span>Vendas</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('vendas.*', 'nfe.*', 'orcamentos.*', 'nfe-xml.*') ? 'show' : '' }}" id="sidebarNfe">
                    <ul class="side-nav-second-level">
                        @can('nfe_view')
                        <li><a href="{{ route('vendas.index') }}" class="{{ request()->routeIs('vendas.*') ? 'active' : '' }}">Todas as Vendas</a></li>
                        @endcan

                        @can('nfe_view')
                        <li><a href="{{ route('nfe.index') }}" class="{{ request()->routeIs('nfe.index') ? 'active' : '' }}">Vendas pedido</a></li>
                        @endcan

                        @can('nfe_create')
                        <li><a href="{{ route('nfe.create') }}" class="{{ request()->routeIs('nfe.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                        @if(__isPlanoFiscal())
                        @can('nfe_inutiliza')
                        <li><a href="{{ route('nfe.inutilizar') }}" class="{{ request()->routeIs('nfe.inutilizar') ? 'active' : '' }}">Inutilizar NFe</a></li>
                        @endcan
                        @endif

                        @can('orcamento_view')
                        <li><a href="{{ route('orcamentos.index') }}" class="{{ request()->routeIs('orcamentos.*') ? 'active' : '' }}">Orçamentos</a></li>
                        @endcan

                        @can('nfe_view')
                        <li><a href="{{ route('faturamento-nfe.index') }}" class="{{ request()->routeIs('faturamento-nfe.index') ? 'active' : '' }}">Faturamento (NF-e)</a></li>

                        <li><a href="{{ route('dashboard-fiscal.index') }}" class="{{ request()->routeIs('dashboard-fiscal.*') ? 'active' : '' }}">Dashboard Fiscal</a></li>
                        @endcan

                        @if(__isPlanoFiscal())
                        @can('arquivos_xml_view')
                        <li><a href="{{ route('nfe-xml.index') }}" class="{{ request()->routeIs('nfe-xml.*') ? 'active' : '' }}">Arquivos XML</a></li>
                        @endcan
                        @endif

                        @can('nfe_create')
                        <li><a href="{{ route('nfe.import-zip') }}" class="{{ request()->routeIs('nfe.import-zip') ? 'active' : '' }}">Importar XML</a></li>
                        @endcan

                        @can('metas_view')
                        <li><a href="{{ route('nfe.metas') }}" class="{{ request()->routeIs('nfe.metas') ? 'active' : '' }}">Metas</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Compras'))
            @canany(['compras_view', 'manifesto_view', 'cotacao_view'])

            <li class="side-nav-item {{ request()->routeIs('compras.*', 'manifesto.*', 'cotacoes.*', 'nfe-entrada-xml.*', 'nfe-importa-xml.*', 'relacao-dados-fornecedor.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarCompra" aria-expanded="{{ request()->routeIs('compras.*', 'manifesto.*', 'cotacoes.*', 'nfe-entrada-xml.*', 'nfe-importa-xml.*', 'relacao-dados-fornecedor.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-logout-box-line"></i>
                    <span>Compras</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('compras.*', 'manifesto.*', 'cotacoes.*', 'nfe-entrada-xml.*', 'nfe-importa-xml.*', 'relacao-dados-fornecedor.*') ? 'show' : '' }}" id="sidebarCompra">

                    <ul class="side-nav-second-level">

                        @can('compras_view')
                        <li><a href="{{ route('compras.index') }}" class="{{ request()->routeIs('compras.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('compras_create')
                        <li><a href="{{ route('compras.create') }}" class="{{ request()->routeIs('compras.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                        <li><a href="{{ route('compras.xml') }}" class="{{ request()->routeIs('compras.xml') ? 'active' : '' }}">Importar XML</a></li>

                        <li><a href="{{ route('compras.validade') }}" class="{{ request()->routeIs('compras.validade') ? 'active' : '' }}">Produtos com validade</a></li>

                        @can('manifesto_view')
                        <li><a href="{{ route('manifesto.index') }}" class="{{ request()->routeIs('manifesto.*') ? 'active' : '' }}">Manifesto</a></li>
                        @endcan

                        @can('cotacao_view')
                        <li><a href="{{ route('cotacoes.index') }}" class="{{ request()->routeIs('cotacoes.*') ? 'active' : '' }}">Cotação</a></li>
                        @endcan

                        @if(__isPlanoFiscal())
                        @can('arquivos_xml_view')
                        <li><a href="{{ route('nfe-entrada-xml.index') }}" class="{{ request()->routeIs('nfe-entrada-xml.*') ? 'active' : '' }}">Arquivos XML Emitidos</a></li>
                        @endcan
                        @endif

                        @if(__isPlanoFiscal())
                        @can('arquivos_xml_view')
                        <li><a href="{{ route('nfe-importa-xml.index') }}" class="{{ request()->routeIs('nfe-importa-xml.*') ? 'active' : '' }}">Arquivos XML Importados</a></li>
                        @endcan
                        @endif

                        @can('relacao_dados_fornecedor_view')
                        <li><a href="{{ route('relacao-dados-fornecedor.index') }}" class="{{ request()->routeIs('relacao-dados-fornecedor.*') ? 'active' : '' }}">Relação dados fornecedor</a></li>
                        @endcan

                        @can('log_depara_xml_view')
                        @if(app('router')->has('log-depara-xml.index'))
                        <li>
                            <a href="{{ route('log-depara-xml.index') }}" class="{{ request()->routeIs('log-depara-xml.*') ? 'active' : '' }}">
                                Logs conversão XML
                            </a>
                        </li>
                        @endif
                        @endcan

                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(!__isContador())
            @if(__isPlanoFiscal())
            @canany(['devolucao_view'])

            <li class="side-nav-item {{ request()->routeIs('devolucao.*', 'trocas.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarDevolucao" aria-expanded="{{ request()->routeIs('devolucao.*', 'trocas.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-arrow-go-back-fill"></i>
                    <span>Devolução</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('devolucao.*', 'trocas.*') ? 'show' : '' }}" id="sidebarDevolucao">

                    <ul class="side-nav-second-level">

                        @can('devolucao_view')
                        <li><a href="{{ route('devolucao.index') }}" class="{{ request()->routeIs('devolucao.index') ? 'active' : '' }}">Lista devolução XML</a></li>
                        @endcan

                        @can('devolucao_create')
                        <li><a href="{{ route('devolucao.xml') }}" class="{{ request()->routeIs('devolucao.xml') ? 'active' : '' }}">Nova devolução XML</a></li>
                        @endcan

                        @can('troca_view')
                        <li><a href="{{ route('trocas.index') }}" class="{{ request()->routeIs('trocas.*') ? 'active' : '' }}">Trocas/Devolução</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            @endif
            @endif


            @if(__isActivePlan(Auth::user()->empresa, 'NFCe'))
            @canany(['nfce_view'])

            <li class="side-nav-item {{ request()->routeIs('nfce.*', 'nfce-xml.*', 'nfce-contigencia.*', 'faturamento-nfce.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarNfce" aria-expanded="{{ request()->routeIs('nfce.*', 'nfce-xml.*', 'nfce-contigencia.*', 'faturamento-nfce.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-bill-line"></i>
                    <span>NFCe</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('nfce.*', 'nfce-xml.*', 'nfce-contigencia.*', 'faturamento-nfce.*') ? 'show' : '' }}" id="sidebarNfce">

                    <ul class="side-nav-second-level">

                        @can('nfce_view')
                        <li><a href="{{ route('nfce.index') }}" class="{{ request()->routeIs('nfce.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('nfce_create')
                        <li><a href="{{ route('nfce.create') }}" class="{{ request()->routeIs('nfce.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                        @if(__isPlanoFiscal())

                        @can('nfce_inutiliza')
                        <li><a href="{{ route('nfce.inutilizar') }}" class="{{ request()->routeIs('nfce.inutilizar') ? 'active' : '' }}">Inutilizar</a></li>
                        @endcan

                        @can('arquivos_xml_view')
                        <li><a href="{{ route('nfce-xml.index') }}" class="{{ request()->routeIs('nfce-xml.*') ? 'active' : '' }}">Arquivos XML</a></li>
                        @endcan

                        @endif

                        <li><a href="{{ route('nfce.import-zip') }}" class="{{ request()->routeIs('nfce.import-zip') ? 'active' : '' }}">Importar XML</a></li>

                        <li><a href="{{ route('nfce-contigencia.index') }}" class="{{ request()->routeIs('nfce-contigencia.*') ? 'active' : '' }}">Envio Contingência</a></li>

                        @can('nfce_view')
                        <li><a href="{{ route('faturamento-nfce.index') }}" class="{{ request()->routeIs('faturamento-nfe.index') ? 'active' : '' }}">Faturamento (NFC-e)</a></li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Pré venda'))
            @canany(['pre_venda_view'])
            <li class="side-nav-item {{ request()->routeIs('pre-venda.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarPreVenda" aria-expanded="{{ request()->routeIs('pre-venda.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-list-ordered"></i>
                    <span>Pré Venda</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('pre-venda.*') ? 'show' : '' }}" id="sidebarPreVenda">
                    <ul class="side-nav-second-level">

                        @can('pre_venda_view')
                        <li><a href="{{ route('pre-venda.index') }}" class="{{ request()->routeIs('pre-venda.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('pre_venda_create')
                        <li><a href="{{ route('pre-venda.create') }}" class="{{ request()->routeIs('pre-venda.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endcanany
            @endif
            <!-- fim vendas/compras comercial -->

            @if(__hasFinanceiro(Auth::user()->empresa))
            <li class="side-nav-title">FINANCEIRO</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'Financeiro'))
            @canany(['conta_pagar_view', 'conta_receber_view', 'relatorio_view', 'caixa_view', 'contas_empresa_view', 'contas_boleto_view', 'boleto_view', 'taxa_pagamento_view'])

            <li class="side-nav-item {{ request()->routeIs('financeiro.*', 'caixa.*', 'conta-pagar.*', 'fechamento-mensal.*', 'conta-receber.*', 'categoria-conta.*', 'relatorios.*', 'taxa-cartao.*', 'plano-contas.*', 'contas-empresa.*', 'contas-boleto.*', 'boleto.*', 'sicredi-config.*', 'asaas-config.*', 'cobranca-bancaria.*', 'sicoob-config.*',) ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarPagar" aria-expanded="{{ request()->routeIs('financeiro.*', 'caixa.*', 'conta-pagar.*', 'fechamento-mensal.*', 'conta-receber.*', 'categoria-conta.*', 'relatorios.*', 'taxa-cartao.*', 'plano-contas.*', 'contas-empresa.*', 'contas-boleto.*', 'boleto.*', 'sicredi-config.*', 'sicoob-config.*', 'asaas-config.*', 'cobranca-bancaria.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-money-dollar-box-fill"></i>
                    <span>Financeiro</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('financeiro.*', 'caixa.*', 'conta-pagar.*', 'fechamento-mensal.*', 'conta-receber.*', 'categoria-conta.*', 'relatorios.*', 'taxa-cartao.*', 'plano-contas.*', 'contas-empresa.*', 'contas-boleto.*', 'boleto.*', 'sicredi-config.*', 'asaas-config.*', 'cobranca-bancaria.*', 'sicoob-config.*') ? 'show' : '' }}" id="sidebarPagar">

                    <ul class="side-nav-second-level">

                        @can('caixa_view')
                        <li><a href="{{ route('financeiro.dashboard') }}" class="{{ request()->routeIs('financeiro.dashboard') ? 'active' : '' }}">Dashboard</a></li>

                        <li>

                            <a data-bs-toggle="collapse" href="#caixa" aria-expanded="{{ request()->routeIs('caixa.*') ? 'true' : 'false' }}">
                                <span> Caixa </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('caixa.*') ? 'show' : '' }}" id="caixa">

                                <ul class="side-nav-third-level">
                                    <li><a href="{{ route('caixa.index') }}" class="{{ request()->routeIs('caixa.index') ? 'active' : '' }}">Movimentação</a></li>
                                    <li><a href="{{ route('caixa.create') }}" class="{{ request()->routeIs('caixa.create') ? 'active' : '' }}">Abrir caixa</a></li>
                                    <li><a href="{{ route('caixa.list') }}" class="{{ request()->routeIs('caixa.list') ? 'active' : '' }}">Listar</a></li>
                                </ul>

                            </div>

                        </li>
                        @endcan

                        @canany(['conta_pagar_view', 'conta_pagar_create'])
                        <li>

                            <a data-bs-toggle="collapse" href="#pagar" aria-expanded="{{ request()->routeIs('conta-pagar.*') ? 'true' : 'false' }}">
                                <span> Contas a pagar </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('conta-pagar.*') ? 'show' : '' }}" id="pagar">

                                <ul class="side-nav-third-level">

                                    @can('conta_pagar_view')
                                    <li><a href="{{ route('conta-pagar.index') }}" class="{{ request()->routeIs('conta-pagar.index') ? 'active' : '' }}">Listar</a></li>
                                    @endcan

                                    @can('conta_pagar_create')
                                    <li><a href="{{ route('conta-pagar.create') }}" class="{{ request()->routeIs('conta-pagar.create') ? 'active' : '' }}">Nova conta</a></li>
                                    @endcan

                                </ul>

                            </div>

                        </li>
                        @endcanany

                        @canany(['fechamento_mensal_view', 'fechamento_mensal_create'])
                        <li>

                            <a data-bs-toggle="collapse" href="#fechamento" aria-expanded="{{ request()->routeIs('fechamento-mensal.*') ? 'true' : 'false' }}">
                                <span> Fechamento mensal </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('fechamento-mensal.*') ? 'show' : '' }}" id="fechamento">

                                <ul class="side-nav-third-level">

                                    @can('conta_pagar_view')
                                    <li><a href="{{ route('fechamento-mensal.historic') }}" class="{{ request()->routeIs('fechamento-mensal.historic') ? 'active' : '' }}">Histórico</a></li>
                                    @endcan

                                    @can('conta_pagar_create')
                                    <li><a href="{{ route('fechamento-mensal.index') }}" class="{{ request()->routeIs('fechamento-mensal.index') ? 'active' : '' }}">Novo fechamento</a></li>
                                    @endcan

                                </ul>

                            </div>

                        </li>
                        @endcanany

                        @canany(['conta_receber_view', 'conta_receber_create'])
                        <li>

                            <a data-bs-toggle="collapse" href="#receber" aria-expanded="{{ request()->routeIs('conta-receber.*') ? 'true' : 'false' }}">
                                <span> Contas a receber </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('conta-receber.*') ? 'show' : '' }}" id="receber">

                                <ul class="side-nav-third-level">

                                    @can('conta_receber_view')
                                    <li><a href="{{ route('conta-receber.index') }}" class="{{ request()->routeIs('conta-receber.index') ? 'active' : '' }}">Listar</a></li>
                                    @endcan

                                    @can('conta_receber_create')
                                    <li><a href="{{ route('conta-receber.create') }}" class="{{ request()->routeIs('conta-receber.create') ? 'active' : '' }}">Nova conta</a></li>
                                    @endcan

                                </ul>

                            </div>

                        </li>
                        @endcanany

                        @can('categoria_conta_view')
                        <li><a href="{{ route('categoria-conta.index') }}" class="{{ request()->routeIs('categoria-conta.*') ? 'active' : '' }}">Categorias de conta</a></li>
                        @endcan

                        @can('relatorio_view')
                        <li><a href="{{ route('relatorios.index') }}" class="{{ request()->routeIs('relatorios.*') ? 'active' : '' }}">Relatórios</a></li>
                        @endcan

                        @can('taxa_pagamento_view')
                        <li><a href="{{ route('taxa-cartao.index') }}" class="{{ request()->routeIs('taxa-cartao.*') ? 'active' : '' }}">Taxas de pagamento</a></li>
                        @endcan

                        @can('plano_contas_view')
                        <li><a href="{{ route('plano-contas.index') }}" class="{{ request()->routeIs('plano-contas.*') ? 'active' : '' }}">Plano de contas</a></li>
                        @endcan

                        @can('contas_empresa_view')
                        <li><a href="{{ route('contas-empresa.index') }}" class="{{ request()->routeIs('contas-empresa.*') ? 'active' : '' }}">Contas da empresa</a></li>
                        @endcan

                        @can('contas_boleto_view')
                        <li><a href="{{ route('contas-boleto.index') }}" class="{{ request()->routeIs('contas-boleto.*') ? 'active' : '' }}">Contas para boleto</a></li>
                        @endcan

                        @can('boleto_view')
                        <li><a href="{{ route('boleto.index') }}" class="{{ request()->routeIs('boleto.*') ? 'active' : '' }}">Boletos</a></li>

                        <li>

                            <a data-bs-toggle="collapse" href="#config-boleto" aria-expanded="{{ request()->routeIs('sicredi-config.*', 'asaas-config.*', 'sicoob-config.*') ? 'true' : 'false' }}">
                                <span> Configuração boleto </span>
                                <span class="menu-arrow"></span>
                            </a>

                            <div class="collapse {{ request()->routeIs('sicredi-config.*', 'asaas-config.*') ? 'show' : '' }}" id="config-boleto">

                                <ul class="side-nav-third-level">
                                    <li><a href="{{ route('sicredi-config.index') }}" class="{{ request()->routeIs('sicredi-config.*') ? 'active' : '' }}">Sicredi</a></li>
                                    <li><a href="{{ route('asaas-config.index') }}" class="{{ request()->routeIs('asaas-config.*') ? 'active' : '' }}">Asaas</a></li>
                                    @if(app('router')->has('sicoob-config.index'))
                                    <li><a href="{{ route('sicoob-config.index') }}" class="{{ request()->routeIs('sicoob-config.*') ? 'active' : '' }}">Sicoob</a></li>
                                    @endif
                                </ul>

                            </div>

                        </li>

                        <li><a href="{{ route('cobranca-bancaria.index') }}" class="{{ request()->routeIs('cobranca-bancaria.*') ? 'active' : '' }}">Cobrança bancária</a></li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Planejamento de Custos'))
            @canany(['planejamento_custo_view', 'projeto_custo_view'])

            <li class="side-nav-item {{ request()->routeIs('projeto-custo.*', 'planejamento-custo.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarPlanejamentoCustos" aria-expanded="{{ request()->routeIs('projeto-custo.*', 'planejamento-custo.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-dashboard-fill"></i>
                    <span>Planejamento de Custos</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('projeto-custo.*', 'planejamento-custo.*') ? 'show' : '' }}" id="sidebarPlanejamentoCustos">

                    <ul class="side-nav-second-level">

                        @can('projeto_custo_view')
                        <li><a href="{{ route('projeto-custo.index') }}" class="{{ request()->routeIs('projeto-custo.*') ? 'active' : '' }}">Projetos</a></li>
                        @endcan

                        @can('planejamento_custo_view')
                        <li><a href="{{ route('planejamento-custo.index') }}" class="{{ request()->routeIs('planejamento-custo.*') ? 'active' : '' }}">Planejamentos</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan
            @endif
            <!--  fim financeiro -->


            @if(__hasComercioDigital(Auth::user()->empresa))
            <li class="side-nav-title">COMÉRCIO DIGITAL</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'Cardapio'))
            @can('cardapio_view')

            <li class="side-nav-item {{ request()->routeIs('config-cardapio.*', 'produtos-cardapio.*', 'categoria-adicional.*', 'adicionais.*', 'pedidos-cardapio.*', 'mesas.*', 'pedido-cozinha.*', 'impressao-pedido.*', 'carrossel.*', 'avaliacao-cardapio.*', 'tamanhos-pizza.*', 'atendimento-garcom.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarCardapio" aria-expanded="{{ request()->routeIs('config-cardapio.*', 'produtos-cardapio.*', 'categoria-adicional.*', 'adicionais.*', 'pedidos-cardapio.*', 'mesas.*', 'pedido-cozinha.*', 'impressao-pedido.*', 'carrossel.*', 'avaliacao-cardapio.*', 'tamanhos-pizza.*', 'atendimento-garcom.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-restaurant-2-line"></i>
                    <span>Cardápio</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('config-cardapio.*', 'produtos-cardapio.*', 'categoria-adicional.*', 'adicionais.*', 'pedidos-cardapio.*', 'mesas.*', 'pedido-cozinha.*', 'impressao-pedido.*', 'carrossel.*', 'avaliacao-cardapio.*', 'tamanhos-pizza.*', 'atendimento-garcom.*') ? 'show' : '' }}" id="sidebarCardapio">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('config-cardapio.index') }}" class="{{ request()->routeIs('config-cardapio.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('produtos-cardapio.categorias') }}" class="{{ request()->routeIs('produtos-cardapio.categorias') ? 'active' : '' }}">Categorias</a></li>

                        <li><a href="{{ route('produtos-cardapio.index') }}" class="{{ request()->routeIs('produtos-cardapio.index') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('categoria-adicional.index') }}" class="{{ request()->routeIs('categoria-adicional.*') ? 'active' : '' }}">Categorias de adicional</a></li>

                        <li><a href="{{ route('adicionais.index') }}" class="{{ request()->routeIs('adicionais.*') ? 'active' : '' }}">Adicionais</a></li>

                        <li><a href="{{ route('pedidos-cardapio.index') }}" class="{{ request()->routeIs('pedidos-cardapio.index') ? 'active' : '' }}">Comandas</a></li>

                        <li><a href="{{ route('mesas.index') }}" class="{{ request()->routeIs('mesas.*') ? 'active' : '' }}">Cadastrar mesas</a></li>

                        <li><a href="{{ route('pedido-cozinha.index') }}" class="{{ request()->routeIs('pedido-cozinha.*') ? 'active' : '' }}">Controle de pedidos</a></li>

                        <li><a href="{{ route('impressao-pedido.index') }}" class="{{ request()->routeIs('impressao-pedido.*') ? 'active' : '' }}">Controle de impressão</a></li>

                        <li><a href="{{ route('carrossel.index') }}" class="{{ request()->routeIs('carrossel.*') ? 'active' : '' }}">Carrossel destaque</a></li>

                        <li><a href="{{ route('avaliacao-cardapio.index') }}" class="{{ request()->routeIs('avaliacao-cardapio.*') ? 'active' : '' }}">Avaliações</a></li>

                        <li><a href="{{ route('tamanhos-pizza.index') }}" class="{{ request()->routeIs('tamanhos-pizza.*') ? 'active' : '' }}">Tamanhos de pizza</a></li>

                        <li><a href="{{ route('atendimento-garcom.index') }}" class="{{ request()->routeIs('atendimento-garcom.*') ? 'active' : '' }}">Atendimentos garçom</a></li>

                        <li><a href="{{ route('pedidos-cardapio.historico') }}" class="{{ request()->routeIs('pedidos-cardapio.historico') ? 'active' : '' }}">Histórico</a></li>

                        @if(file_exists(public_path('app.apk')))
                        <li><a href="{{ route('config-cardapio.download') }}" class="{{ request()->routeIs('config-cardapio.download') ? 'active' : '' }}">Download APP</a></li>
                        @endif

                    </ul>
                </div>
            </li>
            @endcan
            @endif

            @if(env("MARKETPLACE") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'Delivery'))
            @can('delivery_view')

            <li class="side-nav-item {{ request()->routeIs('config-marketplace.*', 'pedidos-delivery.*', 'produtos-delivery.*', 'servico-marketplace.*', 'servicos-marketplace.*', 'funcionamento-delivery.*', 'bairros-empresa.*', 'categoria-adicional.*', 'adicionais.*', 'destaque-marketplace.*', 'cupom-desconto.*', 'tamanhos-pizza.*', 'motoboys.*', 'pedido-cozinha.*', 'impressao-pedido.*', 'clientes-delivery.*', 'config-agendamento.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarMarketPlace" aria-expanded="{{ request()->routeIs('config-marketplace.*', 'pedidos-delivery.*', 'produtos-delivery.*', 'servico-marketplace.*', 'servicos-marketplace.*', 'funcionamento-delivery.*', 'bairros-empresa.*', 'categoria-adicional.*', 'adicionais.*', 'destaque-marketplace.*', 'cupom-desconto.*', 'tamanhos-pizza.*', 'motoboys.*', 'pedido-cozinha.*', 'impressao-pedido.*', 'clientes-delivery.*', 'config-agendamento.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-store-2-line"></i>
                    <span>Delivery/Marketplace</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('config-marketplace.*', 'pedidos-delivery.*', 'produtos-delivery.*', 'servico-marketplace.*', 'servicos-marketplace.*', 'funcionamento-delivery.*', 'bairros-empresa.*', 'categoria-adicional.*', 'adicionais.*', 'destaque-marketplace.*', 'cupom-desconto.*', 'tamanhos-pizza.*', 'motoboys.*', 'pedido-cozinha.*', 'impressao-pedido.*', 'clientes-delivery.*', 'config-agendamento.*') ? 'show' : '' }}" id="sidebarMarketPlace">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('config-marketplace.index') }}" class="{{ request()->routeIs('config-marketplace.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('pedidos-delivery.index') }}" class="{{ request()->routeIs('pedidos-delivery.*') ? 'active' : '' }}">Pedidos</a></li>

                        <li><a href="{{ route('produtos-delivery.categorias') }}" class="{{ request()->routeIs('produtos-delivery.categorias') ? 'active' : '' }}">Categorias de produto</a></li>

                        <li><a href="{{ route('servico-marketplace.categorias') }}" class="{{ request()->routeIs('servico-marketplace.*') ? 'active' : '' }}">Categorias de serviço</a></li>

                        <li><a href="{{ route('produtos-delivery.index') }}" class="{{ request()->routeIs('produtos-delivery.index') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('servicos-marketplace.index') }}" class="{{ request()->routeIs('servicos-marketplace.*') ? 'active' : '' }}">Serviços</a></li>

                        <li><a href="{{ route('funcionamento-delivery.index') }}" class="{{ request()->routeIs('funcionamento-delivery.*') ? 'active' : '' }}">Funcionamento</a></li>

                        <li><a href="{{ route('bairros-empresa.index') }}" class="{{ request()->routeIs('bairros-empresa.*') ? 'active' : '' }}">Bairros</a></li>

                        <li><a href="{{ route('categoria-adicional.index') }}" class="{{ request()->routeIs('categoria-adicional.*') ? 'active' : '' }}">Categorias de adicional</a></li>

                        <li><a href="{{ route('adicionais.index') }}" class="{{ request()->routeIs('adicionais.*') ? 'active' : '' }}">Adicionais</a></li>

                        <li><a href="{{ route('destaque-marketplace.index') }}" class="{{ request()->routeIs('destaque-marketplace.*') ? 'active' : '' }}">Destaques</a></li>

                        <li><a href="{{ route('cupom-desconto.index') }}" class="{{ request()->routeIs('cupom-desconto.*') ? 'active' : '' }}">Cupom de desconto</a></li>

                        <li><a href="{{ route('tamanhos-pizza.index') }}" class="{{ request()->routeIs('tamanhos-pizza.*') ? 'active' : '' }}">Tamanhos de pizza</a></li>

                        <li><a href="{{ route('motoboys.index') }}" class="{{ request()->routeIs('motoboys.*') ? 'active' : '' }}">Motoboys</a></li>

                        <li><a href="{{ route('pedido-cozinha.index') }}" class="{{ request()->routeIs('pedido-cozinha.*') ? 'active' : '' }}">Controle de pedidos</a></li>

                        <li><a href="{{ route('impressao-pedido.index') }}" class="{{ request()->routeIs('impressao-pedido.*') ? 'active' : '' }}">Controle de impressão</a></li>

                        <li><a href="{{ route('clientes-delivery.index') }}" class="{{ request()->routeIs('clientes-delivery.*') ? 'active' : '' }}">Clientes</a></li>

                        <li><a href="{{ route('config-agendamento.index') }}" class="{{ request()->routeIs('config-agendamento.*') ? 'active' : '' }}">Config. de agendamento</a></li>

                        <li><a target="_blank" href="{{ route('config-marketplace.loja') }}">Ver loja</a></li>
                    </ul>
                </div>
            </li>
            @endcan
            @endif
            @endif

            @if(env("ECOMMERCE") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'Ecommerce'))
            @can('ecommerce_view')

            <li class="side-nav-item {{ request()->routeIs('config-ecommerce.*', 'produtos-ecommerce.*', 'pedidos-ecommerce.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarEcommerce" aria-expanded="{{ request()->routeIs('config-ecommerce.*', 'produtos-ecommerce.*', 'pedidos-ecommerce.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-store-3-line"></i>
                    <span> Ecommerce </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('config-ecommerce.*', 'produtos-ecommerce.*', 'pedidos-ecommerce.*') ? 'show' : '' }}" id="sidebarEcommerce">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('config-ecommerce.index') }}" class="{{ request()->routeIs('config-ecommerce.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('produtos-ecommerce.categorias') }}" class="{{ request()->routeIs('produtos-ecommerce.categorias') ? 'active' : '' }}">Categorias de produtos</a></li>

                        <li><a href="{{ route('produtos-ecommerce.index') }}" class="{{ request()->routeIs('produtos-ecommerce.index') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('pedidos-ecommerce.index') }}" class="{{ request()->routeIs('pedidos-ecommerce.*') ? 'active' : '' }}">Pedidos</a></li>

                        <li><a target="_blank" href="{{ route('config-ecommerce.site') }}">Ver site</a></li>
                    </ul>
                </div>
            </li>
            @endcan
            @endif
            @endif

            @if(env("MERCADOLIVRE") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'Mercado Livre'))
            @can('mercado_livre_view')

            <li class="side-nav-item {{ request()->routeIs('mercado-livre-config.*', 'mercado-livre.*', 'mercado-livre-perguntas.*', 'mercado-livre-pedidos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarMercadoLivre" aria-expanded="{{ request()->routeIs('mercado-livre-config.*', 'mercado-livre.*', 'mercado-livre-perguntas.*', 'mercado-livre-pedidos.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-box-1-line"></i>
                    <span> Mercado Livre </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('mercado-livre-config.*', 'mercado-livre.*', 'mercado-livre-perguntas.*', 'mercado-livre-pedidos.*') ? 'show' : '' }}" id="sidebarMercadoLivre">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('mercado-livre-config.index') }}" class="{{ request()->routeIs('mercado-livre-config.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('mercado-livre.produtos-news') }}" class="{{ request()->routeIs('mercado-livre.*') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('mercado-livre-perguntas.index') }}" class="{{ request()->routeIs('mercado-livre-perguntas.*') ? 'active' : '' }}">Perguntas</a></li>

                        <li><a href="{{ route('mercado-livre-pedidos.index') }}" class="{{ request()->routeIs('mercado-livre-pedidos.*') ? 'active' : '' }}">Pedidos</a></li>
                    </ul>
                </div>
            </li>
            @endcan
            @endif
            @endif

            @if(env("WOOCOMMERCE") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'Woocommerce'))
            @can('woocommerce_view')

            <li class="side-nav-item {{ request()->routeIs('woocommerce-config.*', 'woocommerce-categorias.*', 'woocommerce-produtos.*', 'woocommerce-pedidos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarWoocommerce" aria-expanded="{{ request()->routeIs('woocommerce-config.*', 'woocommerce-categorias.*', 'woocommerce-produtos.*', 'woocommerce-pedidos.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-wordpress-fill"></i>
                    <span> Woocommerce </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('woocommerce-config.*', 'woocommerce-categorias.*', 'woocommerce-produtos.*', 'woocommerce-pedidos.*') ? 'show' : '' }}" id="sidebarWoocommerce">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('woocommerce-config.index') }}" class="{{ request()->routeIs('woocommerce-config.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('woocommerce-categorias.index') }}" class="{{ request()->routeIs('woocommerce-categorias.*') ? 'active' : '' }}">Categorias</a></li>

                        <li><a href="{{ route('woocommerce-produtos.index') }}" class="{{ request()->routeIs('woocommerce-produtos.*') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('woocommerce-pedidos.index') }}" class="{{ request()->routeIs('woocommerce-pedidos.*') ? 'active' : '' }}">Pedidos</a></li>
                    </ul>
                </div>
            </li>
            @endcan
            @endif
            @endif

            @if(env("NUVEMSHOP") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'Nuvem Shop'))
            @can('nuvem_shop_view')

            <li class="side-nav-item {{ request()->routeIs('nuvem-shop-config.*', 'nuvem-shop-categorias.*', 'nuvem-shop-produtos.*', 'nuvem-shop-pedidos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarNuvemShop" aria-expanded="{{ request()->routeIs('nuvem-shop-config.*', 'nuvem-shop-categorias.*', 'nuvem-shop-produtos.*', 'nuvem-shop-pedidos.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-cloud-line"></i>
                    <span> Nuvem Shop </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('nuvem-shop-config.*', 'nuvem-shop-categorias.*', 'nuvem-shop-produtos.*', 'nuvem-shop-pedidos.*') ? 'show' : '' }}" id="sidebarNuvemShop">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('nuvem-shop-config.index') }}" class="{{ request()->routeIs('nuvem-shop-config.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('nuvem-shop-categorias.index') }}" class="{{ request()->routeIs('nuvem-shop-categorias.*') ? 'active' : '' }}">Categorias</a></li>

                        <li><a href="{{ route('nuvem-shop-produtos.index') }}" class="{{ request()->routeIs('nuvem-shop-produtos.*') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('nuvem-shop-pedidos.index') }}" class="{{ request()->routeIs('nuvem-shop-pedidos.*') ? 'active' : '' }}">Pedidos</a></li>
                    </ul>
                </div>
            </li>
            @endcan
            @endif
            @endif

            @if(env("IFOOD") == 1)
            @if(__isActivePlan(Auth::user()->empresa, 'IFood'))
            @canany(['ifood_view'])

            <li class="side-nav-item {{ request()->routeIs('ifood-config.*', 'ifood-config-loja.*', 'ifood-catalogos.*', 'ifood-categoria-produtos.*', 'ifood-produtos.*', 'ifood-pedidos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarIfood" aria-expanded="{{ request()->routeIs('ifood-config.*', 'ifood-config-loja.*', 'ifood-catalogos.*', 'ifood-categoria-produtos.*', 'ifood-produtos.*', 'ifood-pedidos.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <img src="/icons/ifood.png" class="icon-menu">
                    <span> IFood </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('ifood-config.*', 'ifood-config-loja.*', 'ifood-catalogos.*', 'ifood-categoria-produtos.*', 'ifood-produtos.*', 'ifood-pedidos.*') ? 'show' : '' }}" id="sidebarIfood">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('ifood-config.index') }}" class="{{ request()->routeIs('ifood-config.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('ifood-config-loja.index') }}" class="{{ request()->routeIs('ifood-config-loja.*') ? 'active' : '' }}">Configuração da loja</a></li>

                        <li><a href="{{ route('ifood-catalogos.index') }}" class="{{ request()->routeIs('ifood-catalogos.*') ? 'active' : '' }}">Catálogos</a></li>

                        <li><a href="{{ route('ifood-categoria-produtos.index') }}" class="{{ request()->routeIs('ifood-categoria-produtos.*') ? 'active' : '' }}">Categorias de Produto</a></li>

                        <li><a href="{{ route('ifood-produtos.index') }}" class="{{ request()->routeIs('ifood-produtos.*') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('ifood-pedidos.index') }}" class="{{ request()->routeIs('ifood-pedidos.*') ? 'active' : '' }}">Pedidos</a></li>

                    </ul>

                </div>

            </li>

            @endcan
            @endif
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'VendiZap'))

            <li class="side-nav-item {{ request()->routeIs('vendizap-config.*', 'vendizap-categorias.*', 'variacoes.*', 'vendizap-produtos.*', 'vendizap-pedidos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarVendiZap" aria-expanded="{{ request()->routeIs('vendizap-config.*', 'vendizap-categorias.*', 'variacoes.*', 'vendizap-produtos.*', 'vendizap-pedidos.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-whatsapp-fill"></i>
                    <span> VendiZap </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('vendizap-config.*', 'vendizap-categorias.*', 'variacoes.*', 'vendizap-produtos.*', 'vendizap-pedidos.*') ? 'show' : '' }}" id="sidebarVendiZap">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('vendizap-config.index') }}" class="{{ request()->routeIs('vendizap-config.*') ? 'active' : '' }}">Configuração</a></li>

                        <li><a href="{{ route('vendizap-categorias.index') }}" class="{{ request()->routeIs('vendizap-categorias.*') ? 'active' : '' }}">Categorias</a></li>

                        @can('variacao_view')
                        <li><a href="{{ route('variacoes.index') }}" class="{{ request()->routeIs('variacoes.*') ? 'active' : '' }}">Variações</a></li>
                        @endcan

                        <li><a href="{{ route('vendizap-produtos.index') }}" class="{{ request()->routeIs('vendizap-produtos.*') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('vendizap-pedidos.index') }}" class="{{ request()->routeIs('vendizap-pedidos.*') ? 'active' : '' }}">Pedidos</a></li>
                    </ul>
                </div>
            </li>
            @endif

            <!-- fim delivery e ecommerce -->

            @if(__hasTransporte(Auth::user()->empresa))
            <li class="side-nav-title">TRANSPORTE</li>
            @endif
            @if(__isActivePlan(Auth::user()->empresa, 'CTe'))
            @canany(['cte_view'])

            <li class="side-nav-item {{ request()->routeIs('cte.*', 'cte-xml.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarCte" aria-expanded="{{ request()->routeIs('cte.*', 'cte-xml.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-truck-fill"></i>
                    <span>CTe</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('cte.*', 'cte-xml.*') ? 'show' : '' }}" id="sidebarCte">

                    <ul class="side-nav-second-level">

                        @can('cte_view')
                        <li><a href="{{ route('cte.index') }}" class="{{ request()->routeIs('cte.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('cte_create')
                        <li><a href="{{ route('cte.create') }}" class="{{ request()->routeIs('cte.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                        @can('arquivos_xml_view')
                        <li><a href="{{ route('cte-xml.index') }}" class="{{ request()->routeIs('cte-xml.*') ? 'active' : '' }}">Arquivos XML</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            @canany(['cte_os_view'])

            <li class="side-nav-item {{ request()->routeIs('cte-os.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarCteOs" aria-expanded="{{ request()->routeIs('cte-os.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-focus-3-line"></i>
                    <span>CTe Os</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('cte-os.*') ? 'show' : '' }}" id="sidebarCteOs">

                    <ul class="side-nav-second-level">
                        @can('cte_os_view')
                        <li><a href="{{ route('cte-os.index') }}" class="{{ request()->routeIs('cte-os.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('cte_os_create')
                        <li><a href="{{ route('cte-os.create') }}" class="{{ request()->routeIs('cte-os.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'MDFe'))
            @canany(['mdfe_view'])

            <li class="side-nav-item {{ request()->routeIs('mdfe.*', 'mdfe-xml.*', 'grupo-pagamento-padrao.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarMdfe" aria-expanded="{{ request()->routeIs('mdfe.*', 'mdfe-xml.*', 'grupo-pagamento-padrao.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-file-lock-line"></i>
                    <span>MDFe</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('mdfe.*', 'mdfe-xml.*', 'grupo-pagamento-padrao.*') ? 'show' : '' }}" id="sidebarMdfe">

                    <ul class="side-nav-second-level">

                        @can('mdfe_view')
                        <li><a href="{{ route('mdfe.index') }}" class="{{ request()->routeIs('mdfe.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('mdfe_create')
                        <li><a href="{{ route('mdfe.create') }}" class="{{ request()->routeIs('mdfe.create') ? 'active' : '' }}">Nova</a></li>
                        @endcan

                        @can('arquivos_xml_view')
                        <li><a href="{{ route('mdfe-xml.index') }}" class="{{ request()->routeIs('mdfe-xml.*') ? 'active' : '' }}">Arquivos XML</a></li>
                        @endcan

                        @can('mdfe_create')
                        <li><a href="{{ route('grupo-pagamento-padrao.index') }}" class="{{ request()->routeIs('grupo-pagamento-padrao.*') ? 'active' : '' }}">Grupos de pagamento</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Veiculos'))
            @canany(['veiculos_view', 'veiculos_create'])

            <li class="side-nav-item {{ request()->routeIs('veiculos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarVeiculos" aria-expanded="{{ request()->routeIs('veiculos.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-roadster-line"></i>
                    <span> Veículos </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('veiculos.*') ? 'show' : '' }}" id="sidebarVeiculos">

                    <ul class="side-nav-second-level">

                        @can('veiculos_view')
                        <li><a href="{{ route('veiculos.index') }}" class="{{ request()->routeIs('veiculos.index') ? 'active' : '' }}">Listar</a></li>
                        @endcan

                        @can('veiculos_create')
                        <li><a href="{{ route('veiculos.create') }}" class="{{ request()->routeIs('veiculos.create') ? 'active' : '' }}">Novo Veículo</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            @endif


            @if(__isActivePlan(Auth::user()->empresa, 'Controle de Fretes'))
            @canany(['tipo_despesa_frete_view', 'frete_view'])

            <li class="side-nav-item {{ request()->routeIs('tipo-despesa-frete.*', 'fretes.*', 'manutencao-veiculos.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarControleFrete" aria-expanded="{{ request()->routeIs('tipo-despesa-frete.*', 'fretes.*', 'manutencao-veiculos.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-road-map-line"></i>
                    <span>Controle de Fretes</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('tipo-despesa-frete.*', 'fretes.*', 'manutencao-veiculos.*') ? 'show' : '' }}" id="sidebarControleFrete">

                    <ul class="side-nav-second-level">

                        @can('tipo_despesa_frete_view')
                        <li><a href="{{ route('tipo-despesa-frete.index') }}" class="{{ request()->routeIs('tipo-despesa-frete.*') ? 'active' : '' }}">Tipos de despesa de frete</a></li>
                        @endcan

                        @can('frete_view')
                        <li><a href="{{ route('fretes.index') }}" class="{{ request()->routeIs('fretes.*') ? 'active' : '' }}">Fretes</a></li>
                        @endcan

                        @can('manutencao_veiculo_view')
                        <li><a href="{{ route('manutencao-veiculos.index') }}" class="{{ request()->routeIs('manutencao-veiculos.*') ? 'active' : '' }}">Manutenção de veículos</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan
            @endif

            <!-- fim transporte -->

            @if(__hasUtilitarios(Auth::user()->empresa))
            <li class="side-nav-title">UTILITÁRIOS</li>
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Localizações'))
            @canany(['localizacao_view'])

            <li class="side-nav-item {{ request()->routeIs('localizacao.*') ? 'menuitem-active mm-active' : '' }}" id="step5">

                <a data-bs-toggle="collapse" href="#sidebarLocalizacao" aria-expanded="{{ request()->routeIs('localizacao.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-building-4-line"></i>
                    <span>Localizações</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('localizacao.*') ? 'show' : '' }}" id="sidebarLocalizacao">

                    <ul class="side-nav-second-level">
                        <li><a href="{{ route('localizacao.index') }}" class="{{ request()->routeIs('localizacao.*') ? 'active' : '' }}">Listar</a></li>
                    </ul>

                </div>

            </li>

            @endcanany
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'CRM'))
            @canany(['crm_view'])

            <li class="side-nav-item {{ request()->routeIs('crm.*', 'mensagem-padrao-crm.*', 'mensagem-crm-logs.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarCrm" aria-expanded="{{ request()->routeIs('crm.*', 'mensagem-padrao-crm.*', 'mensagem-crm-logs.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-user-voice-fill"></i>
                    <span> CRM </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('crm.*', 'mensagem-padrao-crm.*', 'mensagem-crm-logs.*') ? 'show' : '' }}" id="sidebarCrm">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('crm.index') }}" class="{{ request()->routeIs('crm.*') ? 'active' : '' }}">Listar</a></li>

                        <li><a href="{{ route('mensagem-padrao-crm.index') }}" class="{{ request()->routeIs('mensagem-padrao-crm.*') ? 'active' : '' }}">Mensagem padrão</a></li>

                        <li><a href="{{ route('mensagem-crm-logs.index') }}" class="{{ request()->routeIs('mensagem-crm-logs.*') ? 'active' : '' }}">Logs de Mensagem</a></li>

                    </ul>

                </div>

            </li>

            @endcan
            @endif

            @if(__isActivePlan(Auth::user()->empresa, 'Sped'))
            @canany(['sped_config_view', 'sped_create'])

            <li class="side-nav-item {{ request()->routeIs('sped-config.*', 'sped.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarSped" aria-expanded="{{ request()->routeIs('sped-config.*', 'sped.*') ? 'true' : 'false' }}" aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-book-fill"></i>
                    <span> Sped </span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('sped-config.*', 'sped.*') ? 'show' : '' }}" id="sidebarSped">

                    <ul class="side-nav-second-level">

                        @can('sped_config_view')
                        <li><a href="{{ route('sped-config.index') }}" class="{{ request()->routeIs('sped-config.*') ? 'active' : '' }}">Configuração</a></li>
                        @endcan

                        @can('sped_create')
                        <li><a href="{{ route('sped.index') }}" class="{{ request()->routeIs('sped.*') ? 'active' : '' }}">Arquivo</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan
            @endif


            @if(!__isContador())
            @canany(['natureza_operacao_view', 'emitente_view'])

            <li class="side-nav-item {{ request()->routeIs('config.*', 'natureza-operacao.*', 'minhas-faturas.*', 'email-config.*', 'escritorio-contabil.*', 'config-geral.*', 'difal.*', 'cash-back-config.*', 'contigencia.*', 'sitef-config.*', 'scope-config.*', 'config-api.*', 'sintegra.*', 'relatorio-xml-contador.*', 'item-pesagem-pdv.*', 'metas.*', 'impressoras-pedido.*', 'mensagem-fiscal.*') ? 'menuitem-active mm-active' : '' }}" id="step5">

                <a data-bs-toggle="collapse" href="#sidebarConfig" aria-expanded="{{ request()->routeIs('config.*', 'natureza-operacao.*', 'minhas-faturas.*', 'email-config.*', 'escritorio-contabil.*', 'config-geral.*', 'difal.*', 'cash-back-config.*', 'contigencia.*', 'sitef-config.*', 'scope-config.*', 'config-api.*', 'sintegra.*', 'item-pesagem-pdv.*', 'metas.*', 'impressoras-pedido.*', 'relatorio-xml-contador.*', 'mensagem-fiscal.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-settings-4-fill"></i>
                    <span>Configurações</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('config.*', 'natureza-operacao.*', 'minhas-faturas.*', 'email-config.*', 'escritorio-contabil.*', 'config-geral.*', 'difal.*', 'cash-back-config.*', 'contigencia.*', 'sitef-config.*', 'scope-config.*', 'config-api.*', 'sintegra.*', 'item-pesagem-pdv.*', 'metas.*', 'impressoras-pedido.*', 'relatorio-xml-contador.*', 'mensagem-fiscal.*') ? 'show' : '' }}" id="sidebarConfig">

                    <ul class="side-nav-second-level">

                        @can('emitente_view')
                        <li><a href="{{ route('config.index') }}" class="{{ request()->routeIs('config.*') ? 'active' : '' }}">Emitente</a></li>
                        @endcan

                        @can('natureza_operacao_view')
                        <li><a href="{{ route('natureza-operacao.index') }}" class="{{ request()->routeIs('natureza-operacao.*') ? 'active' : '' }}">Natureza de operação</a></li>
                        @endcan

                        @if(Auth::user()->empresa && Auth::user()->empresa->empresa->receber_com_boleto)
                        <li><a href="{{ route('minhas-faturas.index') }}" class="{{ request()->routeIs('minhas-faturas.*') ? 'active' : '' }}">Minhas faturas</a></li>
                        @endif

                        @can('email_config_view')
                        <li><a href="{{ route('email-config.index') }}" class="{{ request()->routeIs('email-config.*') ? 'active' : '' }}">Configuração de email</a></li>
                        @endcan

                        @can('configuracao_crediario_view')
                        <li>
                            <a href="{{ route('configuracao-crediario.index') }}" class="{{ request()->routeIs('configuracao-crediario.*') ? 'active' : '' }}">
                                Configuração de crediário
                            </a>
                        </li>
                        @endcan

                        @can('escritorio_contabil_view')
                        <li><a href="{{ route('escritorio-contabil.index') }}" class="{{ request()->routeIs('escritorio-contabil.*') ? 'active' : '' }}">Escritório contábil</a></li>
                        @endcan

                        @can('emitente_view')
                        <li><a href="{{ route('config-geral.create') }}" class="{{ request()->routeIs('config-geral.*') ? 'active' : '' }}">Geral</a></li>
                        @endcan

                        @can('difal_view')
                        <li><a href="{{ route('difal.index') }}" class="{{ request()->routeIs('difal.*') ? 'active' : '' }}">Op. Interestadual - Difal</a></li>
                        @endcan

                        @can('cashback_config_view')
                        <li><a href="{{ route('cash-back-config.index') }}" class="{{ request()->routeIs('cash-back-config.*') ? 'active' : '' }}">CashBack</a></li>
                        @endcan

                        @can('contigencia_view')
                        <li><a href="{{ route('contigencia.index') }}" class="{{ request()->routeIs('contigencia.*') ? 'active' : '' }}">Contingência</a></li>
                        @endcan

                        @can('config_tef_view')
                        <li><a href="{{ route('sitef-config.index') }}" class="{{ request()->routeIs('sitef-config.*') ? 'active' : '' }}">Configuração TEF SITEF</a></li>

                        <li><a href="{{ route('scope-config.index') }}" class="{{ request()->routeIs('scope-config.*') ? 'active' : '' }}">Configuração TEF SCOPE</a></li>
                        @endcan

                        @can('config_api')
                        <li><a href="{{ route('config-api.index') }}" class="{{ request()->routeIs('config-api.*') ? 'active' : '' }}">API</a></li>
                        @endcan

                        <li>
                            <a href="{{ route('app-qrcode') }}" class="{{ request()->routeIs('app-qrcode') ? 'active' : '' }}">
                                Conectar App
                            </a>
                        </li>

                        <li><a href="{{ route('sintegra.index') }}" class="{{ request()->routeIs('sintegra.*') ? 'active' : '' }}">Sintegra</a></li>
                        @if(app('router')->has('mensagem-fiscal.index'))
                        <li><a href="{{ route('mensagem-fiscal.index') }}" class="{{ request()->routeIs('mensagem-fiscal.*') ? 'active' : '' }}">Mensagem fiscal</a></li>
                        @endif

                        <li>
                            <a href="{{ route('relatorio-xml-contador.index') }}" class="{{ request()->routeIs('relatorio-xml-contador.*') ? 'active' : '' }}">
                                XML para Contador
                            </a>
                        </li>

                        @can('item_pesagem_pdv_view')
                        <li><a href="{{ route('item-pesagem-pdv.index') }}" class="{{ request()->routeIs('item-pesagem-pdv.*') ? 'active' : '' }}">Pesagem PDV</a></li>
                        @endcan

                        @can('metas_view')
                        <li><a href="{{ route('metas.index') }}" class="{{ request()->routeIs('metas.*') ? 'active' : '' }}">Configuração de metas</a></li>
                        @endcan

                        @can('impressora_pedido_view')
                        <li><a href="{{ route('impressoras-pedido.index') }}" class="{{ request()->routeIs('impressoras-pedido.*') ? 'active' : '' }}">Impressoras de pedido</a></li>
                        @endcan

                    </ul>

                </div>

            </li>

            @endcanany

            @endif
            @endif

            @if(Auth::user()->empresa && __isContador())

            <li class="side-nav-item {{ request()->routeIs('contador-empresa.produtos', 'contador-empresa.clientes', 'contador-empresa.fornecedores', 'contador-natureza-operacao.*', 'contador-produto-tributacao.*', 'contador.show') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarCad" aria-expanded="{{ request()->routeIs('contador-empresa.produtos', 'contador-empresa.clientes', 'contador-empresa.fornecedores', 'contador-natureza-operacao.*', 'contador-produto-tributacao.*', 'contador.show') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-draft-fill"></i>
                    <span>Cadastros da empresa</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('contador-empresa.produtos', 'contador-empresa.clientes', 'contador-empresa.fornecedores', 'contador-natureza-operacao.*', 'contador-produto-tributacao.*', 'contador.show') ? 'show' : '' }}" id="sidebarCad">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('contador-empresa.produtos') }}" class="{{ request()->routeIs('contador-empresa.produtos') ? 'active' : '' }}">Produtos</a></li>

                        <li><a href="{{ route('contador-empresa.clientes') }}" class="{{ request()->routeIs('contador-empresa.clientes') ? 'active' : '' }}">Clientes</a></li>

                        <li><a href="{{ route('contador-empresa.fornecedores') }}" class="{{ request()->routeIs('contador-empresa.fornecedores') ? 'active' : '' }}">Fornecedores</a></li>

                        <li><a href="{{ route('contador-natureza-operacao.index') }}" class="{{ request()->routeIs('contador-natureza-operacao.*') ? 'active' : '' }}">Natureza de Operação</a></li>

                        <li><a href="{{ route('contador-produto-tributacao.index') }}" class="{{ request()->routeIs('contador-produto-tributacao.*') ? 'active' : '' }}">Configuração Padrão Fiscal</a></li>

                        <li><a href="{{ route('contador.show') }}" class="{{ request()->routeIs('contador.show') ? 'active' : '' }}">Configuração</a></li>

                    </ul>

                </div>

            </li>

            <li class="side-nav-item {{ request()->routeIs('contador-empresa.nfe', 'contador-empresa.nfce', 'contador-empresa.cte', 'contador-empresa.mdfe') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarDoc" aria-expanded="{{ request()->routeIs('contador-empresa.nfe', 'contador-empresa.nfce', 'contador-empresa.cte', 'contador-empresa.mdfe') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-clipboard-fill"></i>
                    <span>Documentos</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('contador-empresa.nfe', 'contador-empresa.nfce', 'contador-empresa.cte', 'contador-empresa.mdfe') ? 'show' : '' }}" id="sidebarDoc">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('contador-empresa.nfe') }}" class="{{ request()->routeIs('contador-empresa.nfe') ? 'active' : '' }}">NFe</a></li>

                        <li><a href="{{ route('contador-empresa.nfce') }}" class="{{ request()->routeIs('contador-empresa.nfce') ? 'active' : '' }}">NFCe</a></li>

                        <li><a href="{{ route('contador-empresa.cte') }}" class="{{ request()->routeIs('contador-empresa.cte') ? 'active' : '' }}">CTe</a></li>

                        <li><a href="{{ route('contador-empresa.mdfe') }}" class="{{ request()->routeIs('contador-empresa.mdfe') ? 'active' : '' }}">MDFe</a></li>

                    </ul>

                </div>

            </li>

            <li class="side-nav-item {{ request()->routeIs('contador-empresas.*', 'contador-planos.*', 'contador-gerencia.*') ? 'menuitem-active mm-active' : '' }}">

                <a data-bs-toggle="collapse" href="#sidebarGerenciamento" aria-expanded="{{ request()->routeIs('contador-empresas.*', 'contador-planos.*', 'contador-gerencia.*') ? 'true' : 'false' }}" aria-controls="sidebarIcons" class="side-nav-link">
                    <i class="ri-briefcase-line"></i>
                    <span>Gerenciamento</span>
                    <span class="menu-arrow"></span>
                </a>

                <div class="collapse {{ request()->routeIs('contador-empresas.*', 'contador-planos.*', 'contador-gerencia.*') ? 'show' : '' }}" id="sidebarGerenciamento">

                    <ul class="side-nav-second-level">

                        <li><a href="{{ route('contador-empresas.index') }}" class="{{ request()->routeIs('contador-empresas.*') ? 'active' : '' }}">Empresas</a></li>

                        @if(__isContadorPlano())

                        <li><a href="{{ route('contador-planos.index') }}" class="{{ request()->routeIs('contador-planos.*') ? 'active' : '' }}">Planos</a></li>

                        <li><a href="{{ route('contador-gerencia.index') }}" class="{{ request()->routeIs('contador-gerencia.*') ? 'active' : '' }}">Gerenciar planos</a></li>

                        @endif

                    </ul>

                </div>

            </li>

            @endif
        </ul>
    </div>
</div>
