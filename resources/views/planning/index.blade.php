@php
    use App\Auth\Policies\PermissionMatrix;
    use App\Auth\Profiles;

    $userData = $user ?? [];
    $profileName = $userData['perfil'] ?? ($userData['profile'] ?? 'Professor');
    $profileEnum = Profiles::fromString((string) $profileName) ?? Profiles::Teacher;
    $canApproveReservations = PermissionMatrix::allows($profileEnum, PermissionMatrix::RESERVATIONS_APPROVE);
    $canCreateReservations = PermissionMatrix::allows($profileEnum, PermissionMatrix::RESERVATIONS_CREATE);
@endphp

<x-layouts.app :user="$user">
    <div class="container py-4"
         id="planningApp"
         data-planning-app
         data-user-profile="{{ $profileEnum->value }}"
         data-user-id="{{ $userData['id'] ?? '' }}"
         data-can-approve="{{ $canApproveReservations ? 'true' : 'false' }}"
         data-can-reserve="{{ $canCreateReservations ? 'true' : 'false' }}">
        <div class="page-header d-flex justify-content-between align-items-end">
            <div class="page-title">
                <h1>Planejamento de aulas</h1>
                <p>Visualize, crie e acompanhe planejamentos mensais com integração à BNCC.</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary" id="btnNovoPlanejamento">
                    <i class="fas fa-plus"></i> Novo planejamento
                </button>
            </div>
        </div>

        <div id="crudPlanejamentoMensalContainer" class="segundo-container oculto destaque" aria-live="polite">
            <div class="page-title">
                <h2 id="tituloFormPlanejamentoMensal">Carregando...</h2>
                <p id="subtituloFormPlanejamentoMensal">Carregando...</p>
            </div>

            <div class="form-group">
                <label for="tempo">Tipo de Ciclo<span class="required">*</span></label>
                <p>Escolha como o planejamento será distribuído ao longo do tempo.</p>
                <select id="tempo" name="tempo" required class="form-select">
                    <option value="" disabled selected>Selecione o tipo de ciclo</option>
                </select>
            </div>

            <div id="formPlanejamentoWrapper" class="content-container sub-container oculto destaque">
                <form id="crudPlanejamentoMensalForm" autocomplete="off">
                    <input type="hidden" id="id-planejamento-mensal" name="id-planejamento-mensal">
                    <input type="hidden" id="linhas-planejamento" name="linhas_serializadas" value="[]">

                    <h3>Informações iniciais</h3>
                    <p>Esses dados aparecem no cabeçalho do planejamento.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label for="nome-plano-mensal">Nome<span class="required">*</span></label>
                            <p>Defina um título descritivo para o planejamento.</p>
                            <input type="text" id="nome-plano-mensal" name="nome-plano-mensal" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="periodo_realizacao">Período de realização</label>
                            <p>Informe o período planejado (ex.: 01/03/2024 a 31/03/2024).</p>
                            <input type="text" id="periodo_realizacao" name="periodo_realizacao" class="form-control" placeholder="Ex: 01/03/2024 a 31/03/2024">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label for="materia">Matéria<span class="required">*</span></label>
                            <p>Selecione a disciplina vinculada ao planejamento.</p>
                            <select id="materia" name="materia" class="form-select" required>
                                <option value="" disabled selected>Selecione a matéria</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="numero_aulas_semanais">Número de aulas semanais<span class="required">*</span></label>
                            <p>Informe a carga semanal prevista para o plano.</p>
                            <input type="number" id="numero_aulas_semanais" name="numero_aulas_semanais" class="form-control" min="1" max="40" step="1" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label for="anos-plano">Anos do plano</label>
                            <p>Escolha os anos escolares contemplados.</p>
                            <select id="anos-plano" name="anos_plano[]" class="form-select" multiple>
                                <option value="1º">1º</option>
                                <option value="2º">2º</option>
                                <option value="3º">3º</option>
                                <option value="4º">4º</option>
                                <option value="5º">5º</option>
                                <option value="6º">6º</option>
                                <option value="7º">7º</option>
                                <option value="8º">8º</option>
                                <option value="9º">9º</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="objetivo_geral">Objetivo geral</label>
                            <textarea id="objetivo_geral" name="objetivo_geral" class="form-control" rows="4" placeholder="Descreva o objetivo geral do planejamento..."></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label for="objetivo_especifico">Objetivos específicos</label>
                            <textarea id="objetivo_especifico" name="objetivo_especifico" class="form-control" rows="4" placeholder="Detalhe os objetivos específicos..."></textarea>
                        </div>
                        <div class="col-md-6 form-group align-self-end text-end">
                            <button type="button" id="btnCancelarTudo" class="btn btn-cancelar">
                                <i class="fas fa-times"></i> Cancelar edição
                            </button>
                        </div>
                    </div>

                    <div id="blocos-planejamento" class="mb-4"></div>

                    <section id="agendamentoSalas" class="content-container sub-container destaque mt-4">
                        <h3>Agendamento de salas</h3>
                        <p>Reserve salas compartilhadas e acompanhe aprovações em tempo real.</p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 form-group">
                                <label for="reserva-sala">Sala</label>
                                <select id="reserva-sala" class="form-select">
                                    <option value="" selected>Selecione uma sala</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="reserva-inicio">Início</label>
                                <input type="datetime-local" id="reserva-inicio" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="reserva-fim">Fim</label>
                                <input type="datetime-local" id="reserva-fim" class="form-control">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 form-group">
                                <label for="reserva-observacoes">Observações</label>
                                <textarea id="reserva-observacoes" class="form-control" rows="2" placeholder="Detalhes adicionais (opcional)"></textarea>
                            </div>
                        </div>

                        <div class="form-actions mb-3">
                            <button type="button" class="btn btn-secondary" id="btnVerDisponibilidade">
                                <i class="fas fa-calendar-check"></i> Ver reservas do período
                            </button>
                            <button type="button" class="btn btn-primary" id="btnReservarSala">
                                <i class="fas fa-door-open"></i> Solicitar reserva
                            </button>
                        </div>

                        <div class="table-responsive" id="reservasTabelaWrapper">
                            <table class="table table-striped" id="tabelaReservasPlanejamento">
                                <thead>
                                    <tr>
                                        <th>Sala</th>
                                        <th>Início</th>
                                        <th>Fim</th>
                                        <th>Status</th>
                                        <th>Solicitante</th>
                                        <th>Aprovação</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyReservasPlanejamento">
                                    <tr class="estado-vazio">
                                        <td colspan="7" class="text-center text-muted">Nenhuma reserva registrada para este planejamento.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar planejamento
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="filtros-container mt-4">
            <h3>Filtrar</h3>
            <p>Procure planejamentos pelo título ou período.</p>
            <form id="filtroPlanejamentosMensais" class="filtros-form">
                <div class="filtros-row">
                    <div class="filtro-group">
                        <label for="pesquisaPlanejamentosMensais">Buscar texto</label>
                        <input type="text" id="pesquisaPlanejamentosMensais" name="pesquisa" placeholder="Buscar por texto">
                    </div>
                </div>
                <div class="filtros-actions">
                    <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
                    <button type="button" id="btnLimparFiltroPlanejamento" class="btn-limpar"><i class="fas fa-xmark"></i> Limpar</button>
                </div>
            </form>
            <div class="contador-registros" id="contadorRegistrosPlanejamento">
                Exibindo <strong>0</strong> registro(s)
            </div>
        </div>

        <div class="table-container mt-3" id="lista-planejamentos-mensais">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Matéria</th>
                        <th>Anos</th>
                        <th>Período</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbody-lista-planejamentos">
                    <tr class="estado-vazio">
                        <td colspan="6" class="text-center text-muted">Nenhum planejamento cadastrado.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalGeral" tabindex="-1" aria-labelledby="modalGeralLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGeralLabel">Confirmação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="modalGeralBody">Deseja confirmar esta ação?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="modalGeralConfirmar">Confirmar</button>
                    <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <template id="planning-line-template">
        <div class="bloco-linha content-container sub-container" data-grupo="__GID__">
            <div class="page-header" id="bloco-header-__GID__">
                <div class="page-title">
                    <h3 id="tituloAddLinhaMensal-__GID__">Adicionar temas</h3>
                    <p id="subtituloAddLinhaMensal-__GID__">Para preencher as informações, clique em Criar nova linha</p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" id="btnAdicionarLinha-__GID__">
                        <i class="fas fa-plus"></i> Criar nova linha
                    </button>
                </div>
            </div>

            <div class="linhas-adicionadas sub-container mb-4" id="bloco-tabela-__GID__">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Etapa/Ano</th>
                            <th>Área</th>
                            <th>Componente</th>
                            <th>Unidade Temática</th>
                            <th>Objeto Conhecimento</th>
                            <th>Habilidades</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-linhas-planejamento-__GID__"></tbody>
                </table>
            </div>

            <div id="form-linha-bncc-__GID__" class="adicionar-linhas oculto">
                <h4>Crie uma linha com base na BNCC</h4>
                <p>Busque as informações da base para acrescentar uma linha em seu plano mensal.</p>

                <div class="form-group" id="grupo-etapa-__GID__">
                    <label for="etapa-linha-__GID__">Etapa <span class="required">*</span></label>
                    <select id="etapa-linha-__GID__" name="etapa-linha-__GID__" class="form-select">
                        <option value="" disabled selected>Selecione a etapa</option>
                    </select>
                </div>

                <div class="form-group campo-bncc-sequencial oculto destaque" id="grupo-ano-__GID__">
                    <label for="ano-linha-__GID__">Ano <span class="required">*</span></label>
                    <select id="ano-linha-__GID__" name="ano-linha-__GID__" class="form-select">
                        <option value="" disabled selected>Selecione a etapa primeiro</option>
                    </select>
                </div>

                <div class="form-group campo-bncc-sequencial oculto" id="grupo-area-__GID__">
                    <label for="area-linha-__GID__">Área de conhecimento</label>
                    <select id="area-linha-__GID__" name="area-linha-__GID__" class="form-select"></select>
                </div>

                <div class="form-group campo-bncc-sequencial oculto" id="grupo-componente-__GID__">
                    <label for="componente-linha-__GID__">Componente curricular</label>
                    <select id="componente-linha-__GID__" name="componente-linha-__GID__" class="form-select"></select>
                </div>

                <div class="form-group campo-bncc-sequencial oculto destaque" id="grupo-unidade-__GID__">
                    <label for="unidadeTematica-linha-__GID__">Unidade temática</label>
                    <select id="unidadeTematica-linha-__GID__" name="unidadeTematica-linha-__GID__" class="form-select"></select>
                </div>

                <div class="form-group campo-bncc-sequencial oculto" id="grupo-objetos-__GID__">
                    <label for="objetosConhecimento-linha-__GID__">Objeto do conhecimento</label>
                    <select id="objetosConhecimento-linha-__GID__" name="objetosConhecimento-linha-__GID__" class="form-select"></select>
                </div>

                <div class="form-group campo-bncc-sequencial oculto" id="grupo-habilidades-__GID__">
                    <label for="habilidades-linha-__GID__">Habilidades</label>
                    <select id="habilidades-linha-__GID__" name="habilidades-linha-__GID__[]" class="form-select" multiple></select>
                </div>

                <hr>

                <h4>Detalhes</h4>
                <p>Preencha mais informações para o seu plano.</p>

                <div class="form-group">
                    <label for="conteudos-linha-__GID__">Conteúdos</label>
                    <textarea id="conteudos-linha-__GID__" name="conteudos-linha-__GID__" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label for="metodologias-linha-__GID__">Metodologias</label>
                    <textarea id="metodologias-linha-__GID__" name="metodologias-linha-__GID__" class="form-control"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-primary" id="btnSalvarLinha-__GID__">
                        <i class="fas fa-save"></i> Salvar linha
                    </button>
                    <button type="button" class="btn btn-cancelar" id="btnCancelarLinha-__GID__">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </template>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script type="module" src="{{ asset('assets/js/modules/planning.js') }}" defer></script>
    @endpush
</x-layouts.app>
