// js/planejamento-mensal.js
// ─── Variáveis globais ───────────────────────────────────────
let idPlanejamentoMensalParaExcluir = null;
let formPlanejamentoMensal = null;
let choicesAnos = null;
let __HTML_ADD_LINHA__ = null;  // armazena o template bruto de add-linha.php





// ─── Função utilitária para exibir o modal-geral.php ─────────
function showModalGeral({ título = '', corpo = '', textoConfirmar = 'Confirmar', onConfirm }) {
  // 1) Referências
  const modalEl       = document.getElementById('modalGeral');
  const modalTitleEl  = document.getElementById('modalGeralLabel');
  const modalBodyEl   = document.getElementById('modalGeralBody');
  const btnConfirmar  = document.getElementById('modalGeralConfirmar');

  if (!modalEl || !modalTitleEl || !modalBodyEl || !btnConfirmar) {
    console.error('Modal geral não encontrado!');
    return;
  }

  // 2) Atualiza texto do título, corpo e botão confirmar
  modalTitleEl.textContent   = título;
  modalBodyEl.innerHTML      = corpo;
  btnConfirmar.textContent   = textoConfirmar;

  // 3) Remove listener antigo e adiciona o novo
  btnConfirmar.onclick = () => {
    // fecha o modal antes de executar callback
    bootstrap.Modal.getInstance(modalEl).hide();
    if (typeof onConfirm === 'function') onConfirm();
  };

  // 4) Exibe o modal via Bootstrap API
  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();
}


// ─── Inicialização ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  try {
    // 1) Carrega mapas da BNCC
    const respMaps = await fetch('includes/action-bncc.php');
    window.bnccMaps = await respMaps.json();

    // 2) Carrega template de linha (uma única vez)
    const respTpl = await fetch('includes/add-linha.php');
    __HTML_ADD_LINHA__ = await respTpl.text();

    // 3) Inicia o CRUD principal
    inicializarPlanejamentoMensal();
  } catch (err) {
    console.error('Erro na inicialização do planejamento:', err);
    mostrarAlerta('Falha ao carregar dados iniciais.', 'danger');
  }
});

// ─── Função principal de setup ─────────────────────────────────
async function inicializarPlanejamentoMensal() {
  formPlanejamentoMensal = document.getElementById('crudPlanejamentoMensalForm');
 
  
  const selectTempo = document.getElementById('tempo');
  if (selectTempo) {
    try {
      const resp = await fetch('includes/action-planejamento-mensal.php?acao=listar_ciclos');
      const lista = await resp.json();
      selectTempo.innerHTML = '<option value="" disabled selected>Selecione o tipo de ciclo</option>';
      lista.forEach(c => {
        const opt = document.createElement('option');
        opt.value       = c.id;
        opt.textContent = c.nome;  // ajuste de campo conforme seu JSON
        selectTempo.appendChild(opt);
		   });
    } catch {
      mostrarAlerta('Erro ao carregar tipos de ciclo!', 'danger');
    }
  }
  const inputIdPlano = document.getElementById('id-planejamento-mensal');
  const selectMateria = document.getElementById('materia');
	if (selectMateria) {
	  fetch('includes/action-planejamento-mensal.php?acao=materias_do_professor')
		.then(r => r.json())
		.then(lista => {
		  selectMateria.innerHTML = '<option value="" disabled selected>Selecione a matéria</option>';
		  lista.forEach(m => {
			const opt = document.createElement('option');
			opt.value       = m.id;
			opt.textContent = m.label;       // note: o JSON vem com campo 'label'
			selectMateria.appendChild(opt);
		  });
		  
		  selectMateria.dataset.populado = 'true';      // <- MARCA COMO PRONTO
			selectMateria.dispatchEvent(new Event('carregado'));
		})
		.catch(() => mostrarAlerta('Erro ao carregar matérias!', 'danger'));
	}



// ─── 1. Inicializa Choices.js no select de anos ───────────────
 const selectAnos = document.getElementById('anos-plano');
  if (window.Choices && selectAnos) {
    // Apenas atribui à variável já declarada no topo do arquivo
    choicesAnos = new Choices(selectAnos, { removeItemButton: true });
  }

// ─── 2. Inicializa Summernote nos objetivos ────────────────────
if (window.jQuery) {
  $('#objetivo_geral, #objetivo_especifico').summernote({ height: 140 });
}

  // Botão "Novo planejamento"
  document.getElementById('btnNovoPlanejamento')
          .addEventListener('click', e => {
    e.preventDefault();
	setTituloSubtituloCRUD('criar');
    abrirCadastroPlanejamentoMensal();
  });

  // Submissão do form (salvar cabeçalho)
  formPlanejamentoMensal.addEventListener('submit', e => {
    e.preventDefault();
    salvarCadastroPlanejamentoMensal();
  });

  // Botões "Editar" da listagem (ID baseado em dataset)
	 document.querySelectorAll('[data-action="editar-plano"]').forEach(btn => {
	  btn.onclick = () => {
		const id = parseInt(btn.getAttribute('data-id'), 10);
		editarPlanejamentoMensal(id);
	  };
	});

  // Botões "Excluir" da listagem
  document.querySelectorAll('[data-action="excluir-plano"]').forEach(btn => {
    const id = btn.dataset.id;
    btn.addEventListener('click', () => abrirModalExcluirPlanejamento(id));
  });
  

  // Quando muda o tempo → gera blocos
  selectTempo.addEventListener('change', () => {
  // 1) Pega o texto da option selecionada
  const texto = selectTempo.options[selectTempo.selectedIndex].text.trim().toLowerCase();

  // 2) Mapeia para número de grupos
  let qt;
  switch (texto) {
    case 'único':
      qt = 1;
      break;
    case 'anual':
      qt = 12;
      break;
    case 'semestral':
      qt = 2;
      break;
    case 'trimestral':
      qt = 4;
      break;
    case 'bimestral':
      qt = 6;
      break;
    default:
      qt = 0; // ou trate um erro
  }

  // 3) Gera os blocos
  gerarBlocos(qt);
});

  // Se estivermos em edição (campo oculto com valor), dispara edição automática
  if (inputIdPlano && inputIdPlano.value) {
	  
    editarPlanejamentoMensal(inputIdPlano.value);
  }
}
/* ---------------------------------------------------------------
 *  Percorre todos os blocos <div class="bloco-linha" …>
 *  e devolve array de objetos no formato exigido pelo PHP
 * --------------------------------------------------------------- */
