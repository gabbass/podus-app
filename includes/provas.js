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
    fetch('includes/action-provas.php?acao=listarTurmas').then(r => r.json()),
    fetch('includes/action-provas.php?acao=listarMaterias').then(r => r.json()),
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
  const url = `includes/action-provas.php?acao=listarQuestoes${mat?'&materia='+encodeURIComponent(mat):''}`;
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
    const resp = await fetch(`includes/action-provas.php?acao=buscar&id=${id}`);
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
  const resp = await fetch('includes/action-provas.php?acao=listar');
  const j = await resp.json();
  if (!j.sucesso) return showModal({ corpo: j.msg });

  const tbody = dom('tbodyProvas');
  tbody.innerHTML = '';

  j.provas.forEach(p => {
	
    const tr = document.createElement('tr');
    tr.dataset.id = p.id;
    tr.innerHTML = `
      <td>${p.id}</td>
      <td>${p.turma}</td>
      <td>${p.lista_quest ? p.lista_quest.split(',').length : 0}</td>
      <td>${p.materia}</td>
      <td>${p.escola}</td>
      <td>
        <button class="btn-action btn-edit" id="btnEdit-${p.id}"><i class="fas fa-edit"></i></button>
        <button class="btn-action btn-view" id="btnView-${p.id}"><i class="fas fa-eye"></i></button>
        <button class="btn-action btn-delete" id="btnDel-${p.id}"><i class="fas fa-trash-alt"></i></button>
      </td>`;
    tbody.appendChild(tr);

    dom(`btnEdit-${p.id}`).addEventListener('click', () => openProva(p.id, 'editar'));
    dom(`btnView-${p.id}`).addEventListener('click', () => {
  window.location.href = `provas-visualizar.php?id=${p.id}&de=provas`;
});

    dom(`btnDel-${p.id}`).addEventListener('click', () => delProva(p.id));
	
  });
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
      const resp = await fetch('includes/action-provas.php', { method: 'POST', body: fd });
      const j = await resp.json();
      if (j.sucesso) listarProvas();
      else showModal({ corpo: j.msg });
    }
  });
}
async function showLoading(on) {
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
    const resp = await fetch('includes/action-provas.php', { method: 'POST', body: fd });
    const j = await resp.json();
    if (j.sucesso) {
      dom('boxCrudProva').classList.add('oculto');
      listarProvas();
	  mostrarAlerta('Prova salva com sucesso!','success');
    } else {
		mostrarAlerta({ corpo: j.msg },'danger');
      
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

