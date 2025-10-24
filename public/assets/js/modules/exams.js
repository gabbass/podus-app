import { mostrarAlerta } from './menu.js';

const API_BASE = '/api/exams.php';
const SCAN_API = '/api/exam-scans.php';

let modoCRUD = 'criar';
const dom = (id) => document.getElementById(id);
let choicesTurma;
let choicesMateria;
let choicesQuestoes;
let scanPollTimer = null;
const scanDom = {
    panel: null,
    form: null,
    examId: null,
    examTitle: null,
    matricula: null,
    file: null,
    info: null,
    loading: null,
    progress: null,
    progressContent: null,
    history: null,
    historyList: null,
    subtitle: null,
    submit: null,
    cancel: null,
};
let currentScanId = null;

function showModal({ titulo = '', corpo = '', textoConfirmar = 'OK', onConfirm }) {
    const modalElement = dom('modalGeral');
    if (!modalElement) {
        alert(corpo || titulo);
        return;
    }

    dom('modalGeralLabel').textContent = titulo;
    dom('modalGeralBody').innerHTML = corpo;
    const btn = dom('modalGeralConfirmar');
    btn.textContent = textoConfirmar;
    btn.onclick = () => {
        const instance = bootstrap.Modal.getInstance(modalElement);
        if (instance) {
            instance.hide();
        }
        onConfirm?.();
    };
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const data = await response.json();
    return data;
}

async function carregarDadosIniciais() {
    const [turmas, materias] = await Promise.all([
        fetchJson(`${API_BASE}?acao=listarTurmas`),
        fetchJson(`${API_BASE}?acao=listarMaterias`),
    ]);

    if (turmas?.sucesso) {
        const select = dom('turma');
        if (select) {
            turmas.turmas.forEach((turma) => {
                const option = document.createElement('option');
                option.value = turma.nome;
                option.textContent = turma.nome;
                select.append(option);
            });
        }
    }

    if (materias?.sucesso) {
        const select = dom('materia');
        if (select) {
            materias.materias.forEach((nome) => {
                const option = document.createElement('option');
                option.value = nome;
                option.textContent = nome;
                select.append(option);
            });
        }
    }

    const ChoicesConstructor = window.Choices;
    if (ChoicesConstructor) {
        const turmaSelect = dom('turma');
        const materiaSelect = dom('materia');
        if (turmaSelect) {
            choicesTurma = new ChoicesConstructor(turmaSelect, {
                searchEnabled: true,
                placeholderValue: 'Selecione a turma',
            });
        }
        if (materiaSelect) {
            choicesMateria = new ChoicesConstructor(materiaSelect, {
                searchEnabled: true,
                placeholderValue: 'Selecione a matéria',
            });
        }
    }
}

async function carregarQuestoes() {
    const materia = dom('materia')?.value?.trim();
    const filtro = materia ? `&materia=${encodeURIComponent(materia)}` : '';
    const dados = await fetchJson(`${API_BASE}?acao=listarQuestoes${filtro}`);
    if (!dados?.sucesso) {
        showModal({ corpo: dados?.mensagem || 'Não foi possível carregar as questões.' });
        return;
    }

    const select = dom('lista_quest');
    if (!select) {
        return;
    }

    const lista = Array.isArray(dados.questoes)
        ? dados.questoes.map((questao) => ({
              value: String(questao.id),
              label: `${questao.id} – ${questao.questao?.substring(0, 40) ?? ''}…`,
          }))
        : [];

    const ChoicesConstructor = window.Choices;
    if (!choicesQuestoes && ChoicesConstructor) {
        choicesQuestoes = new ChoicesConstructor(select, {
            removeItemButton: true,
            placeholderValue: 'Selecione as questões',
            choices: lista,
        });
        return;
    }

    if (choicesQuestoes) {
        choicesQuestoes.clearChoices();
        choicesQuestoes.setChoices(lista, 'value', 'label', true);
    }
}