function coletarLinhasPlanejamento() {
  const linhas = [];

  document.querySelectorAll('.bloco-linha').forEach(bloco => {
    const gid = bloco.dataset.grupo;                 // 0-based (0,1,2…)

    bloco.querySelectorAll('tbody tr').forEach(tr => {
      linhas.push({
		   id: tr.dataset.id || null,  
        etapa               : tr.dataset.etapa               || '',
        ano                 : tr.dataset.ano                 || '',
        area                : tr.dataset.area                || '',
        componenteCurricular: tr.dataset.componente          || '',
        unidadeTematicas    : tr.dataset.unidade             || '',
        objetosConhecimento : tr.dataset.objeto              || '',
        habilidades         : (tr.dataset.habilidades || '').split(',')
                               .map(h => h.trim())            // garante sem espaços
                               .filter(Boolean),
        conteudos           : tr.dataset.conteudos           || '',
        metodologias        : tr.dataset.metodologias        || '',
        grupo               : Number(gid) + 1                // 1,2,3…
      });
    });
  });

  return linhas;
}

// ─── Ações de CRUD de Cabeçalho ───────────────────────────────
function abrirCadastroPlanejamentoMensal() {
  // Limpa form e estado
  formPlanejamentoMensal.reset();
  rolarEDestacarPrimeiroDestaque();
  document.getElementById('tempo').disabled = false;
  choicesAnos?.removeActiveItems();
  document.querySelectorAll('.bloco-planejamento').forEach(el => el.remove());
  document.getElementById('crudPlanejamentoMensalContainer')
          .classList.remove('oculto');
}

