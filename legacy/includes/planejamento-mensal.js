// Variáveis globais de controle
let linhasPlanejamento = [];
let editandoLinhaId = null;
let idPlanejamentoMensalParaExcluir = null;
let formPlanejamentoMensal = null;
let choicesAnos = null;

/**
 * Carrega opções no select de destino,
 * marca o valor selecionado e resolve quando terminado.
 *
 * @param {string} idDestino   - id do <select>
 * @param {string} entidade    - campo BNCC (etapas, anos, …)
 * @param {object} filtros     - params extra da query
 * @param {string|number|null} valorSelecionado
 * @param {string|null} grupoMostrar - id do grupo que deve ser exibido
 * @returns {Promise<void>}
 */

// Inicialização única: só acontece quando DOM e BNCC estão prontos
document.addEventListener('DOMContentLoaded', () => {
    fetch('includes/action-bncc.php')
        .then(r => r.json())
        .then(maps => {
            window.bnccMaps = maps;
            inicializarPlanejamentoMensal();
        });
});

function carregarSelectESelecionar(idDestino, entidade, filtros = {}, valorSelecionado = null, grupoMostrar = null) {
    const sel = document.getElementById(idDestino);
    if (!sel) return Promise.resolve();

    limparSelect(idDestino);

    if (idDestino === 'ano-linha' && !sel.querySelector('option[value="todos"]')) {
        sel.insertAdjacentHTML('beforeend', '<option value="todos">Todos os anos</option>');
    }

    // BOTÃO "Cancelar" (fecha todo o CRUD)
    const btnCancelarTudo = document.getElementById('btnCancelarTudo');
    if (btnCancelarTudo) {
        btnCancelarTudo.addEventListener('click', () => {
            const container = document.getElementById('crudPlanejamentoMensalContainer');
            if (!container) return;
            container.innerHTML = '';
            container.classList.add('oculto');
        });
    }

    return fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=${entidade}&${new URLSearchParams(filtros)}`)
        .then(r => r.json())
        .then(lista => {
            lista.forEach(item => {
                sel.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`);
            });

            /* Seleciona o valor salvo — tratando diferença "3"  vs  "3º" */
            if (valorSelecionado !== null) {
                sel.value = valorSelecionado;
                if (sel.selectedIndex === -1) {
                    const alvo = String(valorSelecionado).replace(/\D/g, ''); // só números
                    for (const opt of sel.options) {
                        if (opt.value.replace(/\D/g, '') === alvo) {
                            opt.selected = true;
                            break;
                        }
                    }
                }
            }

            if (grupoMostrar) document.getElementById(grupoMostrar)?.classList.remove('oculto');
        })
        .catch(() => mostrarAlerta('Erro ao carregar dados BNCC!', 'danger'));
}

