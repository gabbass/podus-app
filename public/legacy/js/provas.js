// js/provas.js — CRUD de Provas com AJAX e Choices.js
let modoCRUD = 'criar';
const dom = id => document.getElementById(id);
let choicesTurma, choicesMateria, choicesQuestoes;

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

