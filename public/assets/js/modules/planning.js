const API_BASE = '/api/planning';

window.choicesHabilidadesMap = window.choicesHabilidadesMap || {};
let linhasPlanejamento = [];
let editandoLinhaId = null;
let linhasPlanejamentoParaExcluir = [];
let materiasLookup = {};
let choicesAnos = null;
let bnccOptions = {};
let bnccLookups = {};
let podeAprovarReservas = false;
let podeReservar = false;
let usuarioId = null;
let linhaTemplateHtml = '';
let currentPlanningId = null;
let reservasPlanejamento = [];

const planningRoot = document.querySelector('[data-planning-app]');
if (planningRoot) {
    podeAprovarReservas = planningRoot.dataset.canApprove === 'true';
    podeReservar = planningRoot.dataset.canReserve === 'true';
    usuarioId = planningRoot.dataset.userId ? Number(planningRoot.dataset.userId) : null;
}

function showModalGeral({ titulo = '', corpo = '', textoConfirmar = 'Confirmar', onConfirm }) {
    const modalEl = document.getElementById('modalGeral');
    const modalTitleEl = document.getElementById('modalGeralLabel');
    const modalBodyEl = document.getElementById('modalGeralBody');
    const btnConfirmar = document.getElementById('modalGeralConfirmar');

    if (!modalEl || !modalTitleEl || !modalBodyEl || !btnConfirmar) {
        console.error('Modal geral não encontrado!');
        return;
    }

    modalTitleEl.textContent = titulo;
    modalBodyEl.innerHTML = corpo;
    btnConfirmar.textContent = textoConfirmar;
    btnConfirmar.onclick = () => {
        bootstrap.Modal.getInstance(modalEl).hide();
        if (typeof onConfirm === 'function') onConfirm();
    };

    new bootstrap.Modal(modalEl).show();
}