function inicializarPlanejamentoMensal() {
	const tempoSel = document.getElementById('tempo');
    if (!tempoSel || !tempoSel.value) return;  
	
    formPlanejamentoMensal = document.getElementById('crudPlanejamentoMensalForm');

    // Novo planejamento
    const btnNovo = document.getElementById('btnNovoPlanejamento');
    if (btnNovo) btnNovo.addEventListener('click', e => {
        rolarEDestacarPrimeiroDestaque();
        e.preventDefault();
        abrirCadastroPlanejamentoMensal();
    });

    // Submit CRUD
    if (formPlanejamentoMensal) formPlanejamentoMensal.addEventListener('submit', enviarPlanejamentoMensal);

    // Linha: abrir o formulário de linha
    const btnAddLinha = document.getElementById('btnAdicionarLinha');
	if (btnAddLinha) {
        rolarEDestacarPrimeiroDestaque();
        btnAddLinha.addEventListener('click', e => {
            rolarEDestacarPrimeiroDestaque();
            setTituloSubtituloAddLinhaMensal('criar');
            e.preventDefault();
            reiniciarCascataBNCC();
            document.getElementById('form-linha-bncc')?.classList.remove('oculto');
        });
        document.getElementById('adicionar-linhas')?.classList.remove('oculto');
    }

    // Salvar (ou editar) a linha
    const btnSalvar = document.getElementById('btnSalvarLinha');
    if (btnSalvar) btnSalvar.addEventListener('click', adicionarOuEditarLinha);

    // Cancelar a edição da linha
    const btnCancel = document.getElementById('btnCancelarLinha');
    if (btnCancel) {
        btnCancel.addEventListener('click', limparSubformularioLinha);
        document.getElementById('adicionar-linhas')?.classList.add('oculto');
    }

    // Filtro rápido
    const formFiltro = document.getElementById('filtroPlanejamentosMensais');
    if (formFiltro) {
        formFiltro.addEventListener('submit', e => {
            e.preventDefault();
            const termo = document.getElementById('pesquisaPlanejamentosMensais')?.value.trim() || '';
            fetch(`includes/lista-planejamento-mensal.php?pesquisa=${encodeURIComponent(termo)}&t=${Date.now()}`)
                .then(r => r.text())
                .then(html => {
                    const listaEl = document.getElementById('lista-planejamentos-mensais');
                    if (listaEl) listaEl.innerHTML = html;
                })
                .catch(() => mostrarAlerta('Erro ao buscar planejamentos!', 'danger'));
        });
    }

    // BNCC/Choices/Summernote apenas uma vez na inicialização
    bindSequencialBNCC();
    carregarMateriasDoProfessor();
    atualizarListaPlanejamentosMensais();

    // Choices.js para anos
    const selAnos = document.getElementById('anos-plano');
    if (window.Choices && selAnos) {
        choicesAnos = new Choices(selAnos, {
            removeItemButton: true,
            placeholderValue: 'Selecione os anos',
            searchResultLimit: 9
        });
    }

    // Listener para "Selecionar todos os anos"
    const chkTodos = document.getElementById('checkTodosAnos');
    if (chkTodos && selAnos) {
        chkTodos.addEventListener('change', () => {
            const allValues = Array.from(selAnos.options).filter(o => o.value).map(o => o.value);
            if (chkTodos.checked) {
                if (choicesAnos) choicesAnos.setValue(allValues);
                else selAnos.querySelectorAll('option').forEach(o => {
                    if (o.value) o.selected = true;
                });
            } else {
                if (choicesAnos) choicesAnos.removeActiveItems();
                else selAnos.querySelectorAll('option').forEach(o => (o.selected = false));
            }
        });
    }

    // Summernote campos texto rico
    if (window.jQuery) {
        $('#conteudos-linha, #metodologias-linha').summernote({ height: 140 });
        $('#objetivo_geral, #objetivo_especifico').summernote({ height: 140 });
    }

    // Choices para habilidades linha (se usado)
    if (window.Choices) {
        const selH = document.getElementById('habilidades-linha');
        if (selH) window.choicesHabilidadesLinha = new Choices(selH, { removeItemButton: true });
    }
}

// Carrega as matérias do professor via AJAX
function carregarMateriasDoProfessor() {
    return fetch('includes/action-planejamento-mensal.php?acao=materias_do_professor')
        .then(r => r.json())
        .then(lista => {
            const select = document.getElementById('materia');
            if (!select) return;
            select.innerHTML = '<option value="">Selecione a matéria</option>';
            lista.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.label;
                select.appendChild(opt);
            });
        })
        .catch(() => mostrarAlerta('Erro ao carregar matérias!', 'danger'));
}

// Utilitário para título e subtítulo do formulário
function setTituloSubtituloFormPlanejamentoMensal(acao) {
    const t = document.getElementById('tituloFormPlanejamentoMensal');
    const s = document.getElementById('subtituloFormPlanejamentoMensal');
    if (!t || !s) return;
    if (acao === 'criar') {
        t.textContent = 'Novo Planejamento Mensal';
        s.textContent = 'Preencha os dados para cadastrar um novo planejamento.';
    } else {
        t.textContent = 'Editar Planejamento Mensal';
        s.textContent = 'Altere os campos desejados e clique em Salvar.';
    }
}

// Utilitário para título e subtítulo da Linha
function setTituloSubtituloAddLinhaMensal(acao) {
    const t = document.getElementById('tituloAddLinhaMensal');
    const s = document.getElementById('subtituloAddLinhaMensal');
    if (!t || !s) return;
    if (acao === 'criar') {
        t.textContent = 'Nova linha';
        s.textContent = 'Preencha os dados da BNCC para cadastrar';
    } else {
        t.textContent = 'Editar linha';
        s.textContent = 'Altere os campos desejados e clique em Salvar.';
    }
}

// Limpa um select mantendo apenas o "Selecione..."
function limparSelect(idSelect) {
    const select = document.getElementById(idSelect);
    if (select) select.innerHTML = '<option value="">Selecione...</option>';
}

/**
 * Recomeça a cascata BNCC do zero:
 *  - limpa selects
 *  - oculta grupos (de ano em diante)
 *  - recarrega apenas as etapas
 *  - garante textos corretos dos botões
 */