async function openProva(id, modo) {
    modoCRUD = modo;
    dom('boxCrudProva')?.classList.remove('oculto');
    resetFormProva();
    updateHeaderProva();

    await carregarQuestoes();

    if (id) {
        toggleCrudLoading(true);
        try {
            const dados = await fetchJson(`${API_BASE}?acao=buscar&id=${id}`);
            if (!dados?.sucesso) {
                showModal({ corpo: dados?.mensagem || 'Não foi possível carregar a prova.' });
                return;
            }

            dom('idProva').value = dados.dado.id;
            choicesTurma?.setChoiceByValue(dados.dado.turma);
            choicesMateria?.setChoiceByValue(dados.dado.materia);
            dom('escola').value = dados.dado.escola ?? '';

            const selecionadas = (dados.dado.lista_quest || '')
                .toString()
                .split(',')
                .map((valor) => valor.trim())
                .filter(Boolean);

            choicesQuestoes?.removeActiveItems();
            selecionadas.forEach((valor) => choicesQuestoes?.setChoiceByValue(valor));
        } finally {
            toggleCrudLoading(false);
        }
    }

    if (modo === 'visualizar') {
        lockFormProva();
    } else {
        unlockFormProva();
    }
}

function lockFormProva() {
    const form = dom('formProvas');
    if (!form) {
        return;
    }

    [...form.elements].forEach((element) => {
        element.disabled = true;
    });
    const salvar = dom('btnSalvarProva');
    if (salvar) {
        salvar.style.display = 'none';
    }
}

function unlockFormProva() {
    const form = dom('formProvas');
    if (!form) {
        return;
    }

    [...form.elements].forEach((element) => {
        element.disabled = false;
    });
    const salvar = dom('btnSalvarProva');
    if (salvar) {
        salvar.style.display = '';
    }
}

function resetFormProva() {
    dom('formProvas')?.reset();
    if (choicesTurma) {
        choicesTurma.removeActiveItems();
    }
    if (choicesMateria) {
        choicesMateria.removeActiveItems();
    }
    if (choicesQuestoes) {
        choicesQuestoes.removeActiveItems();
    }
    const id = dom('idProva');
    if (id) {
        id.value = '';
    }
}

function updateHeaderProva() {
    const titulo = dom('tituloCrudProvas');
    if (titulo) {
        titulo.textContent =
            modoCRUD === 'editar'
                ? 'Editar provas'
                : modoCRUD === 'visualizar'
                ? 'Visualizar provas'
                : 'Criar provas';
    }

    const subtitulo = dom('subtituloCrudProvas');
    if (subtitulo) {
        subtitulo.textContent =
            modoCRUD === 'criar' ? 'Criar' : modoCRUD === 'editar' ? 'Editar' : 'Visualizar';
    }

    const botaoNovo = dom('btnNovaProva');
    if (botaoNovo) {
        botaoNovo.classList.toggle('oculto', modoCRUD !== 'criar');
    }
}

async function listarProvas() {
    toggleCrudLoading(true);
    try {
        const dados = await fetchJson(`${API_BASE}?acao=listar`);
        if (!dados?.sucesso) {
            showModal({ corpo: dados?.mensagem || 'Não foi possível listar as provas.' });
            return;
        }

        const filtro = dom('filtroTextoProvas')?.value?.trim().toLowerCase() || '';
        const provas = Array.isArray(dados.provas) ? dados.provas : [];
        const filtradas = filtro
            ? provas.filter((prova) => {
                  const campos = [prova.id, prova.turma, prova.materia, prova.escola, prova.lista_quest]
                      .map((valor) => (valor ?? '').toString().toLowerCase());
                  return campos.some((texto) => texto.includes(filtro));
              })
            : provas;

        const tbody = dom('tbodyProvas');
        if (tbody) {
            tbody.innerHTML = '';
            filtradas.forEach((prova) => {
                const questoes = (prova.lista_quest || '')
                    .toString()
                    .split(',')
                    .map((valor) => valor.trim())
                    .filter(Boolean);

                const tr = document.createElement('tr');
                tr.dataset.id = prova.id;
                tr.innerHTML = `
                    <td>${prova.id}</td>
                    <td>${prova.turma ?? ''}</td>
                    <td>${prova.materia ?? ''}</td>
                    <td>${questoes.length}</td>
                    <td>${prova.escola ?? ''}</td>
                    <td>
                        <button class="btn-action btn-edit" data-action="editar-prova" data-id="${prova.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn-action btn-view" data-action="visualizar-prova" data-id="${prova.id}"><i class="fas fa-eye"></i></button>
                        <button class="btn-action btn-delete" data-action="excluir-prova" data-id="${prova.id}"><i class="fas fa-trash-alt"></i></button>
                        <button class="btn-action btn-scan" data-action="scan-prova" data-id="${prova.id}"><i class="fas fa-camera"></i></button>
                    </td>`;
                tbody.appendChild(tr);

                tr.querySelector('[data-action="editar-prova"]')?.addEventListener('click', () => openProva(prova.id, 'editar'));
                tr.querySelector('[data-action="visualizar-prova"]')?.addEventListener('click', () => openProva(prova.id, 'visualizar'));
                tr.querySelector('[data-action="excluir-prova"]')?.addEventListener('click', () => excluirProva(prova.id));
                tr.querySelector('[data-action="scan-prova"]')?.addEventListener('click', () => openScanPanelForExam(prova));
            });
        }

        const total = dom('totalRegistrosProvas');
        if (total) {
            total.textContent = filtradas.length;
        }
    } catch (error) {
        console.error('Erro ao listar provas', error);
        showModal({ corpo: 'Falha ao carregar a lista de provas.' });
    } finally {
        toggleCrudLoading(false);
    }
}

