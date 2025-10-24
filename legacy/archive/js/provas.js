// js/provas.js — CRUD de Provas com AJAX e Choices.js
let modoCRUD = 'criar';
const dom = id => document.getElementById(id);
let choicesTurma, choicesMateria, choicesQuestoes;
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

// Exibe modal genérico
function showModal({ titulo = '', corpo = '', textoConfirmar = 'OK', onConfirm }) {
  const m = dom('modalGeral');
  if (!m) return alert(corpo || titulo);
  dom('modalGeralLabel').textContent = titulo;
  dom('modalGeralBody').innerHTML = corpo;
  const btn = dom('modalGeralConfirmar');
  btn.textContent = textoConfirmar;
  btn.onclick = () => { bootstrap.Modal.getInstance(m).hide(); onConfirm?.(); };
  bootstrap.Modal.getOrCreateInstance(m).show();
}

// Carrega turmas e matérias, inicializa Choices.js
async function carregarDados() {
  // Turmas
  
  const [tJ, mJ] = await Promise.all([
    fetch('/api/provas?acao=listarTurmas').then(r => r.json()),
    fetch('/api/provas?acao=listarMaterias').then(r => r.json()),
  ]);
  
  if (tJ.sucesso) {
    const selT = dom('turma');
    tJ.turmas.forEach(t => {
	  const opt = document.createElement('option');
	  opt.value = t.nome;        // agora value = nome
	  opt.textContent = t.nome;
	  selT.append(opt);
	});
  }

  // Matérias
  if (mJ.sucesso) {
    const selM = dom('materia');
    mJ.materias.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m;
      opt.textContent = m;
      selM.append(opt);
    });
  }

  // Inicializa Choices.js para Turma e Matéria
  choicesTurma = new Choices(dom('turma'), {
    searchEnabled: true,
    placeholderValue: 'Selecione a turma'
  });
  choicesMateria = new Choices(dom('materia'), {
    searchEnabled: true,
    placeholderValue: 'Selecione a matéria'
  });
}

// Carrega questões de acordo com o filtro de matéria
async function carregarQuestoes() {
  const mat = dom('materia').value.trim();
  const url = `/api/provas?acao=listarQuestoes${mat?'&materia='+encodeURIComponent(mat):''}`;
  const resp = await fetch(url);
  const j    = await resp.json();
  if (!j.sucesso) return showModal({ corpo: j.msg });

  const selQ = dom('lista_quest');
  selQ.innerHTML = '';

  const data = j.questoes.map(q => ({
    value: String(q.id),
    label: `${q.id} – ${q.questao.substring(0,40)}…`
  }));

  if (!choicesQuestoes) {
    choicesQuestoes = new Choices(selQ, {
      removeItemButton: true,
      placeholderValue: 'Selecione as questões',
      choices: data
    });
  } else {
    choicesQuestoes.clearChoices();
    choicesQuestoes.setChoices(data, 'value', 'label', true);
  }
}

// Abre o formulário para criar, editar ou visualizar
async function openProva(id, modo) {
	
  modoCRUD = modo;
  dom('boxCrudProva').classList.remove('oculto');
  resetFormProva();
  updateHeaderProva();

  // Recarrega opções antes de preencher
  
  await carregarQuestoes();

  if (id) {
	  showLoading(true);
    const resp = await fetch(`/api/provas?acao=buscar&id=${id}`);
    const j    = await resp.json();
    if (!j.sucesso) return showModal({ corpo: j.msg });

    dom('idProva').value = j.dado.id;
    choicesTurma.setChoiceByValue(j.dado.turma);
    choicesMateria.setChoiceByValue(j.dado.materia);
    dom('escola').value = j.dado.escola;

    // Seleciona questões
    const arr = j.dado.lista_quest.split(',').map(String);
    choicesQuestoes.removeActiveItems();
    arr.forEach(val => choicesQuestoes.setChoiceByValue(val));
	showLoading(false);
  }

  modo === 'visualizar' ? lockFormProva() : unlockFormProva();
}

function lockFormProva() {
  [...dom('formProvas').elements].forEach(el => el.disabled = true);
  dom('btnSalvarProva').style.display = 'none';
}
function unlockFormProva() {
  [...dom('formProvas').elements].forEach(el => el.disabled = false);
  dom('btnSalvarProva').style.display = '';
}

function resetFormProva() {
  dom('formProvas').reset();
  dom('idProva').value = '';
}

