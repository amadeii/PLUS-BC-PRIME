<div class="modal fade" id="fechamento_caixa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog ">
        <div class="modal-content fechamento-modal">

            <div class="modal-header fechamento-header">
                <h5 class="modal-title">Fechar Caixa</h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <h4 class="fw-bold">
                    Valor em Caixa: 
                    <span class="text-success">
                        R$ {{ __moeda($totalVendas + $item->valor_abertura - $somaSangrias + $somaSuprimentos) }}
                    </span>
                </h4>

                <small class="text-muted d-block mb-3">
                    (Calculado como: Suprimentos + Vendas recebidas - Sangrias)
                </small>

                {!! Form::open()->post()->route('caixa.fechar')->multipart() !!}
                <input type="hidden" name="valor_fechamento" value="{{ $totalVendas + $item->valor_abertura }}">
                <input type="hidden" name="caixa_id" value="{{ $item->id }}">
<!-- 
                <div class="mb-3">
                    {!! Form::tel('valor_dinheiro__', 'Total em Dinheiro')->attrs(['class' => 'moeda']) !!}
                </div>

                <div class="mb-3">
                    {!! Form::tel('valor_cheque__', 'Valor em Cheque')->attrs(['class' => 'moeda']) !!}
                </div>

                <div class="mb-3">
                    {!! Form::tel('valor_outros__', 'Valor em Outros')->attrs(['class' => 'moeda']) !!}
                </div> -->

                @php
                $tiposPagamento = \App\Models\Nfce::tiposPagamento();
                @endphp

                <div class="row">
                    @foreach($somaTiposPagamento as $codigo => $valor)
                    @php
                    $codigoFormatado = str_pad($codigo, 2, '0', STR_PAD_LEFT);
                    $nomePagamento = $tiposPagamento[$codigoFormatado] ?? ('Tipo ' . $codigoFormatado);

                    $valorEsperado = (float)$valor;
                    $valorFormatado = number_format($valorEsperado, 2, ',', '.');
                    @endphp

                    @if($valorEsperado > 0)
                    <div class="col-md-6 mb-3">

                        <!-- LABEL -->
                        <label class="form-label fw-semibold">
                            {{ $nomePagamento }}
                        </label>

                        <!-- VALOR ESPERADO -->
                        <div class="text-muted small mb-1">
                            Esperado: <strong class="text-success">R$ {{ $valorFormatado }}</strong>
                        </div>

                        <!-- INPUT -->
                        <input 
                        type="text"
                        name="pagamentos[{{ $codigoFormatado }}][valor]"
                        class="form-control moeda"
                        value="{{ $valorFormatado }}"
                        >

                        <!-- HIDDEN -->
                        <input type="hidden" name="pagamentos[{{ $codigoFormatado }}][codigo]" value="{{ $codigoFormatado }}">
                        <input type="hidden" name="pagamentos[{{ $codigoFormatado }}][nome]" value="{{ $nomePagamento }}">
                        <input type="hidden" name="pagamentos[{{ $codigoFormatado }}][esperado]" value="{{ $valorEsperado }}">

                    </div>
                    @endif
                    @endforeach
                </div>

                <div class="mb-3">
                    {!! Form::text('observacao', 'Observação') !!}
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fechar-btn">
                    Salvar Fechamento
                </button>

                {!! Form::close() !!}
            </div>

        </div>
    </div>
</div>