function reiniciarCascataBNCC() {
    // limpa selects principais
    ['etapa-linha', 'ano-linha', 'area-linha', 'componente-linha', 'unidadeTematica-linha', 'objetosConhecimento-linha', 'habilidades-linha'].forEach(id => limparSelect(id));

    // oculta todos os grupos, exceto etapa
    ['grupo-ano', 'grupo-area', 'grupo-componente', 'grupo-unidade', 'grupo-objetos', 'grupo-habilidades'].forEach(g => document.getElementById(g)?.classList.add('oculto'));
    document.getElementById('grupo-etapa')?.classList.remove('oculto');

    // zera estado de edição e rótulos de botões
    editandoLinhaId = null;
    const btnAdd = document.getElementById('btnAdicionarLinha');
    if (btnAdd) btnAdd.innerHTML = '<i class="fas fa-plus"></i> Criar nova linha';

    const btnSalvar = document.getElementById('btnSalvarLinha');
    if (btnSalvar) btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar Linha';

    // busca etapas (única chamada fetch)
    fetch('includes/action-planejamento-mensal.php?acao=bncc&campo=etapas')
        .then(r => r.json())
        .then(lista => {
            const sel = document.getElementById('etapa-linha');
            if (!sel) return;
            sel.innerHTML = '<option value="">Selecione a etapa</option>';
            lista.forEach(item => sel.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
        })
        .catch(() => mostrarAlerta('Erro ao carregar etapas!', 'danger'));
}

// Busca o nome BNCC conforme tipo e id (com fallback)
function getNomeBNCC(tipo, id) {
    const chave = String(id);
    if (tipo === 'habilidades') {
        return window.bnccMaps?.['habilidades-linha']?.[chave] || window.bnccMaps?.['habilidades']?.[chave] || chave || '';
    }
    return window.bnccMaps?.[tipo]?.[chave] || chave || '';
}

// Fecha o Edit/Criar
function fecharCrudPlanejamento() {
    const container = document.getElementById('crudPlanejamentoMensalContainer');
    if (!container) return;
    container.innerHTML = '';
    container.classList.add('oculto');
}

// Serializa as linhas para o input hidden do form
function serializarLinhasNoForm() {
    const h = document.getElementById('linhas-planejamento-serialized');
    if (h) h.value = JSON.stringify(linhasPlanejamento);
}

// Renderiza a tabela de linhas do planejamento
function renderizarTabelaLinhas() {
    const tbody = document.getElementById('tbody-linhas-planejamento');
    if (!tbody) return;
    tbody.innerHTML = '';
    linhasPlanejamento.forEach((l, i) => {
        console.log(`Linha ${i}: l.area=`, l.area, '| tipo:', typeof l.area, '| linha:', l);
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i + 1}</td>
            <td>${getNomeBNCC('etapas', l.etapa)}</td>
            <td>${getNomeBNCC('areas', l.area)}</td>
            <td>${getNomeBNCC('componentes', l.componenteCurricular)}</td>
            <td>${getNomeBNCC('unidades_tematicas', l.unidadeTematicas)}</td>
            <td>${getNomeBNCC('objetosConhecimento', l.objetosConhecimento)}</td>
            <td>${l.habilidades.map(h => getNomeBNCC('habilidades', h)).join(', ')}</td>
            <td>
                <button type="button" class="btn-action btn-edit" onclick="editarLinha(${i})">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn-action btn-danger" onclick="abrirConfirmacaoExclusao('linha',${i})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });
}

// Limpa os campos do subformulário de linha
function limparSubformularioLinha() {
    const formLinha = document.getElementById('form-linha-bncc');
    if (formLinha) {
        formLinha.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = false;
            } else {
                el.value = '';
            }
        });
        if (window.choicesHabilidadesLinha) window.choicesHabilidadesLinha.removeActiveItems();
        if (window.jQuery) {
            $('#conteudos-linha').summernote('code', '');
            $('#metodologias-linha').summernote('code', '');
        }
    }
    editandoLinhaId = null;
    const btn = document.getElementById('btnAdicionarLinha');
    if (btn) btn.innerHTML = '<i class="fas fa-plus"></i> Criar nova linha';
    rolarEDestacarPrimeiroDestaque();
}