// Atualiza o cabeçalho CRUD de acordo com o modo
function updateHeaderProva() {
  dom('tituloCrudProvas').textContent =
    modoCRUD === 'editar' ? 'Editar provas' :
    modoCRUD === 'visualizar' ? 'Visualizar provas' :
    'Criar provas';

  dom('subtituloCrudProvas').textContent =
    modoCRUD === 'criar' ? 'Criar' :
    modoCRUD === 'editar' ? 'Editar' :
    'Visualizar';

  const btnNova = dom('btnNovaProva');
  if (btnNova) btnNova.classList.toggle('oculto', modoCRUD !== 'criar');

  const btnEdit = dom('btnEditaProva');
  if (btnEdit) btnEdit.classList.toggle('oculto', modoCRUD !== 'visualizar');
}
// Lista as provas na tabela
async function listarProvas() {
  showLoading(true);
  const resp = await fetch('/api/provas?acao=listar');
  const j = await resp.json();
  if (!j.sucesso) {
    showLoading(false);
    return showModal({ corpo: j.msg || 'Não foi possível listar as provas.' });
  }

  const filtro = dom('filtroTextoProvas')?.value?.trim().toLowerCase() || '';
  const provas = Array.isArray(j.provas) ? j.provas : [];
  const filtradas = filtro
    ? provas.filter(p => {
        const campos = [p.id, p.turma, p.materia, p.escola, p.lista_quest]
          .map(valor => (valor ?? '').toString().toLowerCase());
        return campos.some(texto => texto.includes(filtro));
      })
    : provas;

  const tbody = dom('tbodyProvas');
  tbody.innerHTML = '';

  filtradas.forEach(p => {
    const questoes = (p.lista_quest || '')
      .toString()
      .split(',')
      .map(v => v.trim())
      .filter(Boolean);

    const tr = document.createElement('tr');
    tr.dataset.id = p.id;
    tr.innerHTML = `
      <td>${p.id}</td>
      <td>${p.turma ?? ''}</td>
      <td>${p.materia ?? ''}</td>
      <td>${questoes.length}</td>
      <td>${p.escola ?? ''}</td>
      <td>
        <button class="btn-action btn-edit" data-action="editar-prova" data-id="${p.id}"><i class="fas fa-edit"></i></button>
        <button class="btn-action btn-view" data-action="visualizar-prova" data-id="${p.id}"><i class="fas fa-eye"></i></button>
        <button class="btn-action btn-delete" data-action="excluir-prova" data-id="${p.id}"><i class="fas fa-trash-alt"></i></button>
      </td>`;
    tbody.appendChild(tr);

    tr.querySelector('[data-action="editar-prova"]').addEventListener('click', () => openProva(p.id, 'editar'));
    tr.querySelector('[data-action="visualizar-prova"]').addEventListener('click', () => openProva(p.id, 'visualizar'));
    tr.querySelector('[data-action="excluir-prova"]').addEventListener('click', () => delProva(p.id));
    const btnScan = tr.querySelector('[data-action="scan-prova"]');
    if (btnScan) btnScan.addEventListener('click', () => openScanPanelForExam(p));
  });

  const total = dom('totalRegistrosProvas');
  if (total) total.textContent = filtradas.length;

  showLoading(false);
}

// Exclui uma prova após confirmação
function delProva(id) {
  showModal({
    titulo: 'Confirmar',
    corpo: 'Excluir prova?',
    textoConfirmar: 'Excluir',
    onConfirm: async () => {
      const fd = new FormData();
      fd.append('acao', 'excluir');
      fd.append('id', id);
      const resp = await fetch('/api/provas', { method: 'POST', body: fd });
      const j = await resp.json();
      if (j.sucesso) listarProvas();
      else showModal({ corpo: j.msg });
    }
  });
}

function openScanPanelForExam(prova) {
  if (!scanDom.panel) return;
  scanDom.panel.classList.remove('oculto');
  scanDom.examId.value = prova.id;
  scanDom.examTitle.value = `${prova.materia ?? ''} - ${prova.turma ?? ''}`.trim();
  scanDom.matricula.value = '';
  if (scanDom.file) scanDom.file.value = '';
  if (scanDom.subtitle) scanDom.subtitle.textContent = `Turma ${prova.turma ?? ''} • ${prova.materia ?? ''}`;
  scanDom.info.textContent = 'Informe a matrícula do aluno para validar as tentativas disponíveis.';
  scanDom.progressContent.innerHTML = '';
  scanDom.progress.classList.add('oculto');
  scanDom.history.classList.add('oculto');
  scanDom.historyList.innerHTML = '';
  if (scanDom.submit) scanDom.submit.disabled = false;
  stopScanPolling();
  currentScanId = null;
}

function closeScanPanel() {
  if (!scanDom.panel) return;
  scanDom.panel.classList.add('oculto');
  stopScanPolling();
  currentScanId = null;
}