async function salvarCadastroPlanejamentoMensal() {
  const linhasJson = JSON.stringify(coletarLinhasPlanejamento());
  if (linhasJson === '[]') {
    mostrarAlerta('Adicione ao menos uma linha de BNCC antes de salvar.', 'warning');
    return;
  }

  // Torna campos disabled enviáveis
  formPlanejamentoMensal.querySelectorAll('[disabled]')
                        .forEach(el => el.disabled = false);

  // Prepara FormData
  const fd = new FormData(formPlanejamentoMensal);
  fd.set('linhas_serializadas', linhasJson);
  fd.set('id-planejamento-mensal',
         document.getElementById('id-planejamento-mensal').value.trim());
  fd.set('anos_plano',
         (choicesAnos?.getValue(true) || []).join(','));
  if (window.jQuery) {
    fd.set('objetivo_geral',      $('#objetivo_geral').summernote('code'));
    fd.set('objetivo_especifico', $('#objetivo_especifico').summernote('code'));
  }
  fd.append('acao',
            fd.get('id-planejamento-mensal') ? 'editar' : 'criar');

  try {
    const r = await fetch('includes/action-planejamento-mensal.php',
                          { method: 'POST', body: fd });
    const j = await r.json();

    if (!j.sucesso) {
      mostrarAlerta(j.mensagem || 'Falha ao salvar.', 'warning');
      return;
    }

    // ① Guarda o ID retornado (ou o existente) antes do reset
    const idAtual = document.getElementById('id-planejamento-mensal').value || j.id;

    mostrarAlerta(j.mensagem, 'success');
    listarPlanejamentos();
    document.getElementById('crudPlanejamentoMensalContainer')
            .classList.add('oculto');

    // ② Reseta só campos visíveis, sem perder o ID
    formPlanejamentoMensal.reset();
    document.getElementById('id-planejamento-mensal').value = idAtual;

  } catch (e) {
    console.error('Erro no fetch:', e);
    mostrarAlerta('Erro de comunicação com o servidor.', 'danger');
  }
}



async function editarPlanejamentoMensal(id) {
  try {
	  
	const formWrapper = document.getElementById('formPlanejamentoWrapper');
	if (formWrapper) {
	  formWrapper.classList.remove('oculto');
	}
    // 0) Guarda o ID no hidden e atualiza título/subtítulo
    const inputId = document.getElementById('id-planejamento-mensal');
    if (inputId) inputId.value = id;
    setTituloSubtituloCRUD('editar');

    // 1) Busca os dados do servidor
    const resp = await fetch(`includes/action-planejamento-mensal.php?acao=buscar&id=${id}`);
    const json = await resp.json();
    if (!json.sucesso) {
      mostrarAlerta(json.mensagem, 'warning');
      return;
    }
    const cab = json.cabecalho;

    // 2) Exibe o formulário de edição
    document.getElementById('crudPlanejamentoMensalContainer')
            .classList.remove('oculto');

    // 3) Preenche campos de texto e número
    document.getElementById('nome-plano-mensal').value        = cab['nome-plano-mensal']      || '';
    document.getElementById('periodo_realizacao').value       = cab['periodo_realizacao']     || '';
    document.getElementById('numero_aulas_semanais').value    = cab['numero_aulas_semanais']  || '';

    // 4) Preenche/Torna readonly o select de tempo
    const selTempo = document.getElementById('tempo');
	const cicloId  = String(cab['tempo'] || '');   // garante string

	/* 4-A. se o ID existe entre as opções, seleciona normalmente */
	let opt = selTempo.querySelector(`option[value="${cicloId}"]`);

	/* 4-B. fallback: procura por texto (caso o BD grave "bimestral", etc.) */
	if (!opt) {
	  opt = Array.from(selTempo.options)
				 .find(o => o.textContent.trim().toLowerCase() === cicloId.toLowerCase());
	}

	if (opt) {
	  opt.selected = true;
	} else {
	  console.warn('Ciclo não encontrado no <select tempo>:', cicloId);
	}

	/* 4-C. agora, e só agora, bloqueia o campo */
	selTempo.disabled = true;

    // 5) Matéria (aguarda as options chegarem, dispara só uma vez)
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

    // 6) Anos do plano (Choices.js)
    if (choicesAnos) {
      choicesAnos.clearChoices();
      choicesAnos.removeActiveItems();
      // repopula opções (1º–9º)
      choicesAnos.setChoices(
        ['1º','2º','3º','4º','5º','6º','7º','8º','9º']
          .map(v=>({value:v,label:v,disabled:false})),
        'value','label',false
      );
      // marca os anos salvos
      const anos = String(cab['anos_plano']||'')
                    .split(',').map(a=>a.trim()).filter(Boolean);
      choicesAnos.setChoiceByValue(anos);
    }

    // 7) Objetivos (Summernote)
    $('#objetivo_geral').summernote('code',      cab['objetivo_geral']      || '');
    $('#objetivo_especifico').summernote('code', cab['objetivo_especifico'] || '');

    // 8) Linhas & Blocos
    linhasPlanejamento = Array.isArray(json.linhas) ? json.linhas.slice() : [];
    const mapa = { único:1, anual:12, semestral:2, trimestral:4, bimestral:6, mensal:12 };
    const txt = selTempo.selectedOptions[0]?.textContent.trim().toLowerCase();
    const qtd = mapa[txt] || 0;

    const dadosIniciais = Array(qtd).fill(null);
    linhasPlanejamento.forEach(l => {
      const idx = (Number(l.grupo) || 1) - 1;
      if (idx >= 0 && idx < qtd) dadosIniciais[idx] = l;
    });
	rolarEDestacarPrimeiroDestaque();
    gerarBlocos(qtd, dadosIniciais);
    // cada bloco injetado já chama renderizarTabelaLinhas(gid)

  } catch (err) {
    console.error('Erro ao carregar para edição:', err);
    mostrarAlerta('Falha ao carregar planejamento para edição.', 'danger');
  }
}