// Adiciona ou edita uma linha no array
function adicionarOuEditarLinha() {
    rolarEDestacarPrimeiroDestaque();
    const etapa = document.getElementById('etapa-linha')?.value;
    const ano = document.getElementById('ano-linha')?.value;
    const area = document.getElementById('area-linha')?.value;
    const comp = document.getElementById('componente-linha')?.value;
    const uni = document.getElementById('unidadeTematica-linha')?.value;
    const obj = document.getElementById('objetosConhecimento-linha')?.value;

    // Conteúdos & Metodologias
    let cont = '';
    let met = '';
    if (window.jQuery) {
        cont = $('#conteudos-linha').summernote('code');
        met = $('#metodologias-linha').summernote('code');
    }

    if (!etapa || !ano || !area || !comp) {
        mostrarAlerta('Preencha todos os campos obrigatórios da linha!', 'danger');
        return;
    }

    const hab = Array.from(document.getElementById('habilidades-linha').selectedOptions).map(o => String(o.value));

    const linhaObj = {
        etapa,
        ano,
        area,
        componenteCurricular: comp,
        unidadeTematicas: uni,
        objetosConhecimento: obj,
        habilidades: hab,
        conteudos: cont,
        metodologias: met
    };

    if (editandoLinhaId !== null) {
        linhasPlanejamento[editandoLinhaId] = linhaObj;
        mostrarAlerta('Linha alterada com sucesso!', 'success');
    } else {
        linhasPlanejamento.push(linhaObj);
        mostrarAlerta('Linha adicionada com sucesso!', 'success');
    }

    renderizarTabelaLinhas();
    serializarLinhasNoForm();
    limparSubformularioLinha();
    const formLinha = document.getElementById('form-linha-bncc');
    if (formLinha) formLinha.classList.add('oculto');
}

