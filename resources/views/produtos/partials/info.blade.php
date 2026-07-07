<div class="row g-4 align-items-start">

    <div class="col-lg-8">

        <div class="d-flex align-items-start gap-3 mb-4">

            <div class="produto-icon">
                <i class="ri-shopping-bag-3-line"></i>
            </div>

            <div>
                <h3 class="fw-bold mb-1">{{ $item->nome }}</h3>

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge badge-soft-custom">
                        {{ $item->categoria ? $item->categoria->nome : 'Sem categoria' }}
                    </span>

                    <span class="badge badge-soft-light">
                        Unidade: {{ $item->unidade }}
                    </span>
                </div>
            </div>

        </div>

        <div class="row g-3">

            <div class="col-md-6">
                <div class="info-card">
                    <small>Valor de venda</small>
                    <h4 class="text-success fw-bold mb-0">
                        R$ {{ __moeda($item->valor_unitario) }}
                    </h4>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-card">
                    <small>Valor de compra</small>
                    <h4 class="text-primary fw-bold mb-0">
                        R$ {{ __moeda($item->valor_compra) }}
                    </h4>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-card">
                    <small>Data de cadastro</small>
                    <h6 class="fw-semibold mb-0">
                        {{ __data_pt($item->created_at) }}
                    </h6>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-card">
                    <small>Código de barras</small>
                    <h6 class="fw-semibold mb-0 text-break">
                        {{ $item->codigo_barras ?: '--' }}
                    </h6>
                </div>
            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="produto-image-card">

            <img src="{{ $item->img }}"
                class="produto-image"
                onerror="this.src='https://via.placeholder.com/300x300?text=Produto'">

        </div>

    </div>

</div>