async function listarPlanejamentos(termoPesquisa = '') {
   const wrapper = document.getElementById('lista-planejamentos-mensais');
	if (!wrapper) return;                          // nada a renderizar nesta página

  try {
    /* 1. Busca o HTML da lista (já filtrado, se houver termo) */
    const resp = await fetch(
      `includes/lista-planejamento-mensal.php?pesquisa=${encodeURIComponent(termoPesquisa)}`
    );
		if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
		const html = await resp.text();

   /* 2. Substitui TODO o conteúdo atual ───────────────────── */
    wrapper.innerHTML = html;                   // ← duplicação resolvida

    /* 3. Reatribua os eventos dos botões recém-inseridos ───── */
    const tbody = wrapper.querySelector('#tbody-lista-planejamentos');

    tbody?.querySelectorAll('[data-action="editar-plano"]')
         .forEach(btn => {
           btn.onclick = () => editarPlanejamentoMensal(btn.dataset.id);
         });

    tbody?.querySelectorAll('[data-action="excluir-plano"]')
         .forEach(btn => {
           btn.onclick = () => abrirModalExcluirPlanejamento(btn.dataset.id);
         });

  } catch (e) {
    console.error('Erro em listarPlanejamentos():', e);
    mostrarAlerta('Falha ao carregar a lista de planejamentos.', 'danger');
  }
}
/* Torna global, caso o JS esteja em módulo */
window.listarPlanejamentos = listarPlanejamentos;

function abrirModalExcluirPlanejamento(id) {
  showModalGeral({
    título: 'Confirmação de Exclusão',
    corpo: '<p>Deseja realmente excluir este planejamento?</p>',
    textoConfirmar: 'Excluir',
    onConfirm: () => confirmarExclusaoAJAXPlanejamentoMensal(id)
  });
}

// Remova qualquer listener direto sobre #modalGeralConfirmar ou #modalExcluirPlanejamento

async function confirmarExclusaoAJAXPlanejamentoMensal(id) {
  try {
    const resp = await fetch('includes/action-planejamento-mensal.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `acao=excluir&id=${encodeURIComponent(id)}`
    });

    const json = await resp.json();
    if (json.sucesso) {
      mostrarAlerta(json.mensagem, 'success');
      const linha = document.getElementById(`linha-plano-${id}`);
      if (linha) linha.remove();
    } else {
      mostrarAlerta(json.mensagem, 'warning');
    }
  } catch (err) {
    console.error('Erro ao excluir planejamento:', err);
    mostrarAlerta('Falha ao excluir planejamento.', 'danger');
  }
}



// ─── Geração Dinâmica de Blocos ───────────────────────────────
/**
 * Gera N blocos de planejamento, injetando template de linha em cada um.
 * @param {number} quantidade — número de grupos
 * @param {Array<object>} dadosIniciais — array opcional de objetos { ...camposTmp } para preencher
 */
function gerarBlocos(quantidade, dadosIniciais = []) {
  const container = document.getElementById('blocos-planejamento');
  container.innerHTML = '';

  // Garante que dadosIniciais seja array de objetos com .grupo numérico
const dadosMapeados = Array.isArray(dadosIniciais)
  ? dadosIniciais
      .filter(d => d && typeof d.grupo !== 'undefined' && d.grupo !== null)
      .reduce((acc, d) => {
        const g = toInt(d.grupo);
        if (g > 0) acc[g] = d;
        return acc;
      }, {})
  : {};


  for (let gid = 1; gid <= quantidade; gid++) {
    const dadosDoGrupo = dadosMapeados[gid] || null;
    injetarBlocoVisual(gid, dadosDoGrupo);
  }
}


