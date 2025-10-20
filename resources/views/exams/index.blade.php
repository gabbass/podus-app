<x-layouts.app :user="$user">
    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-end">
            <div class="page-title">
                <h1>Provas</h1>
                <p>Visualize, crie e edite suas provas.</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary" id="btnNovaProva">
                    <i class="fas fa-plus"></i> Nova Prova
                </button>
            </div>
        </div>

        <div id="boxCrudProva" class="segundo-container oculto destaque">
            <div id="crudLoading" class="overlay oculto">
                <div class="spinner-border" role="status"></div>
            </div>
            <div id="containerCrudProva">
                <div class="page-title">
                    <h2 id="tituloCrudProvas">Criar provas</h2>
                    <p id="subtituloCrudProvas">Visualize, crie e edite suas provas.</p>
                </div>
                <form id="formProvas">
                    <input type="hidden" id="idProva" name="id">
                    <div class="row g-3 mb-3">
                        <div class="col-md form-group">
                            <label for="turma" class="form-label">Turma</label>
                            <select id="turma" name="turma" class="form-control" required>
                                <option value="">Selecione...</option>
                                @foreach ($turmas as $turma)
                                    <option value="{{ $turma->nome }}">{{ $turma->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md form-group">
                            <label for="escola" class="form-label">Escola <small>(opcional)</small></label>
                            <input type="text" id="escola" name="escola" class="form-control">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="materia" class="form-label">Matéria</label>
                        <select id="materia" name="materia" class="form-control" required data-materias='@json($materias)'>
                            <option value="">Selecione...</option>
                            @foreach ($materias as $nome)
                                <option value="{{ $nome }}">{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 form-group">
                        <label for="lista_quest" class="form-label">Questões da Prova</label>
                        <select id="lista_quest" name="lista_quest[]" class="form-control" multiple required size="7">
                            @foreach ($questoes as $questao)
                                <option value="{{ $questao->id }}">{{ $questao->id }} - {{ \Illuminate\Support\Str::limit(strip_tags($questao->questao), 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" id="btnSalvarProva" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <button type="button" id="btnCancelarProva" class="btn btn-cancelar">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="filtros-container" id="filtrosContainerProvas">
            <h3>Filtrar</h3>
            <p>Procure digitando um texto</p>
            <form id="filtrosFormProvas" class="filtros-form">
                <div class="filtros-row">
                    <div class="filtro-group">
                        <label for="filtroTextoProvas">Buscar texto</label>
                        <input type="text" id="filtroTextoProvas" name="filtroTexto" placeholder="Buscar por texto">
                    </div>
                </div>
                <div class="filtros-actions">
                    <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
                    <button type="button" id="btnLimparFiltroProvas" class="btn-limpar"><i class="fas fa-xmark"></i> Limpar</button>
                </div>
            </form>
            <div class="contador-registros">
                Exibindo <strong id="totalRegistrosProvas">{{ $exams->count() }}</strong> registro(s)
            </div>
        </div>

        <div class="table-container" id="listaContainerProvas">
            <table id="tabelaProvas" class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Turma</th>
                        <th>Matéria</th>
                        <th>Número de questões</th>
                        <th>Escola</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tbodyProvas">
                    @foreach ($exams as $prova)
                        <tr data-id="{{ $prova->id }}">
                            <td>{{ $prova->id }}</td>
                            <td>{{ $prova->turma }}</td>
                            <td>{{ $prova->materia }}</td>
                            <td>{{ $prova->lista_quest ? count(explode(',', $prova->lista_quest)) : 0 }}</td>
                            <td>{{ $prova->escola }}</td>
                            <td>
                                <button class="btn-action btn-edit" data-action="editar-prova" data-id="{{ $prova->id }}"><i class="fas fa-edit"></i></button>
                                <button class="btn-action btn-view" data-action="visualizar-prova" data-id="{{ $prova->id }}"><i class="fas fa-eye"></i></button>
                                <button class="btn-action btn-delete" data-action="excluir-prova" data-id="{{ $prova->id }}"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <x-modal />

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script src="{{ asset('legacy/js/provas.js') }}"></script>
    @endpush
</x-layouts.app>