// Carrega dados de uma linha para edição
function editarLinha(idx) {
    const l = linhasPlanejamento[idx];
    if (!l) return;
    rolarEDestacarPrimeiroDestaque();
    setTituloSubtituloAddLinhaMensal('editar');

    const formLinha = document.getElementById('form-linha-bncc');
    if (formLinha) formLinha.classList.remove('oculto');
    document.getElementById('adicionar-linhas')?.classList.remove('oculto');
    editandoLinhaId = idx;

    document.getElementById('etapa-linha').value = l.etapa;
    document.getElementById('ano-linha').value = l.ano;
    document.getElementById('area-linha').value = l.area;
    document.getElementById('componente-linha').value = l.componenteCurricular;
    document.getElementById('unidadeTematica-linha').value = l.unidadeTematicas;
    document.getElementById('objetosConhecimento-linha').value = l.objetosConhecimento;

    // Encadeia selects
    carregarSelectESelecionar('etapa-linha', 'etapas', {}, l.etapa, 'grupo-etapa')
        .then(() => carregarSelectESelecionar('ano-linha', 'anos', { id_etapa: l.etapa }, l.ano, 'grupo-ano'))
        .then(() => carregarSelectESelecionar('area-linha', 'areas', { id_etapa: l.etapa }, l.area, 'grupo-area'))
        .then(() => carregarSelectESelecionar('componente-linha', 'componentes', { id_area: l.area }, l.componenteCurricular, 'grupo-componente'))
        .then(() => carregarSelectESelecionar('unidadeTematica-linha', 'unidades_tematicas', { id_componente: l.componenteCurricular }, l.unidadeTematicas, 'grupo-unidade'))
        .then(() => carregarSelectESelecionar('objetosConhecimento-linha', 'objetosConhecimento', { id_unidade_tematica: l.unidadeTematicas }, l.objetosConhecimento, 'grupo-objetos'))
        .then(() => {
            if (!window.choicesHabilidadesLinha) return;
            return fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=habilidades&id_objeto=${l.objetosConhecimento}`)
                .then(r => r.json())
                .then(lista => {
                    window.choicesHabilidadesLinha.clearChoices();
                    window.choicesHabilidadesLinha.setChoices(
                        lista.map(item => ({ value: String(item.id), label: item.label, disabled: false })),
                        'value',
                        'label',
                        true
                    );
                    window.choicesHabilidadesLinha.setChoiceByValue(l.habilidades.map(String));
                    document.getElementById('grupo-habilidades')?.classList.remove('oculto');
                });
        })
        .then(() => {
            if (window.jQuery) {
                $('#conteudos-linha').summernote('code', l.conteudos);
                $('#metodologias-linha').summernote('code', l.metodologias);
            }
            const btnSalvar = document.getElementById('btnSalvarLinha');
            if (btnSalvar) btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar alterações';
        });
}

// Exclui uma linha do array
function excluirLinha(idx) {
    if (!confirm('Deseja realmente excluir esta linha?')) return;
    linhasPlanejamento.splice(idx, 1);
    renderizarTabelaLinhas();
    serializarLinhasNoForm();
}

/**
 * Oculta grupos BNCC a partir de um índice ou mostra apenas o alvo
 */
function ocultarGruposApartir(alvo) {
    const grupos = ['grupo-ano', 'grupo-area', 'grupo-componente', 'grupo-unidade', 'grupo-objetos', 'grupo-habilidades'];
    grupos.forEach(g => document.getElementById(g)?.classList.add('oculto'));

    if (typeof alvo === 'number') {
        grupos.forEach((g, i) => {
            if (i < alvo) document.getElementById(g)?.classList.remove('oculto');
        });
    } else if (typeof alvo === 'string') {
        document.getElementById(alvo)?.classList.remove('oculto');
    }
}

// Função de cascata de selects da BNCC (versão simplificada usando carregarSelect)
function carregarSelect(idDestino, entidade, filtros = {}, grupoMostrar = null) {
    const sel = document.getElementById(idDestino);
    if (!sel) return;
    limparSelect(idDestino);

    if (idDestino === 'ano-linha') {
        sel.insertAdjacentHTML('beforeend', '<option value="todos">Todos os anos</option>');
    }

    fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=${entidade}&${new URLSearchParams(filtros)}`)
        .then(r => r.json())
        .then(lista => {
            lista.forEach(item => sel.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
            if (grupoMostrar) document.getElementById(grupoMostrar)?.classList.remove('oculto');
        })
        .catch(() => mostrarAlerta('Erro ao carregar dados BNCC!', 'danger'));
}

// Encadeamento dos selects BNCC (etapa -> ano -> área -> ...)
function bindSequencialBNCC() {
    const selEtapa = document.getElementById('etapa-linha');
    if (selEtapa) {
        selEtapa.addEventListener('change', function () {
            limparSelect('ano-linha');
            limparSelect('area-linha');
            limparSelect('componente-linha');
            limparSelect('unidadeTematica-linha');
            limparSelect('objetosConhecimento-linha');
            limparSelect('habilidades-linha');

            fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=anos&id_etapa=${this.value}`)
                .then(r => r.json())
                .then(lista => {
                    const selAno = document.getElementById('ano-linha');
                    selAno.innerHTML = '<option value="">Selecione o ano</option>';
                    lista.forEach(item => selAno.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
                    document.getElementById('grupo-ano')?.classList.remove('oculto');
                });
        });
    }

    const selAno = document.getElementById('ano-linha');
    if (selAno) {
        selAno.addEventListener('change', function () {
            rolarEDestacarPrimeiroDestaque();
            limparSelect('area-linha');
            limparSelect('componente-linha');
            limparSelect('unidadeTematica-linha');
            limparSelect('objetosConhecimento-linha');
            limparSelect('habilidades-linha');
            const idEtapa = selEtapa.value;

            fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=areas&id_etapa=${idEtapa}`)
                .then(r => r.json())
                .then(lista => {
                    const selArea = document.getElementById('area-linha');
                    selArea.innerHTML = '<option value="">Selecione a área</option>';
                    lista.forEach(item => selArea.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
                    document.getElementById('grupo-area')?.classList.remove('oculto');
                });
        });
    }

    const selArea = document.getElementById('area-linha');
    if (selArea) {
        selArea.addEventListener('change', function () {
            limparSelect('componente-linha');
            limparSelect('unidadeTematica-linha');
            limparSelect('objetosConhecimento-linha');
            limparSelect('habilidades-linha');

            fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=componentes&id_area=${this.value}`)
                .then(r => r.json())
                .then(lista => {
                    const selComp = document.getElementById('componente-linha');
                    selComp.innerHTML = '<option value="">Selecione o componente</option>';
                    lista.forEach(item => selComp.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
                    document.getElementById('grupo-componente')?.classList.remove('oculto');
                });
        });
    }

    const selComp = document.getElementById('componente-linha');
    if (selComp) {
        selComp.addEventListener('change', function () {
            limparSelect('unidadeTematica-linha');
            limparSelect('objetosConhecimento-linha');
            limparSelect('habilidades-linha');

            fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=unidades_tematicas&id_componente=${this.value}`)
                .then(r => r.json())
                .then(lista => {
                    const selUni = document.getElementById('unidadeTematica-linha');
                    selUni.innerHTML = '<option value="">Selecione a unidade temática</option>';
                    lista.forEach(item => selUni.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
                    document.getElementById('grupo-unidade')?.classList.remove('oculto');
                });
        });
    }

    const selUni = document.getElementById('unidadeTematica-linha');
    if (selUni) {
        selUni.addEventListener('change', function () {
            limparSelect('objetosConhecimento-linha');
            limparSelect('habilidades-linha');

            fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=objetosConhecimento&id_unidade_tematica=${this.value}`)
                .then(r => r.json())
                .then(lista => {
                    const selObj = document.getElementById('objetosConhecimento-linha');
                    selObj.innerHTML = '<option value="">Selecione o objeto do conhecimento</option>';
                    lista.forEach(item => selObj.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
                    document.getElementById('grupo-objetos')?.classList.remove('oculto');
                });
        });
    }

    const selObj = document.getElementById('objetosConhecimento-linha');
    if (selObj) {
        selObj.addEventListener('change', function () {
            if (window.choicesHabilidadesLinha) {
                window.choicesHabilidadesLinha.clearStore();
                window.choicesHabilidadesLinha.clearChoices();
                fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=habilidades&id_objeto=${this.value}`)
                    .then(r => r.json())
                    .then(lista => {
                        window.choicesHabilidadesLinha.setChoices(lista.map(item => ({
                            value: item.id,
                            label: item.habilidade || item.label,
                            selected: false,
                            disabled: false
                        })), 'value', 'label', false);
                        document.getElementById('grupo-habilidades')?.classList.remove('oculto');
                    });
            } else {
                limparSelect('habilidades-linha');
                fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=habilidades&id_objeto=${this.value}`)
                    .then(r => r.json())
                    .then(lista => {
                        const selHab = document.getElementById('habilidades-linha');
                        selHab.innerHTML = '';
                        lista.forEach(item => selHab.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.habilidade || item.label}</option>`));
                        document.getElementById('grupo-habilidades')?.classList.remove('oculto');
                    });
            }
        });
    }
}