function excluirProva(id) {
    showModal({
        titulo: 'Confirmar',
        corpo: 'Excluir prova?',
        textoConfirmar: 'Excluir',
        onConfirm: async () => {
            const fd = new FormData();
            fd.append('acao', 'excluir');
            fd.append('id', id);
            const resposta = await fetchJson(API_BASE, { method: 'POST', body: fd });
            if (resposta?.sucesso) {
                listarProvas();
                mostrarAlerta('Prova excluída com sucesso.', 'success');
            } else {
                showModal({ corpo: resposta?.mensagem || 'Não foi possível excluir a prova.' });
            }
        },
    });
}

function toggleCrudLoading(on) {
    dom('crudLoading')?.classList.toggle('oculto', !on);
}

function openScanPanelForExam(prova) {
    if (!scanDom.panel) {
        return;
    }
    scanDom.panel.classList.remove('oculto');
    if (scanDom.examId) {
        scanDom.examId.value = prova.id;
    }
    if (scanDom.examTitle) {
        scanDom.examTitle.value = `${prova.materia ?? ''} - ${prova.turma ?? ''}`.trim();
    }
    if (scanDom.matricula) {
        scanDom.matricula.value = '';
    }
    if (scanDom.file) {
        scanDom.file.value = '';
    }
    if (scanDom.subtitle) {
        scanDom.subtitle.textContent = `Turma ${prova.turma ?? ''} • ${prova.materia ?? ''}`;
    }
    if (scanDom.info) {
        scanDom.info.textContent = 'Informe a matrícula do aluno para validar as tentativas disponíveis.';
    }
    if (scanDom.progressContent) {
        scanDom.progressContent.innerHTML = '';
    }
    scanDom.progress?.classList.add('oculto');
    scanDom.history?.classList.add('oculto');
    if (scanDom.historyList) {
        scanDom.historyList.innerHTML = '';
    }
    if (scanDom.submit) {
        scanDom.submit.disabled = false;
    }
    stopScanPolling();
    currentScanId = null;
}

function closeScanPanel() {
    scanDom.panel?.classList.add('oculto');
    stopScanPolling();
    currentScanId = null;
}

function toggleScanLoading(on) {
    scanDom.loading?.classList.toggle('oculto', !on);
}

function stopScanPolling() {
    if (scanPollTimer) {
        clearInterval(scanPollTimer);
        scanPollTimer = null;
    }
}

async function fetchScanAttempts(examId, matricula) {
    if (!scanDom.panel || !matricula) {
        if (scanDom.info) {
            scanDom.info.textContent = 'Informe a matrícula do aluno para validar as tentativas disponíveis.';
        }
        return;
    }

    toggleScanLoading(true);
    try {
        const dados = await fetchJson(`${SCAN_API}?acao=attempts&exam_id=${examId}&matricula=${encodeURIComponent(matricula)}`);
        toggleScanLoading(false);

        if (!dados?.sucesso) {
            if (scanDom.info) {
                scanDom.info.textContent = dados?.mensagem || 'Não foi possível consultar as tentativas.';
            }
            if (scanDom.submit) {
                scanDom.submit.disabled = true;
            }
            return;
        }

        renderScanAttempts(dados.dados);
    } catch (error) {
        toggleScanLoading(false);
        if (scanDom.info) {
            scanDom.info.textContent = 'Falha ao consultar tentativas.';
        }
        if (scanDom.submit) {
            scanDom.submit.disabled = true;
        }
    }
}