function mostrarAlerta(mensagem, tipo = 'info') {
    if (!planningRoot) {
        alert(mensagem);
        return;
    }

    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo}`;
    alerta.textContent = mensagem;
    planningRoot.prepend(alerta);
    setTimeout(() => alerta.remove(), 5000);
}

function destacarPrimeiroElemento(selector) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.classList.add('destaque');
    setTimeout(() => el.classList.remove('destaque'), 1200);
}

function rolarEDestacarPrimeiroDestaque() {
    destacarPrimeiroElemento('.destaque, [data-destaque]');
}

function toInt(v) {
    const parsed = parseInt(v, 10);
    return Number.isNaN(parsed) ? 0 : parsed;
}

async function carregarBnccMaps() {
    const response = await fetch(`${API_BASE}?acao=bncc&campo=mapas`);
    const json = await response.json();
    if (json && json.sucesso) {
        bnccOptions = json.dados?.options || {};
        bnccLookups = json.dados?.lookups || {};
        window.bnccOptions = bnccOptions;
        window.bnccMaps = bnccLookups;
    } else {
        throw new Error(json?.mensagem || 'Falha ao carregar mapas BNCC.');
    }
}

function obterTemplateLinha() {
    const templateEl = document.getElementById('planning-line-template');
    if (!templateEl) {
        linhaTemplateHtml = '';
        return;
    }

    linhaTemplateHtml = templateEl.innerHTML.trim();
}

function gerarHtmlBloco(gid) {
    if (!linhaTemplateHtml) {
        return null;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = linhaTemplateHtml.replace(/__GID__/g, String(gid));
    return wrapper.firstElementChild;
}

function carregarOpcoesNoSelect(idSelect, entidade, valorSelecionado = null) {
    const sel = document.getElementById(idSelect);
    if (!sel) return;
    const itens = bnccOptions[entidade] || [];
    sel.innerHTML = '<option value="">Selecione...</option>';
    itens.forEach((item) => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.label || item.nome;
        if (valorSelecionado != null) {
            if (Array.isArray(valorSelecionado) && valorSelecionado.includes(item.id)) {
                opt.selected = true;
            } else if (item.id == valorSelecionado) {
                opt.selected = true;
            }
        }
        sel.appendChild(opt);
    });
}

function getNomeBNCC(tipo, id) {
    const chave = String(id);
    if (tipo === 'habilidades') {
        return bnccLookups['habilidades-linha']?.[chave] || bnccLookups['habilidades']?.[chave] || chave || '';
    }

    return bnccLookups[tipo]?.[chave] || chave || '';
}

function setTituloSubtituloCRUD(acao) {
    const titulo = document.getElementById('tituloFormPlanejamentoMensal');
    const subtitulo = document.getElementById('subtituloFormPlanejamentoMensal');
    if (!titulo || !subtitulo) return;
    if (acao === 'criar') {
        titulo.textContent = 'Novo planejamento';
        subtitulo.textContent = 'Preencha os dados para cadastrar um novo planejamento.';
    } else if (acao === 'editar') {
        titulo.textContent = 'Editar planejamento';
        subtitulo.textContent = 'Altere os dados e clique em salvar.';
    } else {
        titulo.textContent = 'Carregando...';
        subtitulo.textContent = 'Carregando...';
    }
}

function setTituloAddLinhaMensal(tipoCiclo, gid) {
    const titulo = document.getElementById(`tituloAddLinhaMensal-${gid}`);
    if (!titulo) return;

    let texto = 'Adicionar aulas';
    const ciclo = (tipoCiclo || '').toLowerCase();
    if (ciclo === 'único' || ciclo === 'unico') {
        texto = 'Adicionar aulas únicas';
    } else if (ciclo === 'anual') {
        const meses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
        ];
        texto = `Adicionar aulas de ${meses[gid - 1] || ''}`;
    } else if (ciclo === 'bimestral') {
        texto = `Adicionar aulas do Bimestre ${gid}`;
    } else if (ciclo === 'trimestral') {
        texto = `Adicionar aulas do Trimestre ${gid}`;
    } else if (ciclo === 'semestral') {
        texto = `Adicionar aulas do Semestre ${gid}`;
    }

    titulo.textContent = texto;
}

function limparSelect(baseId, gid) {
    const sel = document.getElementById(`${baseId}-${gid}`);
    if (sel) sel.innerHTML = '<option value="">Selecione...</option>';
}

function limparSubformularioLinha(gid) {
    const container = document.getElementById(`form-linha-bncc-${gid}`);
    if (!container) return;

    container.classList.add('oculto');
    container.querySelectorAll('input, select, textarea').forEach((el) => {
        if (el instanceof HTMLSelectElement) {
            el.selectedIndex = 0;
        } else {
            el.value = '';
        }
    });

    editandoLinhaId = null;
    const btnSalvar = document.getElementById(`btnSalvarLinha-${gid}`);
    if (btnSalvar) btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar linha';
}

async function carregarSequenciaBNCC(entidade, filtros = {}) {
    const params = new URLSearchParams({ acao: 'bncc', campo: entidade, ...filtros });
    const resp = await fetch(`${API_BASE}?${params.toString()}`);
    const json = await resp.json();
    if (!json.sucesso) {
        throw new Error(json.mensagem || 'Falha ao carregar dados da BNCC.');
    }
    return json.dados || [];
}

function bindSequencialBNCC(gid) {
    const idPrefix = gid ? `-${gid}` : '';
    const etapa = document.getElementById(`etapa-linha${idPrefix}`);
    const ano = document.getElementById(`ano-linha${idPrefix}`);
    const area = document.getElementById(`area-linha${idPrefix}`);
    const componente = document.getElementById(`componente-linha${idPrefix}`);
    const unidade = document.getElementById(`unidadeTematica-linha${idPrefix}`);
    const objeto = document.getElementById(`objetosConhecimento-linha${idPrefix}`);
    const habilidades = document.getElementById(`habilidades-linha${idPrefix}`);

    if (etapa) {
        etapa.addEventListener('change', async function () {
            limparSelect('ano-linha', gid);
            limparSelect('area-linha', gid);
            limparSelect('componente-linha', gid);
            limparSelect('unidadeTematica-linha', gid);
            limparSelect('objetosConhecimento-linha', gid);
            limparSelect('habilidades-linha', gid);

            try {
                const lista = await carregarSequenciaBNCC('anos', { id_etapa: this.value });
                const select = document.getElementById(`ano-linha-${gid}`);
                if (select) {
                    select.innerHTML = '<option value="">Selecione...</option>';
                    lista.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        select.appendChild(opt);
                    });
                    document.getElementById(`grupo-ano-${gid}`)?.classList.remove('oculto');
                }
            } catch (error) {
                mostrarAlerta(error.message, 'danger');
            }
        });
    }

    if (ano) {
        ano.addEventListener('change', async function () {
            limparSelect('area-linha', gid);
            limparSelect('componente-linha', gid);
            limparSelect('unidadeTematica-linha', gid);
            limparSelect('objetosConhecimento-linha', gid);
            limparSelect('habilidades-linha', gid);

            try {
                const lista = await carregarSequenciaBNCC('areas', { id_etapa: etapa?.value });
                const select = document.getElementById(`area-linha-${gid}`);
                if (select) {
                    select.innerHTML = '<option value="">Selecione...</option>';
                    lista.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        select.appendChild(opt);
                    });
                    document.getElementById(`grupo-area-${gid}`)?.classList.remove('oculto');
                }
            } catch (error) {
                mostrarAlerta(error.message, 'danger');
            }
        });
    }

    if (area) {
        area.addEventListener('change', async function () {
            limparSelect('componente-linha', gid);
            limparSelect('unidadeTematica-linha', gid);
            limparSelect('objetosConhecimento-linha', gid);
            limparSelect('habilidades-linha', gid);
            try {
                const lista = await carregarSequenciaBNCC('componentes', { id_area: this.value });
                const select = document.getElementById(`componente-linha-${gid}`);
                if (select) {
                    select.innerHTML = '<option value="">Selecione...</option>';
                    lista.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        select.appendChild(opt);
                    });
                    document.getElementById(`grupo-componente-${gid}`)?.classList.remove('oculto');
                }
            } catch (error) {
                mostrarAlerta(error.message, 'danger');
            }
        });
    }

    if (componente) {
        componente.addEventListener('change', async function () {
            limparSelect('unidadeTematica-linha', gid);
            limparSelect('objetosConhecimento-linha', gid);
            limparSelect('habilidades-linha', gid);
            try {
                const lista = await carregarSequenciaBNCC('unidades_tematicas', { id_componente: this.value });
                const select = document.getElementById(`unidadeTematica-linha-${gid}`);
                if (select) {
                    select.innerHTML = '<option value="">Selecione...</option>';
                    lista.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        select.appendChild(opt);
                    });
                    document.getElementById(`grupo-unidade-${gid}`)?.classList.remove('oculto');
                }
            } catch (error) {
                mostrarAlerta(error.message, 'danger');
            }
        });
    }

    if (unidade) {
        unidade.addEventListener('change', async function () {
            limparSelect('objetosConhecimento-linha', gid);
            limparSelect('habilidades-linha', gid);
            try {
                const lista = await carregarSequenciaBNCC('objetosConhecimento', { id_unidade_tematica: this.value });
                const select = document.getElementById(`objetosConhecimento-linha-${gid}`);
                if (select) {
                    select.innerHTML = '<option value="">Selecione...</option>';
                    lista.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        select.appendChild(opt);
                    });
                    document.getElementById(`grupo-objetos-${gid}`)?.classList.remove('oculto');
                }
            } catch (error) {
                mostrarAlerta(error.message, 'danger');
            }
        });
    }

    if (objeto) {
        objeto.addEventListener('change', async function () {
            limparSelect('habilidades-linha', gid);
            try {
                const lista = await carregarSequenciaBNCC('habilidades', { id_objeto: this.value });
                const select = document.getElementById(`habilidades-linha-${gid}`);
                if (select) {
                    select.innerHTML = '';
                    lista.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        select.appendChild(opt);
                    });
                    document.getElementById(`grupo-habilidades-${gid}`)?.classList.remove('oculto');
                    if (window.Choices) {
                        const inst = window.choicesHabilidadesMap[gid];
                        if (inst) {
                            inst.clearChoices();
                            inst.setChoices(lista.map((item) => ({
                                value: String(item.id),
                                label: item.label,
                            })), 'value', 'label', true);
                        }
                    }
                }
            } catch (error) {
                mostrarAlerta(error.message, 'danger');
            }
        });
    }
}

function serializarLinhasNoForm() {
    const hidden = document.getElementById('linhas-planejamento');
    if (hidden) hidden.value = JSON.stringify(linhasPlanejamento);
}

function renderizarTabelaLinhas(gidNum) {
    const gid = toInt(gidNum);
    const tb = document.getElementById(`tbody-linhas-planejamento-${gid}`);
    if (!tb) return;

    tb.innerHTML = '';

    linhasPlanejamento
        .filter((l) => toInt(l.grupo) === gid)
        .forEach((l, idx) => {
            const tr = document.createElement('tr');
            if (l.id) tr.dataset.id = l.id;
            tr.dataset.etapa = l.etapa;
            tr.dataset.ano = l.ano;
            tr.dataset.area = l.area;
            tr.dataset.componente = l.componenteCurricular;
            tr.dataset.unidade = l.unidadeTematicas;
            tr.dataset.objeto = l.objetosConhecimento;
            tr.dataset.habilidades = Array.isArray(l.habilidades) ? l.habilidades.join(',') : '';
            tr.dataset.conteudos = (l.conteudos || '').replace(/\s+/g, ' ');
            tr.dataset.metodologias = (l.metodologias || '').replace(/\s+/g, ' ');
            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>${getNomeBNCC('etapas', l.etapa)} / ${l.ano || ''}</td>
                <td>${getNomeBNCC('areas', l.area)}</td>
                <td>${getNomeBNCC('componentes', l.componenteCurricular)}</td>
                <td>${getNomeBNCC('unidades_tematicas', l.unidadeTematicas)}</td>
                <td>${getNomeBNCC('objetosConhecimento', l.objetosConhecimento)}</td>
                <td>${(l.habilidades || []).map((h) => getNomeBNCC('habilidades', h)).join(', ')}</td>
                <td class="text-end">
                    <button class="btn-action btn-edit" data-action="editar-linha" data-gid="${gid}" data-index="${idx}"><i class="fas fa-edit"></i></button>
                    <button class="btn-action btn-danger" data-action="excluir-linha" data-gid="${gid}" data-index="${idx}"><i class="fas fa-trash"></i></button>
                </td>`;
            tb.appendChild(tr);
        });

    tb.querySelectorAll('[data-action="editar-linha"]').forEach((btn) => {
        btn.addEventListener('click', () => editarLinha(Number(btn.dataset.index), Number(btn.dataset.gid)));
    });
    tb.querySelectorAll('[data-action="excluir-linha"]').forEach((btn) => {
        btn.addEventListener('click', () => excluirLinha(Number(btn.dataset.index), Number(btn.dataset.gid)));
    });
}

