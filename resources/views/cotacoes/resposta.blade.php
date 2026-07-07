<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resposta de Cotação</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }

        .navbar-brand img {
            max-height: 60px;
            width: auto;
        }

        .page-header {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-weight: 800;
            margin-bottom: 0;
        }

        .info-label {
            font-size: .85rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: .2rem;
            font-weight: 700;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: #212529;
        }

        .card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.25rem;
        }

        .card-title {
            font-weight: 800;
            margin-bottom: 0;
        }

        .table thead th {
            white-space: nowrap;
            font-size: .92rem;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .required-note {
            font-size: .9rem;
            font-weight: 600;
        }

        .summary-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
        }

        .summary-box h5,
        .summary-box h4 {
            margin-bottom: .5rem;
        }

        .form-section-title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .w-produto {
            min-width: 320px;
        }

        .w-qtd,
        .w-valor,
        .w-subtotal,
        .w-vencimento,
        .w-parcela {
            min-width: 140px;
        }

        .w-observacao {
            min-width: 260px;
        }

        .w-tipo {
            min-width: 200px;
        }

        .btn-submit {
            min-width: 220px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 18px;
            }

            .page-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-md bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="javascript:void(0)">
                @if($empresa->logo != '')
                    <img src="{{ $empresa->img }}" alt="Logo {{ $empresa->nome }}">
                @else
                    <span class="fw-bold">{{ $empresa->nome }}</span>
                @endif
            </a>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">

            <form method="POST" action="{{ route('cotacoes.resposta-store') }}">
                @csrf
                <input type="hidden" name="cotacao_id" value="{{ $cotacao->id }}">

                <div class="page-header">
                    <div class="text-center mb-4">
                        <h2 class="page-title">
                            COTAÇÃO <span class="text-primary">#{{ $cotacao->referencia }}</span>
                        </h2>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-8 col-md-6 col-12">
                            <div class="info-label">Solicitante</div>
                            <div class="info-value">{{ $empresa->nome }}</div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="info-label">Telefone</div>
                            <div class="info-value">{{ $empresa->celular }}</div>
                        </div>

                        <div class="col-lg-8 col-md-6 col-12">
                            <div class="info-label">Fornecedor</div>
                            <div class="info-value">{{ strtoupper($cotacao->fornecedor->razao_social) }}</div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="info-label">Cidade</div>
                            <div class="info-value">{{ $cotacao->fornecedor->cidade->info }}</div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="info-label">CNPJ</div>
                            <div class="info-value">{{ $cotacao->fornecedor->cpf_cnpj }}</div>
                        </div>

                        @if($cotacao->observacao)
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    <strong>Observação da cotação:</strong> {{ $cotacao->observacao }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Itens da Cotação</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-danger required-note mb-3">* Campos obrigatórios</p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="w-produto">Produto</th>
                                        <th class="w-qtd">Quantidade</th>
                                        <th class="w-valor">Valor Unitário <span class="text-danger">*</span></th>
                                        <th class="w-subtotal">Subtotal <span class="text-danger">*</span></th>
                                        <th class="w-observacao">Observação do item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cotacao->itens as $linha => $p)
                                        @php
                                            $casasDecimais = 2;
                                        @endphp

                                        <tr>
                                            <input type="hidden" name="item_id[]" value="{{ $p->id }}">

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="produto_nome[]"
                                                    value="{{ $p->descricao_produto }}"
                                                    readonly
                                                    disabled
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control quantidade moeda"
                                                    name="quantidade[]"
                                                    value="{{ number_format($p->quantidade, $casasDecimais, ',', '.') }}"
                                                    readonly
                                                    required
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control moeda value"
                                                    name="valor_unitario[]"
                                                    placeholder="0,00"
                                                    required
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control subtotal"
                                                    name="subtotal[]"
                                                    placeholder="0,00"
                                                    readonly
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="observacao_item[]"
                                                    placeholder="Observação do item"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="summary-box mt-3 mb-4">
                            <h5 class="mb-0">
                                Soma dos produtos:
                                <strong class="text-dark total">R$ 0,00</strong>
                            </h5>
                        </div>

                        <div class="form-section-title">Informações complementares</div>

                        <div class="row g-3">
                            <div class="col-lg-2 col-md-4 col-6">
                                <label for="desconto" class="form-label">Desconto</label>
                                <input type="text" id="desconto" name="desconto" class="form-control moeda" placeholder="0,00">
                            </div>

                            <div class="col-lg-2 col-md-4 col-6">
                                <label for="valor_frete" class="form-label">Valor do frete</label>
                                <input type="text" id="valor_frete" name="valor_frete" class="form-control moeda" placeholder="0,00">
                            </div>

                            <div class="col-lg-4 col-md-12 col-12">
                                <label for="observacao_frete" class="form-label">Observação do frete</label>
                                <input type="text" id="observacao_frete" name="observacao_frete" class="form-control">
                            </div>

                            <div class="col-lg-2 col-md-6 col-12">
                                <label for="previsao_entrega" class="form-label">
                                    Previsão de entrega <span class="text-danger">*</span>
                                </label>
                                <input required type="date" id="previsao_entrega" name="previsao_entrega" class="form-control">
                            </div>

                            <div class="col-lg-2 col-md-6 col-12">
                                <label for="responsavel" class="form-label">
                                    Responsável <span class="text-danger">*</span>
                                </label>
                                <input required type="text" id="responsavel" name="responsavel" class="form-control">
                            </div>

                            <div class="col-12">
                                <label for="observacao" class="form-label">Observação geral</label>
                                <input type="text" id="observacao" name="observacao" class="form-control">
                            </div>
                        </div>

                        <div class="summary-box mt-4">
                            <h4 class="mb-0 text-primary">
                                Valor total da cotação:
                                <strong class="total-cotacao">R$ 0,00</strong>
                            </h4>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h5 class="mb-0 fw-bold">Fatura da cotação (opcional)</h5>

                            <button type="button" class="btn btn-dark btn-add-tr">
                                Adicionar parcela
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-dynamic align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="w-vencimento">Data de vencimento</th>
                                        <th class="w-parcela">Valor da parcela</th>
                                        <th class="w-tipo">Tipo de pagamento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="dynamic-form">
                                        <td>
                                            <input type="date" name="data_vencimento[]" class="form-control">
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="valor_parcela[]"
                                                class="form-control moeda valor_parcela"
                                                placeholder="0,00"
                                            >
                                        </td>
                                        <td>
                                            <select class="form-select" name="tipo_pagamento[]">
                                                @foreach(App\Models\FaturaCotacao::tiposPagamento() as $key => $tp)
                                                    <option value="{{ $key }}">{{ $tp }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="summary-box mt-3 mb-4">
                            <h5 class="mb-0 text-primary">
                                Soma fatura:
                                <strong class="total-fatura">R$ 0,00</strong>
                            </h5>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success btn-lg btn-submit">
                            	Finalizar
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <script>
        $('.moeda').mask('000.000.000.000.000,00', { reverse: true });
    </script>

    <script src="/js/cotacao_response.js"></script>
</body>
</html>