function renderScanAttempts(dados) {
    const feitas = dados?.tentativas_feitas ?? 0;
    const proxima = dados?.proxima_tentativa;
    const max = dados?.max_tentativas ?? 3;

    if (proxima && scanDom.info) {
        scanDom.info.textContent = `Tentativa ${proxima} de ${max}.`;
        if (scanDom.submit) {
            scanDom.submit.disabled = false;
        }
    } else if (scanDom.info) {
        scanDom.info.textContent = `Limite de ${max} tentativas já atingido para este aluno.`;
        if (scanDom.submit) {
            scanDom.submit.disabled = true;
        }
    }

    renderScanHistory(dados?.scans ?? []);
}

function renderScanHistory(scans) {
    if (!scanDom.history || !scanDom.historyList) {
        return;
    }

    if (!Array.isArray(scans) || scans.length === 0) {
        scanDom.history.classList.add('oculto');
        scanDom.historyList.innerHTML = '';
        return;
    }

    scanDom.history.classList.remove('oculto');
    scanDom.historyList.innerHTML = scans
        .map((scan) => {
            const status = (scan.status || '').toString();
            const attempt = scan.attempt ? `Tentativa ${scan.attempt}` : '';
            const confidence = scan.overall_confidence != null ? ` • Confiança ${(scan.overall_confidence * 100).toFixed(1)}%` : '';
            const review = scan.requires_review ? ' • Revisão manual necessária' : '';
            const processed = scan.processed_at ? ` • Processado em ${scan.processed_at}` : '';
            return `<div class="scan-history-item"><strong>${status.toUpperCase()}</strong> ${attempt}${confidence}${review}${processed}</div>`;
        })
        .join('');
}

async function submitExamScan(event) {
    event.preventDefault();
    if (!scanDom.form) {
        return;
    }

    const examId = scanDom.examId?.value;
    const matricula = scanDom.matricula?.value?.trim();
    if (!matricula) {
        showModal({ titulo: 'Atenção', corpo: 'Informe a matrícula do aluno.' });
        return;
    }

    if (!scanDom.file || !scanDom.file.files || scanDom.file.files.length === 0) {
        showModal({ titulo: 'Atenção', corpo: 'Selecione o arquivo digitalizado da prova.' });
        return;
    }

    const formData = new FormData(scanDom.form);
    formData.append('acao', 'upload');

    toggleScanLoading(true);
    try {
        const dados = await fetchJson(SCAN_API, { method: 'POST', body: formData });
        toggleScanLoading(false);

        if (!dados?.sucesso) {
            showModal({ titulo: 'Erro', corpo: dados?.mensagem || 'Falha ao enviar o cartão de respostas.' });
            return;
        }

        currentScanId = dados.scan_id;
        mostrarProgressoInicial(dados);
        await pollScanStatus();
        scanPollTimer = setInterval(pollScanStatus, 4000);
        fetchScanAttempts(examId, matricula);
    } catch (error) {
        toggleScanLoading(false);
        showModal({ titulo: 'Erro', corpo: 'Não foi possível enviar o cartão de respostas.' });
    }
}

function mostrarProgressoInicial(payload) {
    if (!scanDom.progress || !scanDom.progressContent) {
        return;
    }
    scanDom.progress.classList.remove('oculto');
    const attempt = payload?.attempt ? `Tentativa ${payload.attempt}` : '';
    scanDom.progressContent.innerHTML = `<p><strong>Status:</strong> aguardando processamento. ${attempt}</p>`;
}

async function pollScanStatus() {
    if (!currentScanId) {
        return;
    }

    try {
        const dados = await fetchJson(`${SCAN_API}?acao=status&scan_id=${currentScanId}`);
        if (!dados?.sucesso) {
            return;
        }

        renderScanStatus(dados.dados);

        const status = (dados.dados.status || '').toString().toLowerCase();
        if (status === 'completed' || status === 'failed') {
            stopScanPolling();
            const examId = scanDom.examId?.value;
            const matricula = scanDom.matricula?.value?.trim();
            if (examId && matricula) {
                fetchScanAttempts(examId, matricula);
            }
        }
    } catch (error) {
        // Silencia erros de polling para evitar alertas repetitivos
    }
}