// Busca etapas BNCC direto
function carregarEtapasBNCC() {
    const sel = document.getElementById('etapa-linha');
    if (!sel) return;
    sel.innerHTML = '<option value="">Selecione a etapa</option>';

    fetch('includes/action-planejamento-mensal.php?acao=bncc&campo=etapas')
        .then(r => r.json())
        .then(lista => lista.forEach(item => sel.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`)))
        .catch(() => mostrarAlerta('Erro ao carregar etapas!', 'danger'));
}

// Reseta subformulário
function resetarSubformularioLinha() {
    limparSubformularioLinha();
    ['grupo-ano', 'grupo-area', 'grupo-componente', 'grupo-unidade', 'grupo-objetos', 'grupo-habilidades'].forEach(g => document.getElementById(g)?.classList.add('oculto'));
    document.getElementById('grupo-etapa')?.classList.remove('oculto');
    carregarEtapasBNCC();
}

// Limpa cookies de planejamento
function limparCookies() {
    if (!confirm('Tem certeza que deseja limpar dados salvos?')) return;
    [
        'planejamento_data',
        'planejamento_curso',
        'planejamento_ano',
        'planejamento_sequencial',
        'planejamento_componente_curricular',
        'planejamento_numero_aulas_semanais',
        'planejamento_objetivo_geral',
        'planejamento_objetivo_especifico',
        'planejamento_tipo',
        'planejamento_diagnostico',
        'planejamento_referencias',
        'planejamento_objetosConhecimento',
        'planejamento_projetos_integrador'
    ].forEach(c => {
        document.cookie = `${c}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    });
    mostrarAlerta('Dados limpos com sucesso!', 'success');
    location.reload();
}

// ------- CRUD Principal ---------