/**
 * Injeta um bloco do template de add-linha.php, sufixando IDs e names
 * e preenchendo selects via BNCC e dados iniciais (se houver).
 */
async function injetarBlocoVisual(gid, dados) {
  // 1) fetch do template cru
  const resp = await fetch(`includes/add-linha.php?gid=${gid}`);
  const html = await resp.text();
  const temp = document.createElement('div');
  temp.innerHTML = html.trim();
  const bloco = temp.firstElementChild;
  
 
 
  // 1-A) adapta chaves do JSON para *_tmp_<gid>
  if (dados) {
    dados[`etapa_tmp_${gid}`]               = dados.etapa;
    dados[`ano_tmp_${gid}`]                 = dados.ano;
    dados[`area_tmp_${gid}`]                = dados.area;
    dados[`componente_tmp_${gid}`]          = dados.componenteCurricular;
    dados[`unidadeTematica_tmp_${gid}`]     = dados.unidadeTematicas;
    dados[`objetosConhecimento_tmp_${gid}`] = dados.objetosConhecimento;
    dados[`habilidades_tmp_${gid}`]         = dados.habilidades;
    dados[`conteudos_linha_tmp_${gid}`]     = dados.conteudos;
    dados[`metodologias_linha_tmp_${gid}`]  = dados.metodologias;
  }

  // 2) Preenche selects BNCC
  ['etapa','ano','area','componente','unidadeTematica','objetosConhecimento']
    .forEach(ent => {
      const sel = bloco.querySelector(`#${ent}-linha-${gid}`);
      if (!sel) return;
      const valor = dados ? dados[`${ent}_tmp_${gid}`] : null;
      carregarOpcoesNoSelect(
        `${ent}-linha-${gid}`,
        ent === 'ano' ? 'anos' : ent,
        valor
      );
      // mostra o grupo correspondente
      const grp = document.getElementById(`grupo-${ent === 'componente' 
                        ? 'componente' 
                        : ent === 'unidadeTematica' 
                          ? 'unidade' 
                          : ent}-` + gid);
      if (grp) grp.classList.remove('oculto');
    });


  // 3) Preenche textareas (Conteúdos e Metodologias)
  if (dados) {
	  
	// ---------------------------------------------------------------------------
// 3. Bloco de habilidades (Choices.js)
// ---------------------------------------------------------------------------
		const ch = window.choicesHabilidadesMap[gid];
		if (ch) {
			// 3.1 Limpa as escolhas atuais
			ch.clearChoices();

			// 3.2 Constrói array de opções a partir do objeto bnccMaps.habilidades
			//     O objeto possui a forma { "EF01LP01": "Descrição da habilidade", ... }
			const objHab = window.bnccMaps['habilidades'] || {};   // garante objeto
			const listaHab = Object.entries(objHab).map(([id, nome]) => ({
				value : String(id),   // Choices requer value em string
				label : nome          // Texto que aparece ao usuário
			}));

			// 3.3 Carrega todas as habilidades na instância Choices
			ch.setChoices(listaHab, 'value', 'label', true); // replaceChoices = true

			// 3.4 Seleciona apenas as habilidades já salvas no back-end
			const habSalvas = dados[`habilidades_tmp_${gid}`] || [];
			ch.setChoiceByValue(habSalvas.map(String));

			// 3.5 Exibe o grupo de habilidades no formulário
			const grpHab = document.getElementById(`grupo-habilidades-` + gid);
			if (grpHab) grpHab.classList.remove('oculto');
		}

    // dentro de injetarBlocoVisual(), logo após container.appendChild(bloco)

// 3) Preenche e (re)inicializa Summernote nos textareas de Conteúdo e Metodologia
if (window.jQuery && $.fn.summernote) {
  const $cont = $('#conteudos-linha-'+gid);
  const $met  = $('#metodologias-linha-'+gid);

  // só inicializa se ainda não tiver sido
  if ($cont.next('.note-editor').length === 0) {
    $cont.summernote({ height: 140 });
  }
  if ($met.next('.note-editor').length === 0) {
    $met.summernote({ height: 140 });
  }

  // agora sim setamos o código vindo do back
  $cont.summernote('code',      dados[`conteudos_linha_tmp_${gid}`]  || '');
  $met .summernote('code',      dados[`metodologias_linha_tmp_${gid}`] || '');

} else {
  // fallback para plain textarea
  const txtCont = bloco.querySelector(`#conteudos-linha-${gid}`);
  const txtMet  = bloco.querySelector(`#metodologias-linha-${gid}`);
  if (txtCont) txtCont.value = dados[`conteudos_linha_tmp_${gid}`]     || '';
  if (txtMet)  txtMet.value  = dados[`metodologias_linha_tmp_${gid}`]  || '';
}

	
	
  }

  // 4) Insere o bloco no DOM
  const container = document.getElementById('blocos-planejamento');
  container.appendChild(bloco);

	//titulo
	const selectTempo = document.getElementById('tempo');
	if (selectTempo) {
		const tipoCiclo = selectTempo.options[selectTempo.selectedIndex].textContent.trim();
		setTituloAddLinhaMensal(tipoCiclo, gid);
	}


  // 5) Inicializa eventos do CRUD de linha
  if (window.crudLinha?.initBloco) {
    window.crudLinha.initBloco(bloco);
  }
  


  // 6) Desenha a tabela interna com as linhas existentes
  renderizarTabelaLinhas(gid);
  
  
  
}