function adicionarOuEditarLinha(gid) {
    const grupo = toInt(gid);
    const form = document.getElementById(`form-linha-bncc-${grupo}`);
    if (!form) return;

    const etapa = document.getElementById(`etapa-linha-${grupo}`)?.value;
    const ano = document.getElementById(`ano-linha-${grupo}`)?.value;
    const area = document.getElementById(`area-linha-${grupo}`)?.value;
    const componente = document.getElementById(`componente-linha-${grupo}`)?.value;
    const unidade = document.getElementById(`unidadeTematica-linha-${grupo}`)?.value;
    const objeto = document.getElementById(`objetosConhecimento-linha-${grupo}`)?.value;
    const habilidadesSel = document.getElementById(`habilidades-linha-${grupo}`);
    const conteudos = window.jQuery ? window.jQuery(`#conteudos-linha-${grupo}`).summernote('code') : document.getElementById(`conteudos-linha-${grupo}`)?.value;
    const metodologias = window.jQuery ? window.jQuery(`#metodologias-linha-${grupo}`).summernote('code') : document.getElementById(`metodologias-linha-${grupo}`)?.value;

    if (!etapa || !ano) {
        mostrarAlerta('Selecione a etapa e o ano para continuar.', 'warning');
        return;
    }

    const habilidades = habilidadesSel ? Array.from(habilidadesSel.selectedOptions).map((opt) => opt.value) : [];

    const novaLinha = {
        id: null,
        grupo: grupo,
        etapa,
        ano,
        area,
        componenteCurricular: componente,
        unidadeTematicas: unidade,
        objetosConhecimento: objeto,
        habilidades,
        conteudos: conteudos || '',
        metodologias: metodologias || '',
    };

    if (editandoLinhaId !== null) {
        const alvo = linhasPlanejamento[editandoLinhaId];
        if (alvo) {
            linhasPlanejamento[editandoLinhaId] = { ...alvo, ...novaLinha, id: alvo.id };
        }
    } else {
        linhasPlanejamento.push(novaLinha);
    }

    serializarLinhasNoForm();
    renderizarTabelaLinhas(grupo);
    limparSubformularioLinha(grupo);
}

