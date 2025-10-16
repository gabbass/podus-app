// js/crud-linha.js
window.choicesHabilidadesMap = {};
/* ============================================================
 * CRUD – Linhas do Planejamento Mensal (IDs dinâmicos por bloco)
 * ============================================================ */

/* -----------------------------------------------------------------
 * helper: gera IDs únicos por bloco (gid = data-grupo)
 * ----------------------------------------------------------------- */
const gidId = (base, gid) => `${base}-${gid}`;
const toInt = v => parseInt(v, 10);

/* -----------------------------------------------------------------
 * estado global (compartilhado entre todos os blocos)
 * ----------------------------------------------------------------- */
let linhasPlanejamento = [];      // array completo do plano
let editandoLinhaId    = null;    // índice da linha sendo editada
let linhasPlanejamentoParaExcluir = [];
/* ============================================================
 * 1.  INICIALIZAÇÃO DE CADA BLOCO (mês / grupo)
 * ============================================================ */
function initBlocoLinha(divBloco) {
  const gid       = divBloco.getAttribute('data-grupo');
  const btnAdd    = document.getElementById(gidId('btnAdicionarLinha', gid));
  const btnSalvar = document.getElementById(gidId('btnSalvarLinha', gid));
  const btnCancel = document.getElementById(gidId('btnCancelarLinha', gid));

  // NOVA LINHA
  if (btnAdd) {
    btnAdd.onclick = function(e) {
      e.preventDefault();
      setTituloSubtituloAddLinhaMensal('criar', gid);
      reiniciarCascataBNCC(gid);
      const form = document.getElementById(gidId('form-linha-bncc', gid));
      if (form) form.classList.remove('oculto');
    };
  }

  // SALVAR / CANCELAR
  if (btnSalvar) btnSalvar.onclick = () => adicionarOuEditarLinha(gid);
  if (btnCancel) btnCancel.onclick = () => limparSubformularioLinha(gid);

  // plugins (Summernote / cascata) carregados apenas 1×
   // 1) Bind da cascata sempre (para cada bloco)
  bindSequencialBNCC(gid);

  // 2) Summernote só no primeiro bloco
  if (!window.__LINHA_PLUGINS__) {
    if (window.jQuery) {
      ['conteudos-linha','metodologias-linha'].forEach(base => {
        const sel = document.getElementById(gidId(base, gid));
        if (sel) $('#'+sel.id).summernote({ height: 140 });
      });
    }
    window.__LINHA_PLUGINS__ = true;
  }
  
	// inicializa o Choices no select de habilidades do bloco
	const habSel = document.getElementById(gidId('habilidades-linha', gid));
	if (window.Choices && habSel) {
	  // salva a instância num map, indexado pelo gid
	  window.choicesHabilidadesMap[gid] = new Choices(habSel, { removeItemButton: true });
	}


}

function serializarLinhasNoForm() {
  const h = document.getElementById('linhas-planejamento');
  if (h) h.value = JSON.stringify(linhasPlanejamento);
  
}

// expõe API para o script principal
window.crudLinha = {
  initBloco : initBlocoLinha,
  serializar: serializarLinhasNoForm,
  linhas    : linhasPlanejamento
};

/* ============================================================
 * 2.  UTILITÁRIOS (ids dinâmicos)
 * ============================================================ */
function setTituloSubtituloAddLinhaMensal(acao, gid) {
  const t = document.getElementById(gidId('tituloAddLinhaMensal', gid));
  const s = document.getElementById(gidId('subtituloAddLinhaMensal', gid));
  if (!t || !s) return;
  if (acao === 'criar') {
    t.textContent = 'Nova linha';
    s.textContent = 'Preencha os dados da BNCC para cadastrar';
  } else {
    t.textContent = 'Editar linha';
    s.textContent = 'Altere os campos desejados e clique em Salvar.';
  }
}

function limparSelect(baseId, gid) {
  const sel = document.getElementById(gidId(baseId, gid));
  if (sel) sel.innerHTML = '<option value="">Selecione...</option>';
}

function reiniciarCascataBNCC(gid) {
  // limpa todos os selects e grupos
  ['etapa-linha','ano-linha','area-linha','componente-linha','unidadeTematica-linha','objetosConhecimento-linha','habilidades-linha']
    .forEach(base => limparSelect(base, gid));
  ['grupo-etapa','grupo-ano','grupo-area','grupo-componente','grupo-unidade','grupo-objetos','grupo-habilidades']
    .forEach(gr => {
      const grp = document.getElementById(gidId(gr, gid));
      if (grp) grp.classList.add('oculto');
    });
  // mostra etapa
  const etapaGroup = document.getElementById(gidId('grupo-etapa', gid));
  if (etapaGroup) etapaGroup.classList.remove('oculto');

  editandoLinhaId = null;
  const btnSalvar = document.getElementById(gidId('btnSalvarLinha', gid));
  if (btnSalvar) btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar Linha';

  // Carrega Etapas
  fetch('includes/action-planejamento-mensal.php?acao=bncc&campo=etapas')
    .then(r => r.json())
    .then(lista => {
      const sel = document.getElementById(gidId('etapa-linha', gid));
      if (!sel) return;
      sel.innerHTML = '<option value="">Selecione a etapa</option>';
      lista.forEach(it => {
        const opt = document.createElement('option'); opt.value = it.id; opt.textContent = it.label;
        sel.appendChild(opt);
      });
    })
    .catch(() => mostrarAlerta('Erro ao carregar etapas!', 'danger'));
}