function toggleScanLoading(on) {
  if (!scanDom.loading) return;
  scanDom.loading.classList.toggle('oculto', !on);
}

function stopScanPolling() {
  if (scanPollTimer) {
    clearInterval(scanPollTimer);
    scanPollTimer = null;
  }
}

async function fetchScanAttempts(examId, matricula) {
  if (!scanDom.panel) return;
  if (!matricula) {
    scanDom.info.textContent = 'Informe a matrícula do aluno para validar as tentativas disponíveis.';
    return;
  }

  toggleScanLoading(true);
  try {
    const resp = await fetch(`/api/exam-scans.php?acao=attempts&exam_id=${examId}&matricula=${encodeURIComponent(matricula)}`);
    const data = await resp.json();
    toggleScanLoading(false);

    if (!data.sucesso) {
      scanDom.info.textContent = data.mensagem || 'Não foi possível consultar as tentativas.';
      if (scanDom.submit) scanDom.submit.disabled = true;
      return;
    }

    renderScanAttempts(data.dados);
  } catch (erro) {
    toggleScanLoading(false);
    scanDom.info.textContent = 'Falha ao consultar tentativas.';
    if (scanDom.submit) scanDom.submit.disabled = true;
  }
}

function renderScanAttempts(dados) {
  if (!scanDom.info) return;
  const feitas = dados.tentativas_feitas ?? 0;
  const proxima = dados.proxima_tentativa;
  const max = dados.max_tentativas ?? 3;

  if (proxima) {
    scanDom.info.textContent = `Tentativa ${proxima} de ${max}.`;
    if (scanDom.submit) scanDom.submit.disabled = false;
  } else {
    scanDom.info.textContent = `Limite de ${max} tentativas já atingido para este aluno.`;
    if (scanDom.submit) scanDom.submit.disabled = true;
  }

  renderScanHistory(dados.scans ?? []);
}

function renderScanHistory(scans) {
  if (!scanDom.history || !scanDom.historyList) return;
  if (!Array.isArray(scans) || scans.length === 0) {
    scanDom.history.classList.add('oculto');
    scanDom.historyList.innerHTML = '';
    return;
  }

  scanDom.history.classList.remove('oculto');
  scanDom.historyList.innerHTML = scans
    .map(scan => {
      const status = (scan.status || '').toString();
      const attempt = scan.attempt ? `Tentativa ${scan.attempt}` : '';
      const confid = scan.overall_confidence != null ? ` • Confiança ${(scan.overall_confidence * 100).toFixed(1)}%` : '';
      const review = scan.requires_review ? ' • Revisão manual necessária' : '';
      const processed = scan.processed_at ? ` • Processado em ${scan.processed_at}` : '';
      return `<div class="scan-history-item"><strong>${status.toUpperCase()}</strong> ${attempt}${confid}${review}${processed}</div>`;
    })
    .join('');
}

async function submitExamScan(ev) {
  ev.preventDefault();
  if (!scanDom.form) return;

  const examId = scanDom.examId.value;
  const matricula = scanDom.matricula.value.trim();
  if (!matricula) {
    showModal({ titulo: 'Atenção', corpo: 'Informe a matrícula do aluno.' });
    return;
  }

  if (!scanDom.file || !scanDom.file.files || scanDom.file.files.length === 0) {
    showModal({ titulo: 'Atenção', corpo: 'Selecione o arquivo digitalizado da prova.' });
    return;
  }

  const fd = new FormData(scanDom.form);
  fd.append('acao', 'upload');

  toggleScanLoading(true);
  try {
    const resp = await fetch('/api/exam-scans.php', { method: 'POST', body: fd });
    const data = await resp.json();
    toggleScanLoading(false);

    if (!data.sucesso) {
      showModal({ titulo: 'Erro', corpo: data.mensagem || 'Falha ao enviar o cartão de respostas.' });
      return;
    }

    currentScanId = data.scan_id;
    showScanProgressPlaceholder(data);
    pollScanStatus();
    scanPollTimer = setInterval(pollScanStatus, 4000);
    fetchScanAttempts(examId, matricula);
  } catch (erro) {
    toggleScanLoading(false);
    showModal({ titulo: 'Erro', corpo: 'Não foi possível enviar o cartão de respostas.' });
  }
}

function showScanProgressPlaceholder(payload) {
  if (!scanDom.progress || !scanDom.progressContent) return;
  scanDom.progress.classList.remove('oculto');
  const attempt = payload.attempt ? `Tentativa ${payload.attempt}` : '';
  scanDom.progressContent.innerHTML = `<p><strong>Status:</strong> aguardando processamento. ${attempt}</p>`;
}

