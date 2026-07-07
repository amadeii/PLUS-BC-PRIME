
(function () {

    const $loading = $('#produtos-loading');
    const $tabela = $('#tabela-produtos');
    const $tbody  = $('#lista-produtos');
    const $info   = $('#produtos-info');
    const $msg    = $('#produtos-msg');

    function getFiltros() {
        return {
            nome: $('#filtro_nome').val(),
            categoria_id: $('#filtro_categoria').val(),
            marca_id: $('#filtro_marca').val(),
            codigo_barras: $('#filtro_codigo_barras').val(),
            referencia: $('#filtro_referencia').val(),
            empresa_id: $('#empresa_id').val()
        };
    }

    function setLoading(show) {
        if (show) {
            $loading.removeClass('d-none');
        } else {
            $loading.addClass('d-none');
        }
    }

    function resetTabela() {
        $tbody.html('');
        // $tabela.addClass('d-none');
        $info.text('0 itens');
        $msg.html('<span class="text-muted">Use os filtros para buscar produtos.</span>');
    }

    function buscarProdutosPdv() {
        setLoading(true);
        $msg.html('<span class="text-muted">Buscando produtos...</span>');

        $.ajax({
            url: path_url + 'api/produtos/busca-pdv',
            type: 'GET',
            data: getFiltros(),
            dataType: 'json',

            success: function (res) {
                if (res.html && res.html.trim() !== '') {
                    $tbody.html(res.html);
                    // $tabela.removeClass('d-none');
                    $info.text((res.total ?? 0) + ' itens');
                    $msg.html('');
                } else {
                    resetTabela();
                }
            },

            error: function (xhr) {
                console.error('Erro busca PDV:', xhr);
                $tbody.html(`
                    <tr>
                    <td colspan="7" class="text-center text-danger py-3">
                    <i class="ri-error-warning-line"></i>
                    Erro ao buscar produtos
                    </td>
                    </tr>
                    `);
                // $tabela.removeClass('d-none');
                $info.text('0 itens');
                $msg.html('');
            },

            complete: function () {
                setLoading(false);
            }
        });
    }

    /* =========================
       Eventos
       ========================= */

    // Botão filtrar
    $('#btn-filtrar-produto').on('click', function () {
        buscarProdutosPdv();
    });

    // Enter no campo nome
    $('#modalBuscarProdutos input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            buscarProdutosPdv();
        }
    });

    $('#modalBuscarProdutos select').on('change', function (e) {
        buscarProdutosPdv();
    });

    // Limpar filtros
    $('#btn-limpar-filtros-produto').on('click', function () {
        $('#filtro_nome').val('');
        $('#filtro_categoria').val('');
        $('#filtro_marca').val('');
        $('#filtro_codigo_barras').val('');
        $('#filtro_referencia').val('');
        resetTabela();
    });

    // Clique em adicionar produto
    $(document).on('click', '.btn-add-produto', function () {
        const produtoId = $(this).data('id');
        addProdutos(produtoId)
        console.log('Produto selecionado:', produtoId);

        $('#modalBuscarProdutos').modal('hide');
    });


})();