function renderScanStatus(dados) {
    if (!scanDom.progress || !scanDom.progressContent) {
        return;
    }

    scanDom.progress.classList.remove('oculto');

    const status = (dados.status || '').toString().toUpperCase();
    const attempt = dados.attempt ? `Tentativa ${dados.attempt}` : '';
    const confidence = dados.overall_confidence != null ? `${(dados.overall_confidence * 100).toFixed(1)}%` : 'n/d';
    const score = dados.score != null ? dados.score.toFixed(2) : 'n/d';
    const review = dados.requires_review ? '<span class="badge bg-warning text-dark">Revisar manualmente</span>' : '';
    const itens = Array.isArray(dados.itens)
        ? dados.itens
              .map((item) => {
                  const letra = item.detected_alternative ?? '-';
                  const conf = item.confidence != null ? `${(item.confidence * 100).toFixed(1)}%` : 'n/d';
                  const statusItem = (item.status || 'n/d').toString();
                  return `<li>Questão ${item.question_id}: resposta ${letra} • confiança ${conf} • status ${statusItem}</li>`;
              })
              .join('')
        : '';

    scanDom.progressContent.innerHTML = `
        <p><strong>Status:</strong> ${status} ${review}</p>
        <p><strong>${attempt}</strong> • Confiança geral: ${confidence}</p>
        <p><strong>Acertos:</strong> ${dados.correct_answers ?? 0}/${dados.total_questions ?? 0} • <strong>Nota estimada:</strong> ${score}</p>
        ${dados.error_message ? `<p class="text-danger">${dados.error_message}</p>` : ''}
        <ul class="scan-status-list">${itens}</ul>
    `;
}

function handleFormSubmit(event) {
    event.preventDefault();
    const form = dom('formProvas');
    if (!form) {
        return;
    }

    const fd = new FormData(form);
    fd.append('acao', dom('idProva')?.value ? 'editar' : 'criar');

    fetchJson(API_BASE, { method: 'POST', body: fd }).then((dados) => {
        if (dados?.sucesso) {
            dom('boxCrudProva')?.classList.add('oculto');
            listarProvas();
            mostrarAlerta('Prova salva com sucesso!', 'success');
        } else {
            showModal({ titulo: 'Erro', corpo: dados?.mensagem || 'Não foi possível salvar a prova.' });
        }
    });
}

function bindEvents() {
    dom('materia')?.addEventListener('change', carregarQuestoes);
    dom('btnNovaProva')?.addEventListener('click', () => openProva(null, 'criar'));
    dom('btnCancelarProva')?.addEventListener('click', () => dom('boxCrudProva')?.classList.add('oculto'));
    dom('formProvas')?.addEventListener('submit', handleFormSubmit);
    dom('filtrosFormProvas')?.addEventListener('submit', (event) => {
        event.preventDefault();
        listarProvas();
    });
    dom('btnLimparFiltroProvas')?.addEventListener('click', () => {
        const input = dom('filtroTextoProvas');
        if (input) {
            input.value = '';
        }
        listarProvas();
    });

    scanDom.panel = dom('examScanPanel');
    scanDom.form = dom('formExamScan');
    scanDom.examId = dom('scanExamId');
    scanDom.examTitle = dom('scanExamTitle');
    scanDom.matricula = dom('scanMatricula');
    scanDom.file = dom('scanFile');
    scanDom.info = dom('scanAttemptsInfo');
    scanDom.loading = dom('scanLoading');
    scanDom.progress = dom('scanProgress');
    scanDom.progressContent = dom('scanProgressContent');
    scanDom.history = dom('scanHistory');
    scanDom.historyList = dom('scanHistoryList');
    scanDom.subtitle = dom('examScanSubtitle');
    scanDom.submit = dom('btnEnviarScan');
    scanDom.cancel = dom('btnCancelarScan');

    scanDom.form?.addEventListener('submit', submitExamScan);
    scanDom.cancel?.addEventListener('click', () => closeScanPanel());

    if (scanDom.matricula) {
        const triggerAttempts = () => {
            const examId = scanDom.examId?.value;
            const matricula = scanDom.matricula?.value?.trim();
            if (examId && matricula) {
                fetchScanAttempts(examId, matricula);
            }
        };
        scanDom.matricula.addEventListener('change', triggerAttempts);
        scanDom.matricula.addEventListener('blur', triggerAttempts);
    }
}

export function initExamsModule() {
    toggleCrudLoading(true);
    Promise.resolve()
        .then(carregarDadosIniciais)
        .then(carregarQuestoes)
        .then(() => {
            bindEvents();
            listarProvas();
        })
        .catch((error) => {
            console.error('Erro ao inicializar módulo de provas', error);
            showModal({ titulo: 'Erro', corpo: 'Falha ao carregar dados iniciais das provas.' });
        })
        .finally(() => {
            toggleCrudLoading(false);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    if (dom('formProvas')) {
        initExamsModule();
    }
});