function abrirCadastroPlanejamentoMensal() {
    formPlanejamentoMensal.reset();
    setTituloSubtituloFormPlanejamentoMensal('criar');

    // Carrega ciclos
    fetch('includes/action-planejamento-mensal.php?acao=listar_ciclos')
        .then(r => r.json())
        .then(dados => {
            const tempo = document.getElementById('tempo');
            if (!tempo) return;
            tempo.innerHTML = '<option value="" disabled selected>Selecione o tipo de ciclo</option>';
            if (dados && dados.length) {
                dados.forEach(item => {
                    tempo.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.nome}</option>`);
                });
            }
        });

    // Mudança de ciclo
document.getElementById('tempo').onchange = async function () {
    const idPeriodo = this.value;

    // 1. Descobre quantos “grupos” devem aparecer
    const cicloResp = await fetch(
        `includes/action-planejamento-mensal.php?acao=detalhe_ciclo&id=${idPeriodo}`
    ).then(r => r.json());

    if (!cicloResp || !cicloResp.quantidadeMeses) return;

    // 2. Pede ao servidor o HTML de add-linha.php (uma vez só)
    const htmlAddLinha = await fetch(
        'includes/action-planejamento-mensal.php?acao=html_add_linha'
    ).then(r => r.text());

    // 3. Monta os blocos
    let blocos = '';
    for (let i = 1; i <= cicloResp.quantidadeMeses; i++) {
        blocos += `
          <div class="container destaque" data-grupo="${i}">
              <h4>Grupo ${i}</h4>
              ${htmlAddLinha}
          </div>`;
    }
    document.getElementById('blocos-planejamento').innerHTML = blocos;
};



    if (window.jQuery) {
        $('#objetivo_geral').summernote('code', '');
        $('#objetivo_especifico').summernote('code', '');
    }
    if (window.Choices && choicesAnos) {
        choicesAnos.setValue([]);
    }

    rolarEDestacarPrimeiroDestaque();
    document.getElementById('crudPlanejamentoMensalContainer')?.classList.remove('oculto');
    linhasPlanejamento = [];
    renderizarTabelaLinhas();
    serializarLinhasNoForm();
    carregarMateriasDoProfessor();

    document.getElementById('grupo-etapa')?.classList.remove('oculto');
    reiniciarCascataBNCC();

    fetch('includes/action-planejamento-mensal.php?acao=bncc&campo=etapas')
        .then(r => r.json())
        .then(lista => {
            const sel = document.getElementById('etapa-linha');
            sel.innerHTML = '<option value="">Selecione a etapa</option>';
            lista.forEach(item => sel.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.label}</option>`));
        })
        .catch(() => mostrarAlerta('Erro ao carregar etapas!', 'danger'));

    ['ano-linha', 'area-linha', 'componente-linha', 'unidadeTematica-linha', 'objetosConhecimento-linha', 'habilidades-linha'].forEach(id => limparSelect(id));
}

async function editarPlanejamentoMensal(id) {
    await carregarMateriasDoProfessor();
    fetch(`includes/action-planejamento-mensal.php?acao=buscar&id=${id}`)
        .then(r => r.json())
        .then(json => {
            if (!json.sucesso) return mostrarAlerta(json.mensagem, 'danger');
            formPlanejamentoMensal.reset();
            Object.entries(json.cabecalho).forEach(([k, v]) => {
                const campo = formPlanejamentoMensal.querySelector(`[name="${k}"]`);
                if (campo) campo.value = v;
            });

            // Choices.js para anos
            if (window.Choices && choicesAnos && json.cabecalho.anos_plano) {
                const anosArray = Array.isArray(json.cabecalho.anos_plano) ? json.cabecalho.anos_plano : String(json.cabecalho.anos_plano).split(',').map(s => s.trim()).filter(Boolean);
                choicesAnos.setValue(anosArray);
            }

            // Summernote para objetivos
            if (window.jQuery) {
                $('#objetivo_geral').summernote('code', json.cabecalho.objetivo_geral || '');
                $('#objetivo_especifico').summernote('code', json.cabecalho.objetivo_especifico || '');
            }

            rolarEDestacarPrimeiroDestaque();
            linhasPlanejamento = json.linhas;
            renderizarTabelaLinhas();
            serializarLinhasNoForm();
            setTituloSubtituloFormPlanejamentoMensal('editar');
            document.getElementById('crudPlanejamentoMensalContainer')?.classList.remove('oculto');
        })
        .catch(() => mostrarAlerta('Erro ao carregar planejamento!', 'danger'));
}

function atualizarListaPlanejamentosMensais() {
    fetch(`includes/lista-planejamento-mensal.php?t=${Date.now()}`)
        .then(r => r.text())
        .then(html => {
            const container = document.getElementById('lista-planejamentos-mensais');
            if (container) container.innerHTML = html;
        })
        .catch(() => mostrarAlerta('Erro ao carregar lista de planejamentos!', 'danger'));
}