function editarLinha(idx, gid) {
    const linha = linhasPlanejamento[idx];
    if (!linha) return;

    const grupo = toInt(gid);
    const form = document.getElementById(`form-linha-bncc-${grupo}`);
    if (!form) return;

    editandoLinhaId = idx;
    setTituloSubtituloAddLinhaMensal('editar', grupo);
    form.classList.remove('oculto');

    document.getElementById(`etapa-linha-${grupo}`)?.value = linha.etapa || '';
    document.getElementById(`ano-linha-${grupo}`)?.value = linha.ano || '';
    document.getElementById(`area-linha-${grupo}`)?.value = linha.area || '';
    document.getElementById(`componente-linha-${grupo}`)?.value = linha.componenteCurricular || '';
    document.getElementById(`unidadeTematica-linha-${grupo}`)?.value = linha.unidadeTematicas || '';
    document.getElementById(`objetosConhecimento-linha-${grupo}`)?.value = linha.objetosConhecimento || '';

    const habSelect = document.getElementById(`habilidades-linha-${grupo}`);
    if (habSelect) {
        Array.from(habSelect.options).forEach((opt) => {
            opt.selected = linha.habilidades?.includes(opt.value) ?? false;
        });
        const inst = window.choicesHabilidadesMap[grupo];
        if (inst) {
            inst.removeActiveItems();
            inst.setChoiceByValue((linha.habilidades || []).map(String));
        }
    }

    if (window.jQuery) {
        window.jQuery(`#conteudos-linha-${grupo}`).summernote('code', linha.conteudos || '');
        window.jQuery(`#metodologias-linha-${grupo}`).summernote('code', linha.metodologias || '');
    } else {
        const conteudos = document.getElementById(`conteudos-linha-${grupo}`);
        const metodologias = document.getElementById(`metodologias-linha-${grupo}`);
        if (conteudos) conteudos.value = linha.conteudos || '';
        if (metodologias) metodologias.value = linha.metodologias || '';
    }
}

function excluirLinha(idx, gid) {
    const linha = linhasPlanejamento[idx];
    if (!linha) return;

    linhasPlanejamento.splice(idx, 1);
    if (linha.id) {
        linhasPlanejamentoParaExcluir.push(linha.id);
    }

    serializarLinhasNoForm();
    renderizarTabelaLinhas(gid);
}

function gerarBlocos(quantidade, dadosIniciais = []) {
    const container = document.getElementById('blocos-planejamento');
    if (!container) return;

    container.innerHTML = '';

    const dadosMapeados = Array.isArray(dadosIniciais)
        ? dadosIniciais.filter((d) => d && d.grupo != null).reduce((acc, d) => {
              const g = toInt(d.grupo);
              if (g > 0) acc[g] = d;
              return acc;
          }, {})
        : {};

    for (let gid = 1; gid <= quantidade; gid++) {
        const bloco = gerarHtmlBloco(gid);
        if (!bloco) continue;

        container.appendChild(bloco);
        bindSequencialBNCC(gid);

        if (window.jQuery) {
            const conteudoEl = document.getElementById(`conteudos-linha-${gid}`);
            const metodologiaEl = document.getElementById(`metodologias-linha-${gid}`);
            if (conteudoEl && window.jQuery(`#${conteudoEl.id}`).next('.note-editor').length === 0) {
                window.jQuery(`#${conteudoEl.id}`).summernote({ height: 140 });
            }
            if (metodologiaEl && window.jQuery(`#${metodologiaEl.id}`).next('.note-editor').length === 0) {
                window.jQuery(`#${metodologiaEl.id}`).summernote({ height: 140 });
            }
        }

        if (window.Choices) {
            const habSel = document.getElementById(`habilidades-linha-${gid}`);
            if (habSel && !window.choicesHabilidadesMap[gid]) {
                window.choicesHabilidadesMap[gid] = new Choices(habSel, { removeItemButton: true });
            }
        }

        const tipoCiclo = document.getElementById('tempo');
        if (tipoCiclo) {
            const texto = tipoCiclo.options[tipoCiclo.selectedIndex]?.textContent || '';
            setTituloAddLinhaMensal(texto, gid);
        }

        renderizarTabelaLinhas(gid);

        document.getElementById(`btnAdicionarLinha-${gid}`)?.addEventListener('click', (event) => {
            event.preventDefault();
            setTituloSubtituloAddLinhaMensal('criar', gid);
            const form = document.getElementById(`form-linha-bncc-${gid}`);
            if (form) {
                form.classList.remove('oculto');
                rolarEDestacarPrimeiroDestaque();
            }
        });

        document.getElementById(`btnSalvarLinha-${gid}`)?.addEventListener('click', () => adicionarOuEditarLinha(gid));
        document.getElementById(`btnCancelarLinha-${gid}`)?.addEventListener('click', () => limparSubformularioLinha(gid));
    }
}

