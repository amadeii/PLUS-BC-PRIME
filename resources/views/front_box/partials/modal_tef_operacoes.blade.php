<div class="modal fade" id="modal-tef-operacoes" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">
                    <i class="la la-credit-card mr-2 text-white"></i>Operações TEF - Vendas do Dia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Filtro de Data --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                            </div>
                            <input type="date" id="tef-filtro-data" class="form-control" value="{{ date('Y-m-d') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" onclick="carregarVendasTEF()">
                                    <i class="la la-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 text-right">
                        <button type="button" class="btn btn-outline-warning" onclick="iniciarConsultaPendencias()">
                            <i class="la la-clock mr-1"></i> Consultar Pendências
                        </button>
                    </div>
                </div>

                {{-- Tabela de Vendas --}}
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-striped" id="tabela-vendas-tef">
                        <thead class="thead-dark" style="position: sticky; top: 0;">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Nº Venda</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>NSU</th>
                                <th>Bandeira</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-vendas-tef">
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="la la-spinner la-spin la-2x"></i>
                                    <br>Carregando vendas TEF...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted mr-auto" id="tef-total-vendas"></span>
                <button class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-tef-confirma-cancelamento" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i class="la la-exclamation-triangle mr-2 text-white"></i>Cancelar Pagamento TEF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Atenção:</strong> Esta operação irá estornar o pagamento TEF.
                </div>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Venda Nº</small>
                                <h5 id="cancel-venda-numero">-</h5>
                            </div>
                            <div class="col-6 text-right">
                                <small class="text-muted">Valor</small>
                                <h5 class="text-success" id="cancel-venda-valor">R$ 0,00</h5>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-4">
                                <small class="text-muted">NSU</small>
                                <div id="cancel-venda-nsu">-</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Bandeira</small>
                                <div id="cancel-venda-bandeira">-</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Tipo</small>
                                <div id="cancel-venda-tipo">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-3">O que deseja fazer?</h6>

                <button class="btn btn-warning btn-lg w-100 btn-block mb-2" onclick="executarCancelamentoTEFAutomatico(false)">
                    <i class="la la-credit-card mr-2"></i>
                    Cancelar SOMENTE o pagamento TEF
                    <br><small class="font-weight-normal">(A venda permanece ativa)</small>
                </button>

                <button class="btn btn-danger btn-lg w-100 btn-block" onclick="executarCancelamentoTEFAutomatico(true)">
                    <i class="la la-times-circle mr-2"></i>
                    Cancelar TEF + VENDA completa
                    <br><small class="font-weight-normal">(Estorna pagamento e cancela venda)</small>
                </button>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Voltar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Detalhes da Transação --}}
<div class="modal fade" id="modal-tef-detalhes" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="la la-info-circle mr-2 text-white"></i>Detalhes da Transação TEF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body" id="modal-tef-detalhes-body">
                <!-- Preenchido via JS -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
            </div>
        </div>
    </div>
</div>