function getNomeBNCC(tipo, id) {
  const k = String(id);
  if (tipo === 'habilidades') {
    return window.bnccMaps['habilidades-linha']?.[k] || window.bnccMaps['habilidades']?.[k] || k || '';
  }
  return window.bnccMaps[tipo]?.[k] || k || '';
}

function serializarLinhasNoForm() {
  const h = document.getElementById('linhas_serializadas');
  if (h) h.value = JSON.stringify(linhasPlanejamento);
}

function renderizarTabelaLinhas(gidNum) {
	
  const gid = toInt(gidNum);                      // garante número
  const tb  = document.getElementById(`tbody-linhas-planejamento-${gid}`);
  if (!tb) return;
  tb.innerHTML = '';

  // grupo no JSON é 1-based
  linhasPlanejamento
    .filter(l => toInt(l.grupo) === gid)
    .forEach((l, idx) => {
		const tr = document.createElement('tr');
		if (l.id) tr.dataset.id = l.id; 
		tr.dataset.etapa        = l.etapa;
		tr.dataset.ano          = l.ano;
		tr.dataset.area         = l.area;
		tr.dataset.componente   = l.componenteCurricular;
		tr.dataset.unidade      = l.unidadeTematicas;
		tr.dataset.objeto       = l.objetosConhecimento;
		tr.dataset.habilidades  = l.habilidades.join(',');
		tr.dataset.conteudos    = l.conteudos.replace(/\s+/g,' ');
		tr.dataset.metodologias = l.metodologias.replace(/\s+/g,' ');
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${getNomeBNCC('etapas', l.etapa)} / ${l.ano}</td>
        <td>${getNomeBNCC('areas',  l.area)}</td>
        <td>${getNomeBNCC('componentes', l.componenteCurricular)}</td>
        <td>${getNomeBNCC('unidades_tematicas', l.unidadeTematicas)}</td>
        <td>${getNomeBNCC('objetosConhecimento', l.objetosConhecimento)}</td>
        <td>${l.habilidades.map(h => getNomeBNCC('habilidades', h)).join(', ')}</td>
        <td>
          <button class="btn-action btn-edit"
                  onclick="editarLinha(${idx}, ${gid})">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn-action btn-danger"
                  onclick="excluirLinha(${idx}, ${gid})">
            <i class="fas fa-trash"></i>
          </button>
        </td>`;
      tb.appendChild(tr);
    });
}


function limparSubformularioLinha(gid) {
  const container = document.getElementById(`form-linha-bncc-${gid}`);
  if (!container) return;

  // Oculta o formulário
  container.classList.add('oculto');

  // Limpa todos os campos de entrada dentro do container
  container.querySelectorAll('input, select, textarea').forEach(el => {
    if (el.classList.contains('note-editable')) return; // ignora Summernote diretamente

    if (el.tagName === 'SELECT') {
      el.selectedIndex = 0;
      el.dispatchEvent(new Event('change')); // dispara evento se necessário
    } else if (el.tagName === 'TEXTAREA' && $(el).hasClass('summernote')) {
      $(el).summernote('reset');
    } else {
      el.value = '';
    }
  });
}

/* ============================================================
 * 3.  CRUD da LINHA                                            */
function adicionarOuEditarLinha(gidRaw) {

		const gid = toInt(gidRaw);     
	  const etapa = document.getElementById(gidId('etapa-linha', gid)).value;
	  const ano   = document.getElementById(gidId('ano-linha', gid)).value;
	  const area  = document.getElementById(gidId('area-linha', gid)).value;
	  const comp  = document.getElementById(gidId('componente-linha', gid)).value;
	  const uni   = document.getElementById(gidId('unidadeTematica-linha', gid)).value;
	  const obj   = document.getElementById(gidId('objetosConhecimento-linha', gid)).value;
  let cont = '', met = '';
  if (window.jQuery) {
    cont = $('#'+gidId('conteudos-linha', gid)).summernote('code');
    met  = $('#'+gidId('metodologias-linha', gid)).summernote('code');
  }
  
  
   let linhaId = null;
  if (editandoLinhaId !== null) {
    linhaId = linhasPlanejamento[editandoLinhaId].id || null;
  }
  
  

  if (!etapa || !ano || !area || !comp) {
    mostrarAlerta('Preencha todos os campos obrigatórios da linha!', 'danger');
    return;
  }

  const habOptions = document.getElementById(gidId('habilidades-linha', gid));
  const habilidades = [];
  if (habOptions) {
    for (let i = 0; i < habOptions.options.length; i++) {
      const opt = habOptions.options[i];
      if (opt.selected) habilidades.push(opt.value);
    }
  }

  const linha = { 
					id: linhaId, 
					grupo: gid + 1,  
				  etapa, ano, area,
                  componenteCurricular: comp,
                  unidadeTematicas: uni,
                  objetosConhecimento: obj,
                  habilidades,
                  conteudos: cont,
                  metodologias: met };

  if (editandoLinhaId !== null) {
    linhasPlanejamento[editandoLinhaId] = linha;
    mostrarAlerta('Linha alterada com sucesso!', 'success');
  } else {
    linhasPlanejamento.push(linha);
    mostrarAlerta('Linha adicionada com sucesso!', 'success');
  }

  renderizarTabelaLinhas(gid);  
  serializarLinhasNoForm();
  limparSubformularioLinha(gid);
  document.getElementById(gidId('form-linha-bncc', gid))?.classList.add('oculto');
    editandoLinhaId = null;
}

function editarLinha(idx) {
  const l = linhasPlanejamento[idx];
  if (!l) return;
  // usa primeiro bloco para edição
  const bloco = document.querySelector('[data-grupo]');
  const gid   = bloco.getAttribute('data-grupo');
  editandoLinhaId = idx;
  setTituloSubtituloAddLinhaMensal('editar', gid);
  // preenche campos
  document.getElementById(gidId('etapa-linha', gid)).value = l.etapa;
  document.getElementById(gidId('ano-linha', gid)).value = l.ano;
  document.getElementById(gidId('area-linha', gid)).value = l.area;
  document.getElementById(gidId('componente-linha', gid)).value = l.componenteCurricular;
  document.getElementById(gidId('unidadeTematica-linha', gid)).value = l.unidadeTematicas;
  document.getElementById(gidId('objetosConhecimento-linha', gid)).value = l.objetosConhecimento;
  const form = document.getElementById(gidId('form-linha-bncc', gid));
  if (form) form.classList.remove('oculto');

  // sequência BNCC encadeada
  carregarSelectESelecionar('etapa-linha', 'etapas', {},          l.etapa, 'grupo-etapa', gid)
    .then(() => carregarSelectESelecionar(
  'ano-linha', 'anos',
  { id_etapa: l.etapa },
  `${l.ano}º`,           // concatena o "º" automaticamente
  'grupo-ano',
  gid
))


    .then(() => carregarSelectESelecionar('area-linha', 'areas',   {id_etapa: l.etapa}, l.area, 'grupo-area', gid))
    .then(() => carregarSelectESelecionar('componente-linha', 'componentes', {id_area: l.area}, l.componenteCurricular, 'grupo-componente', gid))
    .then(() => carregarSelectESelecionar('unidadeTematica-linha', 'unidades_tematicas', {id_componente: l.componenteCurricular}, l.unidadeTematicas, 'grupo-unidade', gid))
    .then(() => carregarSelectESelecionar('objetosConhecimento-linha', 'objetosConhecimento', {id_unidade_tematica: l.unidadeTematicas}, l.objetosConhecimento, 'grupo-objetos', gid))
    .then(() => {
  return fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=habilidades&id_objeto=${l.objetosConhecimento}`)
    .then(r => r.json())
    .then(lista => {
      const ch = window.choicesHabilidadesMap[gid];
      ch.clearChoices();
      ch.setChoices(
        lista.map(it => ({value: String(it.id), label: it.label, disabled: false})),
        'value','label', true
      );
      // marca as que vieram no JSON
      ch.setChoiceByValue(l.habilidades.map(String));
      document.getElementById(gidId('grupo-habilidades', gid))
              .classList.remove('oculto');
    });
})

    .then(() => {
      if (window.jQuery) {
        $('#'+gidId('conteudos-linha', gid)).summernote('code', l.conteudos);
        $('#'+gidId('metodologias-linha', gid)).summernote('code', l.metodologias);
      }
      const btnSalvar = document.getElementById(gidId('btnSalvarLinha', gid));
      if (btnSalvar) btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar alterações';
    });
}