async function listarPlanejamentos(termoPesquisa = '') {
    const tbody = document.getElementById('tbody-lista-planejamentos');
    if (!tbody) return;

    try {
        const resp = await fetch(`${API_BASE}?acao=buscar_todos&pesquisa=${encodeURIComponent(termoPesquisa)}`);
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Erro ao listar planejamentos');
        }

        tbody.innerHTML = '';
        const lista = json.data || [];
        lista.forEach((plano) => {
            const tr = document.createElement('tr');
            tr.dataset.id = plano.id;
            const materiaNome = materiasLookup[String(plano.materia)] || materiasLookup[String(plano.materia_id)] || plano.materia || '';
            tr.innerHTML = `
                <td>${plano.id}</td>
                <td>${plano.nome ?? ''}</td>
                <td>${materiaNome}</td>
                <td>${plano.anosDoPlano ?? ''}</td>
                <td>${plano.periodo ?? ''}</td>
                <td class="text-end">
                    <button class="btn-action btn-view" data-action="visualizar-plano" data-id="${plano.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn-action btn-edit" data-action="editar-plano" data-id="${plano.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn-action btn-delete" data-action="excluir-plano" data-id="${plano.id}"><i class="fas fa-trash"></i></button>
                </td>`;
            tbody.appendChild(tr);
        });

        if (!lista.length) {
            const vazio = document.createElement('tr');
            vazio.innerHTML = '<td colspan="6" class="text-center text-muted">Nenhum planejamento cadastrado.</td>';
            tbody.appendChild(vazio);
        }

        tbody.querySelectorAll('[data-action="editar-plano"]').forEach((btn) => {
            btn.addEventListener('click', () => editarPlanejamentoMensal(btn.dataset.id));
        });
        tbody.querySelectorAll('[data-action="excluir-plano"]').forEach((btn) => {
            btn.addEventListener('click', () => abrirModalExcluirPlanejamento(btn.dataset.id));
        });
        tbody.querySelectorAll('[data-action="visualizar-plano"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.location.href = `/planejamentos/${btn.dataset.id}`;
            });
        });

        const contador = document.getElementById('contadorRegistrosPlanejamento');
        if (contador) {
            contador.innerHTML = `Exibindo <strong>${lista.length}</strong> registro(s)`;
        }
    } catch (error) {
        console.error(error);
        mostrarAlerta('Falha ao carregar a lista de planejamentos.', 'danger');
    }
}

async function carregarMateriasDoProfessor() {
    const select = document.getElementById('materia');
    if (!select) return;

    try {
        const resp = await fetch(`${API_BASE}?acao=materias_do_professor`);
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Falha ao carregar matérias.');
        }
        const lista = json.materias || [];
        select.innerHTML = '<option value="" disabled selected>Selecione a matéria</option>';
        materiasLookup = {};
        lista.forEach((item) => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.label;
            materiasLookup[String(item.id)] = item.label;
            select.appendChild(opt);
        });
        select.dataset.populado = 'true';
        select.dispatchEvent(new Event('carregado'));
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

function formatarDataHora(valor) {
    if (!valor) return '-';
    const data = new Date(valor);
    if (Number.isNaN(data.getTime())) {
        return valor;
    }
    return data.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
}