async function pollScanStatus() {
  if (!currentScanId) return;
  try {
    const resp = await fetch(`/api/exam-scans.php?acao=status&scan_id=${currentScanId}`);
    const data = await resp.json();
    if (!data.sucesso) {
      return;
    }
    renderScanStatus(data.dados);

    if (['completed', 'failed'].includes((data.dados.status || '').toLowerCase())) {
      stopScanPolling();
      const examId = scanDom.examId.value;
      const matricula = scanDom.matricula.value.trim();
      if (examId && matricula) {
        fetchScanAttempts(examId, matricula);
      }
    }
  } catch (erro) {
    // polling errors are ignored para evitar loops de alerta
  }
}

function renderScanStatus(statusData) {
  if (!scanDom.progress || !scanDom.progressContent) return;
  scanDom.progress.classList.remove('oculto');

  const status = (statusData.status || '').toString().toUpperCase();
  const attempt = statusData.attempt ? `Tentativa ${statusData.attempt}` : '';
  const confidence = statusData.overall_confidence != null ? `${(statusData.overall_confidence * 100).toFixed(1)}%` : 'n/d';
  const score = statusData.score != null ? statusData.score.toFixed(2) : 'n/d';
  const review = statusData.requires_review ? '<span class="badge bg-warning text-dark">Revisar manualmente</span>' : '';
  const itens = Array.isArray(statusData.itens)
    ? statusData.itens.map(item => {
        const letra = item.detected_alternative ?? '-';
        const conf = item.confidence != null ? `${(item.confidence * 100).toFixed(1)}%` : 'n/d';
        const st = (item.status || 'n/d').toString();
        return `<li>Questão ${item.question_id}: resposta ${letra} • confiança ${conf} • status ${st}</li>`;
      }).join('')
    : '';

  scanDom.progressContent.innerHTML = `
    <p><strong>Status:</strong> ${status} ${review}</p>
    <p><strong>${attempt}</strong> • Confiança geral: ${confidence}</p>
    <p><strong>Acertos:</strong> ${statusData.correct_answers ?? 0}/${statusData.total_questions ?? 0} • <strong>Nota estimada:</strong> ${score}</p>
    ${statusData.error_message ? `<p class="text-danger">${statusData.error_message}</p>` : ''}
    <ul class="scan-status-list">${itens}</ul>
  `;
}
function showLoading(on) {
  dom('crudLoading').classList.toggle('oculto', !on);
}
// DOMContentLoaded — inicializa tudo
document.addEventListener('DOMContentLoaded', async () => {
        showLoading(true);
  await carregarDados();
  await carregarQuestoes();
   showLoading(false);


  dom('materia').addEventListener('change', carregarQuestoes);
  dom('btnNovaProva').addEventListener('click', () => openProva(null, 'criar'));
  dom('btnCancelarProva').addEventListener('click', () => dom('boxCrudProva').classList.add('oculto'));

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

  if (scanDom.form) {
    scanDom.form.addEventListener('submit', submitExamScan);
  }
  if (scanDom.cancel) {
    scanDom.cancel.addEventListener('click', () => {
      closeScanPanel();
    });
  }
  if (scanDom.matricula) {
    const triggerAttempts = () => {
      const examId = scanDom.examId ? scanDom.examId.value : '';
      const matricula = scanDom.matricula.value.trim();
      if (examId && matricula) {
        fetchScanAttempts(examId, matricula);
      }
    };
    scanDom.matricula.addEventListener('change', triggerAttempts);
    scanDom.matricula.addEventListener('blur', triggerAttempts);
  }

  dom('formProvas').addEventListener('submit', async ev => {
    ev.preventDefault();
    const fd = new FormData(dom('formProvas'));
    fd.append('acao', dom('idProva').value ? 'editar' : 'criar');
    const resp = await fetch('/api/provas', { method: 'POST', body: fd });
    const j = await resp.json();
    if (j.sucesso) {
      dom('boxCrudProva').classList.add('oculto');
      listarProvas();
      showModal({ titulo: 'Sucesso', corpo: 'Prova salva com sucesso!' });
    } else {
      showModal({ titulo: 'Erro', corpo: j.msg || 'Não foi possível salvar a prova.' });
    }
  });

  dom('filtrosFormProvas').addEventListener('submit', ev => {
    ev.preventDefault();
    listarProvas();
  });
  dom('btnLimparFiltroProvas').addEventListener('click', () => {
    dom('filtroTextoProvas').value = '';
    listarProvas();
  });

  listarProvas();
});