function enviarPlanejamentoMensal(e) {
    e.preventDefault();
    if (linhasPlanejamento.length === 0) {
        mostrarAlerta('Adicione ao menos um tema antes de salvar!', 'warning');
        return;
    }
    serializarLinhasNoForm();
    const fd = new FormData(formPlanejamentoMensal);
    fd.append('acao', formPlanejamentoMensal['id-planejamento-mensal'].value ? 'editar' : 'criar');

    fetch('includes/action-planejamento-mensal.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.sucesso) {
                mostrarAlerta(res.mensagem, 'success');
                fecharFormPlanejamentoMensal();
                atualizarListaPlanejamentosMensais();
            } else {
                mostrarAlerta(res.mensagem, 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            mostrarAlerta('Erro de comunicação!', 'danger');
        });
}

function fecharFormPlanejamentoMensal() {
    document.getElementById('crudPlanejamentoMensalContainer')?.classList.add('oculto');
    formPlanejamentoMensal.reset();
    linhasPlanejamento = [];
    renderizarTabelaLinhas();
}

function abrirModalExcluirPlanejamento(id) {
    idPlanejamentoMensalParaExcluir = id;
    const modal = new bootstrap.Modal(document.getElementById('modalGeral'));
    document.getElementById('modalGeralLabel').textContent = 'Confirmar exclusão';
    document.getElementById('modalGeralBody').textContent = 'Deseja realmente excluir este planejamento?';
    document.getElementById('modalGeralConfirmar').onclick = confirmarExclusaoAJAXPlanejamentoMensal;
    modal.show();
}

function confirmarExclusaoAJAXPlanejamentoMensal() {
    if (!idPlanejamentoMensalParaExcluir) return;
    const fd = new FormData();
    fd.append('acao', 'excluir');
    fd.append('id', idPlanejamentoMensalParaExcluir);

    fetch('includes/action-planejamento-mensal.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(json => {
            if (json.sucesso) {
                mostrarAlerta('Planejamento excluído!', 'success');
                atualizarListaPlanejamentosMensais();
            } else {
                mostrarAlerta(json.mensagem, 'danger');
            }
            bootstrap.Modal.getInstance(document.getElementById('modalGeral')).hide();
        })
        .catch(() => mostrarAlerta('Falha ao excluir!', 'danger'));
}

/* ------------------ Variáveis de controle ------------------ */
let exclusaoContexto = {
    tipo: null, // 'material' | 'plano' | 'linha'
    id: null
};

/* ------------------ Abrir modal genérico ------------------ */
function abrirConfirmacaoExclusao(tipo, id) {
    if (!['material', 'plano', 'linha'].includes(tipo)) {
        console.error(`Tipo "${tipo}" não suportado na exclusão.`);
        return;
    }

    rolarEDestacarPrimeiroDestaque();
    exclusaoContexto = { tipo, id };

    const label = document.getElementById('modalGeralLabel');
    if (label) label.innerText = `Excluir ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`;

    const body = document.getElementById('modalGeralBody');
    if (body) body.innerText = `Deseja realmente excluir este ${tipo}?`;

    const btn = document.getElementById('modalGeralConfirmar');
    if (btn) btn.onclick = confirmarExclusaoAJAX;

    new bootstrap.Modal(document.getElementById('modalGeral')).show();
}

/* ------------------ Confirmação AJAX ------------------ */
function confirmarExclusaoAJAX() {
    const { tipo, id } = exclusaoContexto;
    if (!id) return;

    const acao = tipo === 'plano' ? 'excluir' : 'excluir_linha';

    fetch('includes/action-planejamento-mensal.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=${encodeURIComponent(acao)}&id=${encodeURIComponent(id)}`
    })
        .then(async r => {
            const text = await r.text();
            try {
                const res = JSON.parse(text);
                if (res.sucesso) {
                    mostrarAlerta(res.mensagem || 'Excluído com sucesso.', 'success');
                    removerElementoDaUI(tipo, id);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalGeral'));
                    if (modal) modal.hide();
                    atualizarListaPlanejamentosMensais();
                } else {
                    mostrarAlerta(res.mensagem || 'Erro ao excluir.', 'danger');
                }
            } catch (e) {
                console.error('Erro ao interpretar resposta do servidor:', text);
                mostrarAlerta('Resposta inválida do servidor.', 'danger');
            }
        })
        .catch(err => {
            console.error('Erro na requisição:', err);
            mostrarAlerta('Erro de conexão ou servidor ao excluir.', 'danger');
        });
}

/* ------------- Helper opcional para atualizar a UI ------------- */
function removerElementoDaUI(tipo, id) {
    const linha = document.getElementById(`linha-${tipo}-${id}`);
    if (linha) linha.remove();
}