function excluirLinha(idx, gidRaw) {
  const gid = toInt(gidRaw);
  showModalGeral({
    título: 'Excluir linha',
    corpo: '<p>Deseja realmente excluir esta linha?</p>',
    textoConfirmar: 'Sim, excluir',
    onConfirm: () => {
      linhasPlanejamento.splice(idx, 1);
      renderizarTabelaLinhas(gid);
      serializarLinhasNoForm();
    }
  });
}



/* ============================================================
 * 4.  CASCATA BNCC – funções auxiliares
 * ============================================================ */
function carregarSelectESelecionar(baseId, entidade, filtros = {}, valorSel = null, grupoMostrar = null, gid) {
  const sel = document.getElementById(gidId(baseId, gid));
  if (!sel) return Promise.resolve();

  limparSelect(baseId, gid);
  if (baseId === 'ano-linha') {
    const hasTodos = Array.prototype.slice.call(sel.options).some(o => o.value === 'todos');
    if (!hasTodos) {
      const optTodos = document.createElement('option'); optTodos.value = 'todos'; optTodos.textContent = 'Todos os anos'; sel.appendChild(optTodos);
    }
  }

  return fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=${entidade}&${new URLSearchParams(filtros)}`)
    .then(r => r.json())
    .then(lista => {
      lista.forEach(it => {
        const opt = document.createElement('option'); opt.value = it.id; opt.textContent = it.label; sel.appendChild(opt);
      });
      if (valorSel !== null) sel.value = String(valorSel);
      if (grupoMostrar) {
        const grp = document.getElementById(gidId(grupoMostrar, gid));
        if (grp) grp.classList.remove('oculto');
      }
    })
    .catch(() => mostrarAlerta('Erro ao carregar dados BNCC!', 'danger'));
}

function bindSequencialBNCC(gid) {
  // ETAPA → ANO
  const etapaSel = document.getElementById(gidId('etapa-linha', gid));
  if (etapaSel) etapaSel.onchange = function() {
    ['ano-linha','area-linha','componente-linha','unidadeTematica-linha','objetosConhecimento-linha','habilidades-linha']
      .forEach(base => limparSelect(base, gid));
    carregarSelectESelecionar('ano-linha', 'anos', {id_etapa: this.value}, null, 'grupo-ano', gid);
  };

  // ANO → ÁREA
  const anoSel = document.getElementById(gidId('ano-linha', gid));
  if (anoSel) anoSel.onchange = function() {
    ['area-linha','componente-linha','unidadeTematica-linha','objetosConhecimento-linha','habilidades-linha']
      .forEach(base => limparSelect(base, gid));
    carregarSelectESelecionar('area-linha', 'areas', {id_etapa: etapaSel.value}, null, 'grupo-area', gid);
  };

  // ÁREA → COMPONENTE
  const areaSel = document.getElementById(gidId('area-linha', gid));
  if (areaSel) areaSel.onchange = function() {
    ['componente-linha','unidadeTematica-linha','objetosConhecimento-linha','habilidades-linha']
      .forEach(base => limparSelect(base, gid));
    carregarSelectESelecionar('componente-linha', 'componentes', {id_area: this.value}, null, 'grupo-componente', gid);
  };

  // COMPONENTE → UNIDADE
  const compSel = document.getElementById(gidId('componente-linha', gid));
  if (compSel) compSel.onchange = function() {
    ['unidadeTematica-linha','objetosConhecimento-linha','habilidades-linha']
      .forEach(base => limparSelect(base, gid));
    carregarSelectESelecionar('unidadeTematica-linha', 'unidades_tematicas', {id_componente: this.value}, null, 'grupo-unidade', gid);
  };

  // UNIDADE → OBJETOS
  const uniSel = document.getElementById(gidId('unidadeTematica-linha', gid));
  if (uniSel) uniSel.onchange = function() {
    ['objetosConhecimento-linha','habilidades-linha']
      .forEach(base => limparSelect(base, gid));
    carregarSelectESelecionar('objetosConhecimento-linha', 'objetosConhecimento', {id_unidade_tematica: this.value}, null, 'grupo-objetos', gid);
  };

  // OBJETOS → HABILIDADES
  const objSel = document.getElementById(gidId('objetosConhecimento-linha', gid));
  if (objSel) objSel.onchange = function() {
    limparSelect('habilidades-linha', gid);
    fetch(`includes/action-planejamento-mensal.php?acao=bncc&campo=habilidades&id_objeto=${this.value}`)
      .then(r => r.json())
      .then(lista => {
        const ch = window.choicesHabilidadesMap[gid];
			if (ch) {
			  ch.clearChoices();
			  ch.setChoices(
				lista.map(it => ({ value: String(it.id), label: it.label, disabled: false })),
				'value','label', true
			  );
			 if (editandoLinhaId !== null) {
					ch.setChoiceByValue(linhasPlanejamento[editandoLinhaId].habilidades.map(String));
					}
			}
        const grp = document.getElementById(gidId('grupo-habilidades', gid));
        if (grp) grp.classList.remove('oculto');
      });
  };
}