/**
 * Popula um <select> de BNCC com base no objeto window.bnccMaps
 * @param {string} idSelect
 * @param {string} entidade — chave em bnccMaps (ex. "etapas", "areas", "anos", ...)
 * @param {string|number|null} valorSelecionado
 */
function carregarOpcoesNoSelect(idSelect, entidade, valorSelecionado = null) {
  const sel = document.getElementById(idSelect);
  if (!sel || !window.bnccMaps) return;
  const itens = window.bnccMaps[entidade] || [];
  sel.innerHTML = '<option value="">Selecione...</option>';
  itens.forEach(item => {
    const opt = document.createElement('option');
    opt.value = item.id;
    opt.textContent = item.label || item.nome;
    if (valorSelecionado != null) {
      // Para multi-select de habilidades, valorSelecionado pode ser array
      if (Array.isArray(valorSelecionado) && valorSelecionado.includes(item.id)) {
        opt.selected = true;
      } else if (item.id == valorSelecionado) {
        opt.selected = true;
      }
    }
    sel.appendChild(opt);
  });
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
    const id = `tituloAddLinhaMensal-${gid}`;
    const titulo = document.getElementById(id);
    if (!titulo) return;

    let texto = 'Adicionar aulas';

    if (tipoCiclo === 'Único') {
        texto = 'Adicionar aulas únicas';
    } else if (tipoCiclo === 'Anual') {
        const meses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];
        texto = `Adicionar aulas de ${meses[(gid - 1)] || ''}`;
    } else if (tipoCiclo === 'Bimestral') {
		console.log('Adicionado Bimestral');
        texto = `Adicionar aulas do Bimestre ${gid}`;
    } else if (tipoCiclo === 'Trimestral') {
		console.log('Adicionado Trimestral');
        texto = `Adicionar aulas do Trimestre ${gid}`;
    } else if (tipoCiclo === 'Semestral') {
		console.log('Adicionado Semestre');
        texto = `Adicionar aulas do Semestre ${gid}`;
    }
    titulo.textContent = texto;
}


document.addEventListener('DOMContentLoaded', function () {
  const btnCancelarTudo = document.getElementById('btnCancelarTudo');
  if (btnCancelarTudo) {
    btnCancelarTudo.addEventListener('click', function () {
      const container = document.getElementById('crudPlanejamentoMensalContainer');
      if (container) {
        container.classList.add('oculto');
        container.querySelectorAll('input, select, textarea').forEach(el => {
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else el.value = '';
        });
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const selectTempo = document.getElementById('tempo');
  const formWrapper = document.getElementById('formPlanejamentoWrapper');

  if (!selectTempo || !formWrapper) return;

  // Executa na troca de opção
  selectTempo.addEventListener('change', function () {
    if (selectTempo.value) {
      formWrapper.classList.remove('oculto');
	  rolarEDestacarPrimeiroDestaque();
    } else {
      formWrapper.classList.add('oculto');
    }
  });

  // Executa uma vez ao carregar a página (para estado inicial)
  if (selectTempo.value) {
    formWrapper.classList.remove('oculto');
	rolarEDestacarPrimeiroDestaque();
  } else {
    formWrapper.classList.add('oculto');
  }
});