function renderizarReservas(reservas, podeAprovar = false) {
    const tbody = document.getElementById('tbodyReservasPlanejamento');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!reservas.length) {
        const vazio = document.createElement('tr');
        vazio.className = 'estado-vazio';
        vazio.innerHTML = '<td colspan="7" class="text-center text-muted">Nenhuma reserva registrada para este planejamento.</td>';
        tbody.appendChild(vazio);
        return;
    }

    reservas.forEach((reserva) => {
        const tr = document.createElement('tr');
        tr.dataset.id = reserva.id;
        tr.innerHTML = `
            <td>${reserva.sala}</td>
            <td>${formatarDataHora(reserva.inicio)}</td>
            <td>${formatarDataHora(reserva.fim)}</td>
            <td class="text-capitalize">${reserva.status}</td>
            <td>${reserva.solicitante || '-'}</td>
            <td>${reserva.aprovador ? `${reserva.aprovador}${reserva.aprovado_em ? ` (${formatarDataHora(reserva.aprovado_em)})` : ''}` : '-'}</td>
            <td class="text-end"></td>`;

        const actionsCell = tr.lastElementChild;
        const buttons = [];

        if ((reserva.pode_aprovar || podeAprovar) && reserva.status === 'pending') {
            buttons.push(`<button class="btn-action btn-success" data-action="aprovar-reserva" data-id="${reserva.id}" data-decisao="aprovar" title="Aprovar"><i class="fas fa-check"></i></button>`);
            buttons.push(`<button class="btn-action btn-danger" data-action="rejeitar-reserva" data-id="${reserva.id}" data-decisao="rejeitar" title="Rejeitar"><i class="fas fa-times"></i></button>`);
        }

        if (reserva.pode_cancelar || (reserva.status !== 'cancelled' && reserva.status !== 'rejected' && podeAprovar)) {
            buttons.push(`<button class="btn-action btn-cancelar" data-action="cancelar-reserva" data-id="${reserva.id}" title="Cancelar"><i class="fas fa-ban"></i></button>`);
        }

        if (buttons.length === 0) {
            actionsCell.innerHTML = '<span class="text-muted">-</span>';
        } else {
            actionsCell.innerHTML = buttons.join('');
        }

        tbody.appendChild(tr);
    });

    tbody.querySelectorAll('[data-action="aprovar-reserva"]').forEach((btn) => {
        btn.addEventListener('click', () => atualizarStatusReserva(Number(btn.dataset.id), 'aprovar'));
    });
    tbody.querySelectorAll('[data-action="rejeitar-reserva"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const comentario = prompt('Informe o motivo da rejeição (opcional):');
            atualizarStatusReserva(Number(btn.dataset.id), 'rejeitar', comentario || undefined);
        });
    });
    tbody.querySelectorAll('[data-action="cancelar-reserva"]').forEach((btn) => {
        btn.addEventListener('click', () => cancelarReserva(Number(btn.dataset.id)));
    });
}

async function carregarSalas() {
    const select = document.getElementById('reserva-sala');
    if (!select) return;

    try {
        const resp = await fetch(`${API_BASE}?acao=listar_salas`);
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Falha ao carregar salas.');
        }

        const salas = json.salas || [];
        podeAprovarReservas = json.pode_aprovar || false;

        select.innerHTML = '<option value="" selected>Selecione uma sala</option>';
        salas.forEach((sala) => {
            const opt = document.createElement('option');
            opt.value = sala.id;
            opt.textContent = sala.nome;
            select.appendChild(opt);
        });
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

async function listarReservasPlanejamento(filtros = {}) {
    const tabela = document.getElementById('tabelaReservasPlanejamento');
    if (!tabela) return;

    if (!currentPlanningId) {
        reservasPlanejamento = [];
        renderizarReservas(reservasPlanejamento, podeAprovarReservas);
        document.getElementById('btnReservarSala')?.setAttribute('disabled', 'disabled');
        return;
    }

    document.getElementById('btnReservarSala')?.removeAttribute('disabled');

    try {
        const params = new URLSearchParams({ acao: 'listar_reservas', planning_id: String(currentPlanningId) });
        if (filtros.inicio) params.append('inicio', filtros.inicio);
        if (filtros.fim) params.append('fim', filtros.fim);
        if (filtros.room_id) params.append('room_id', String(filtros.room_id));

        const resp = await fetch(`${API_BASE}?${params.toString()}`);
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Falha ao listar reservas.');
        }

        reservasPlanejamento = json.reservas || [];
        podeAprovarReservas = json.pode_aprovar || podeAprovarReservas;
        renderizarReservas(reservasPlanejamento, podeAprovarReservas);
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

async function reservarSalaAtual() {
    if (!currentPlanningId) {
        mostrarAlerta('Salve o planejamento antes de reservar uma sala.', 'warning');
        return;
    }

    if (!podeReservar) {
        mostrarAlerta('Você não possui permissão para reservar salas.', 'warning');
        return;
    }

    const salaId = document.getElementById('reserva-sala')?.value;
    const inicio = document.getElementById('reserva-inicio')?.value;
    const fim = document.getElementById('reserva-fim')?.value;
    const observacoes = document.getElementById('reserva-observacoes')?.value.trim();

    if (!salaId || !inicio || !fim) {
        mostrarAlerta('Preencha sala, início e fim para solicitar a reserva.', 'warning');
        return;
    }

    const fd = new URLSearchParams({
        acao: 'reservar_sala',
        room_id: salaId,
        planning_id: String(currentPlanningId),
        inicio,
        fim,
    });
    if (observacoes) {
        fd.append('observacoes', observacoes);
    }

    try {
        const resp = await fetch(API_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: fd.toString(),
        });
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Falha ao registrar reserva.');
        }

        mostrarAlerta(json.mensagem || 'Reserva registrada com sucesso.', 'success');
        listarReservasPlanejamento();
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

async function atualizarStatusReserva(reservaId, decisao, comentario) {
    try {
        const fd = new URLSearchParams({
            acao: 'aprovar_reserva',
            reserva_id: String(reservaId),
            decisao,
        });
        if (comentario) {
            fd.append('comentario', comentario);
        }

        const resp = await fetch(API_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: fd.toString(),
        });
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Falha ao atualizar reserva.');
        }

        mostrarAlerta(json.mensagem || 'Reserva atualizada com sucesso.', 'success');
        listarReservasPlanejamento();
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

async function cancelarReserva(reservaId) {
    try {
        const fd = new URLSearchParams({
            acao: 'cancelar_reserva',
            reserva_id: String(reservaId),
        });

        const resp = await fetch(API_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: fd.toString(),
        });
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Falha ao cancelar reserva.');
        }

        mostrarAlerta(json.mensagem || 'Reserva cancelada com sucesso.', 'success');
        listarReservasPlanejamento();
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

function abrirModalExcluirPlanejamento(id) {
    showModalGeral({
        titulo: 'Confirmação de exclusão',
        corpo: '<p>Deseja realmente excluir este planejamento?</p>',
        textoConfirmar: 'Excluir',
        onConfirm: () => confirmarExclusaoAJAXPlanejamentoMensal(id),
    });
}

async function confirmarExclusaoAJAXPlanejamentoMensal(id) {
    try {
        const resp = await fetch(API_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ acao: 'excluir', id: String(id) }).toString(),
        });
        const json = await resp.json();
        if (json.sucesso) {
            mostrarAlerta(json.mensagem || 'Planejamento excluído com sucesso.', 'success');
            listarPlanejamentos();
        } else {
            mostrarAlerta(json.mensagem || 'Não foi possível excluir o planejamento.', 'warning');
        }
    } catch (error) {
        console.error(error);
        mostrarAlerta('Falha ao excluir planejamento.', 'danger');
    }
}

async function salvarCadastroPlanejamentoMensal(event) {
    event.preventDefault();
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (!linhasPlanejamento.length) {
        mostrarAlerta('Adicione ao menos uma linha de BNCC antes de salvar.', 'warning');
        return;
    }

    serializarLinhasNoForm();

    const fd = new FormData(form);
    fd.set('linhas_serializadas', JSON.stringify(linhasPlanejamento));
    fd.set('anos_plano', (choicesAnos?.getValue(true) || []).join(','));
    if (window.jQuery) {
        fd.set('objetivo_geral', window.jQuery('#objetivo_geral').summernote('code'));
        fd.set('objetivo_especifico', window.jQuery('#objetivo_especifico').summernote('code'));
    }

    const acao = fd.get('id-planejamento-mensal') ? 'editar' : 'criar';
    fd.append('acao', acao);

    try {
        const resp = await fetch(API_BASE, { method: 'POST', body: fd });
        const json = await resp.json();
        if (!json.sucesso) {
            mostrarAlerta(json.mensagem || 'Falha ao salvar.', 'warning');
            return;
        }

        mostrarAlerta(json.mensagem || 'Planejamento salvo com sucesso.', 'success');
        listarPlanejamentos();
        document.getElementById('crudPlanejamentoMensalContainer')?.classList.add('oculto');
        form.reset();
        linhasPlanejamento = [];
        serializarLinhasNoForm();
    } catch (error) {
        console.error(error);
        mostrarAlerta('Erro de comunicação com o servidor.', 'danger');
    }
}

async function editarPlanejamentoMensal(id) {
    const formWrapper = document.getElementById('formPlanejamentoWrapper');
    if (formWrapper) {
        formWrapper.classList.remove('oculto');
    }

    const inputId = document.getElementById('id-planejamento-mensal');
    if (inputId) inputId.value = id;
    setTituloSubtituloCRUD('editar');
    currentPlanningId = Number(id);

    try {
        const resp = await fetch(`${API_BASE}?acao=buscar&id=${id}`);
        const json = await resp.json();
        if (!json.sucesso) {
            mostrarAlerta(json.mensagem || 'Falha ao carregar planejamento.', 'warning');
            return;
        }

        const cab = json.cabecalho || {};
        document.getElementById('crudPlanejamentoMensalContainer')?.classList.remove('oculto');
        document.getElementById('nome-plano-mensal').value = cab['nome-plano-mensal'] || '';
        document.getElementById('periodo_realizacao').value = cab['periodo_realizacao'] || '';
        document.getElementById('numero_aulas_semanais').value = cab['numero_aulas_semanais'] || '';

        const selTempo = document.getElementById('tempo');
        if (selTempo) {
            const cicloId = String(cab['tempo'] || '');
            let opt = selTempo.querySelector(`option[value="${cicloId}"]`);
            if (!opt) {
                opt = Array.from(selTempo.options).find((o) => o.textContent.trim().toLowerCase() === cicloId.toLowerCase());
            }
            if (opt) {
                opt.selected = true;
            }
            selTempo.disabled = true;
        }

        const selMat = document.getElementById('materia');
        const definirMat = () => {
            selMat.value = cab['materia'] || '';
            selMat.removeEventListener('carregado', definirMat);
        };
        if (selMat.dataset.populado === 'true') {
            definirMat();
        } else {
            selMat.addEventListener('carregado', definirMat);
        }

        if (choicesAnos) {
            choicesAnos.removeActiveItems();
            const anos = String(cab['anos_plano'] || '')
                .split(',')
                .map((a) => a.trim())
                .filter(Boolean);
            choicesAnos.setChoiceByValue(anos);
        }

        if (window.jQuery) {
            window.jQuery('#objetivo_geral').summernote('code', cab['objetivo_geral'] || '');
            window.jQuery('#objetivo_especifico').summernote('code', cab['objetivo_especifico'] || '');
        }

        linhasPlanejamento = Array.isArray(json.linhas) ? json.linhas.slice() : [];
        serializarLinhasNoForm();

        const mapa = { unico: 1, mensal: 12, anual: 12, semestral: 2, trimestral: 4, bimestral: 6 };
        const txt = selTempo?.selectedOptions[0]?.textContent.trim().toLowerCase();
        const qtd = mapa[txt || ''] || 0;
        gerarBlocos(qtd, linhasPlanejamento);
        await carregarSalas();
        await listarReservasPlanejamento();
    } catch (error) {
        console.error(error);
        mostrarAlerta('Falha ao carregar planejamento para edição.', 'danger');
    }
}

async function carregarCiclos() {
    const selectTempo = document.getElementById('tempo');
    if (!selectTempo) return;

    try {
        const resp = await fetch(`${API_BASE}?acao=listar_ciclos`);
        const json = await resp.json();
        if (!json.sucesso) {
            throw new Error(json.mensagem || 'Erro ao carregar tipos de ciclo.');
        }
        const lista = json.ciclos || [];
        selectTempo.innerHTML = '<option value="" disabled selected>Selecione o tipo de ciclo</option>';
        lista.forEach((c) => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nome;
            selectTempo.appendChild(opt);
        });
    } catch (error) {
        console.error(error);
        mostrarAlerta(error.message, 'danger');
    }
}

async function inicializarPlanejamentoMensal() {
    if (!planningRoot) return;

    await carregarBnccMaps();
    obterTemplateLinha();

    const form = document.getElementById('crudPlanejamentoMensalForm');
    if (form) {
        form.addEventListener('submit', salvarCadastroPlanejamentoMensal);
    }

    const btnNovo = document.getElementById('btnNovoPlanejamento');
    if (btnNovo) {
        btnNovo.addEventListener('click', (event) => {
            event.preventDefault();
            setTituloSubtituloCRUD('criar');
            document.getElementById('crudPlanejamentoMensalContainer')?.classList.remove('oculto');
            const selectTempo = document.getElementById('tempo');
            if (selectTempo?.value) {
                document.getElementById('formPlanejamentoWrapper')?.classList.remove('oculto');
            }
            currentPlanningId = null;
            listarReservasPlanejamento();
        });
    }

    const btnCancelarTudo = document.getElementById('btnCancelarTudo');
    if (btnCancelarTudo) {
        btnCancelarTudo.addEventListener('click', () => {
            document.getElementById('crudPlanejamentoMensalContainer')?.classList.add('oculto');
            form?.reset();
            linhasPlanejamento = [];
            serializarLinhasNoForm();
        });
    }

    const selectTempo = document.getElementById('tempo');
    const formWrapper = document.getElementById('formPlanejamentoWrapper');
    if (selectTempo && formWrapper) {
        selectTempo.addEventListener('change', () => {
            if (selectTempo.value) {
                formWrapper.classList.remove('oculto');
                const mapa = { unico: 1, mensal: 12, anual: 12, semestral: 2, trimestral: 4, bimestral: 6 };
                const txt = selectTempo.options[selectTempo.selectedIndex]?.textContent.trim().toLowerCase();
                const qtd = mapa[txt || ''] || 0;
                gerarBlocos(qtd);
            } else {
                formWrapper.classList.add('oculto');
            }
        });
    }

    const filtroForm = document.getElementById('filtroPlanejamentosMensais');
    if (filtroForm) {
        filtroForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const termo = document.getElementById('pesquisaPlanejamentosMensais')?.value || '';
            listarPlanejamentos(termo.trim());
        });
    }

    document.getElementById('btnLimparFiltroPlanejamento')?.addEventListener('click', () => {
        document.getElementById('pesquisaPlanejamentosMensais').value = '';
        listarPlanejamentos('');
    });

    const btnReservar = document.getElementById('btnReservarSala');
    if (btnReservar) {
        btnReservar.addEventListener('click', reservarSalaAtual);
    }

    const btnDisponibilidade = document.getElementById('btnVerDisponibilidade');
    if (btnDisponibilidade) {
        btnDisponibilidade.addEventListener('click', () => {
            const inicio = document.getElementById('reserva-inicio')?.value || undefined;
            const fim = document.getElementById('reserva-fim')?.value || undefined;
            listarReservasPlanejamento({ inicio, fim });
        });
    }

    const anosSelect = document.getElementById('anos-plano');
    if (window.Choices && anosSelect) {
        choicesAnos = new Choices(anosSelect, { removeItemButton: true });
    }

    if (window.jQuery) {
        window.jQuery('#objetivo_geral, #objetivo_especifico').summernote({ height: 140 });
    }

    await carregarCiclos();
    await carregarMateriasDoProfessor();
    await carregarSalas();
    await listarPlanejamentos();
    await listarReservasPlanejamento();
}

document.addEventListener('DOMContentLoaded', () => {
    inicializarPlanejamentoMensal().catch((error) => {
        console.error(error);
        mostrarAlerta('Falha ao inicializar o planejador.', 'danger');
    });
});

