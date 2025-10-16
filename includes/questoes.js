/* js/questoes.js — Banco de Questões */
/* ═════════ VARIÁVEIS GLOBAIS ═════════ */
let formQuestao      = null;
let modoCRUD         = 'criar';           // cria | editar | visualizar
const ST             = window.__qsState ||= { pag:1, pags:1, ord:{campo:'id',dir:'desc'} };

/* ═════════ UTIL / MODAL GERAL ═════════ */
const dom  = id => document.getElementById(id);
const $jq  = window.jQuery;
const qp   = k  => new URLSearchParams(location.search).get(k)||'';
const idURL= () => { const n=+qp('id'); return n>0?n:null; };
const esc  = s  => String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

function showModalGeral({ titulo='', corpo='', textoConfirmar='OK', onConfirm }) {
  const m = dom('modalGeral'); if (!m) return alert(corpo||titulo);
  dom('modalGeralLabel').textContent = titulo;
  dom('modalGeralBody').innerHTML    = corpo;
  const btn = dom('modalGeralConfirmar');
  btn.textContent = textoConfirmar;
  btn.onclick = () => { bootstrap.Modal.getInstance(m).hide(); onConfirm?.(); };
  bootstrap.Modal.getOrCreateInstance(m).show();
}
const alerta = (msg, ok=false) => showModalGeral({ titulo: ok?'Sucesso':'Aviso', corpo: msg });

/* ═════════ LISTAGEM ═════════ */
const linha = q => `<tr>
  <td>${q.id}</td>
  <td>${snippetHTML(q.questao)}</td>
  <td>${esc(q.materia)}</td>
  <td>${esc(q.assunto)}</td>
  <td>
    ${q.pode_editar
      ? `<button class="btn-action btn-edit" onclick="openQ(${q.id},'editar')"><i class="fas fa-edit"></i></button>
         <button class="btn-action btn-delete" onclick="delQ(${q.id})"><i class="fas fa-trash-alt"></i></button>`
      : ''}
    <button class="btn-action btn-view" onclick="openQ(${q.id},'visualizar')"><i class="fas fa-eye"></i></button>
  </td>
</tr>`;

async function listar(p=1){
  if(!dom('tbodyQuestoes')) return;
  
  const qs = new URLSearchParams({
    acao:'listar',page:p,
    ordenar_por:ST.ord.campo,ordem:ST.ord.dir,
    materia:dom('filtroMateria')?.value||'',
    assunto:dom('filtroAssunto')?.value||'',
    texto  : dom('filtroTexto')  ?.value || ''  
  });
  try{
    const j=await (await fetch(`includes/api-questoes.php?${qs}`)).json();
    if(!j.sucesso) throw j.msg;
    dom('tbodyQuestoes').innerHTML=j.questoes.map(linha).join('');
    ST.pag=j.pagina; ST.pags=j.paginas; paginar();
  }catch(e){ alerta(e); }
}

function paginar() {
  const ul = dom('paginacaoQuestoes');
  if (!ul) return;
  ul.innerHTML = '';

  const total     = ST.pags;
  const atual     = ST.pag;
  const tamanho   = 10;
  const grupoIni  = Math.floor((atual - 1) / tamanho) * tamanho + 1;
  const grupoFim  = Math.min(grupoIni + tamanho - 1, total);

  if (grupoIni > 1) {
    ul.insertAdjacentHTML('beforeend',
      `<li class="page-item">
         <a href="#" class="page-link" onclick="listar(${grupoIni - 1});return false;">&laquo;</a>
       </li>`);
  }
  for (let p = grupoIni; p <= grupoFim; p++) {
    ul.insertAdjacentHTML('beforeend',
      `<li class="page-item${p === atual ? ' active' : ''}">
         <a href="#" class="page-link" onclick="listar(${p});return false;">${p}</a>
       </li>`);
  }
  if (grupoFim < total) {
    ul.insertAdjacentHTML('beforeend',
      `<li class="page-item">
         <a href="#" class="page-link" onclick="listar(${grupoFim + 1});return false;">&raquo;</a>
       </li>`);
  }
}

window.sortQ = campo=>{
  ST.ord = (ST.ord.campo===campo)
    ? { campo, dir: ST.ord.dir==='asc'?'desc':'asc' }
    : { campo, dir: 'asc' };
  listar(1);
};

/* ═════════ CRUD ═════════ */
window.openQ = async (id, modo) => {
  const box = dom('boxCrud');
  if (!box) return;
  box.classList.remove('oculto');

  resetForm();
  modoCRUD = modo;
  updateHeader(modo);

  if (id) {                     // editar / visualizar
    await carregar(id);
  }

  if (modo === 'visualizar') {
    lockForm();
  } else {
    unlockForm();
  }
};

window.delQ  = id=>{
  showModalGeral({
    titulo:'Confirmar',
    corpo:'Excluir esta questão?',
    textoConfirmar:'Excluir',
    onConfirm: async ()=>{
      const fd=new FormData(); fd.append('acao','excluir'); fd.append('id',id);
      const j=await (await fetch('includes/api-questoes.php',{method:'POST',body:fd})).json();
      j.sucesso?listar(ST.pag):alerta(j.msg);
    }
  });
};

async function carregar(id){
  try{
    const j=await (await fetch(`includes/api-questoes.php?acao=buscar&id=${id}`)).json();
    if(!j.sucesso) throw j.msg;

    // 1) campos simples
    ['materia','assunto','grau_escolar','autor','fonte']
      .forEach(f=> dom(f).value = j.dado[f]||'');

    dom('idQuestao').value    = id;
    dom('resposta').value     = j.dado.resposta;
    dom('isRestrito').checked = !!j.dado.isRestrito;

    // 2) summernote: enunciado, alternativas e justificativa
    if($jq?.fn.summernote){
      $jq('#questao').summernote('code', j.dado.questao);
      ['A','B','C','D','E'].forEach(l=>
        $jq('#alternativa_'+l).summernote('code', j.dado['alternativa_'+l]||'')
      );
      // <-- nova linha para carregar a justificativa -->
      $jq('#justificativa').summernote('code', j.dado.justificativa||'');
    }

    // 3) preview de imagem (se existir)
    if(j.dado.imagem){
      const img = dom('previewImagemQuestao');
      if(img){
        img.src = '../' + j.dado.imagem;
        img.style.display = 'block';
      }
    }

    return true;
  }catch(e){
    alerta(e);
    return false;
  }
}

function resetForm(){
  formQuestao.reset();
  if($jq?.fn.summernote){
    $jq('.summernote').summernote('code','');
    $jq('.summernote-small').summernote('code','');
  }
  dom('previewImagemQuestao')?.classList.add('d-none');
  dom('idQuestao').value     = '';
  dom('isRestrito').checked  = false;
}

function lockForm() {
  [...formQuestao.elements].forEach(el=>{
    el.disabled = true;
    el.classList.add('bg-light');
  });
  dom('btnSalvarQuestao').style.display = 'none';
  $jq?.fn.summernote && $jq('.summernote, .summernote-small').summernote('disable');
}

function unlockForm() {
  [...formQuestao.elements].forEach(el=>{
    el.disabled = false;
    el.classList.remove('bg-light');
  });
  dom('btnSalvarQuestao').style.display = '';
  $jq?.fn.summernote && $jq('.summernote, .summernote-small').summernote('enable');
}

function updateHeader(modo){
  dom('tituloCrudQuestoes').textContent =
      modo==='editar'     ? 'Editar questão'
    : modo==='visualizar' ? 'Visualizar questão'
    : 'Nova questão';

  dom('subtituloCrudQuestoes').textContent =
      modo==='editar'     ? 'Editar'
    : modo==='visualizar' ? 'Visualização'
    : 'Criar';

  const btnEdit = dom('btnEditaVisualiza');
  const btnIA   = dom('btnGerarIA');
  if (btnEdit) btnEdit.classList.toggle('oculto', modo!=='visualizar');
  if (btnIA)   btnIA  .classList.toggle('oculto', modo!=='criar');
}

function salvar(ev){
  ev.preventDefault();

  // sincroniza conteúdo Summernote para o textarea
  if($jq?.fn.summernote)
    $jq('.summernote, .summernote-small').each(function(){
      this.value = $jq(this).summernote('code');
    });

  const fd = new FormData(ev.target);
  fd.append('acao', fd.get('id') ? 'editar' : 'criar');
  if(!dom('isRestrito').checked) fd.delete('isRestrito');

  fetch('includes/api-questoes.php',{ method:'POST', body:fd })
    .then(r=>r.json()).then(j=>{
      if(!j.sucesso) throw j.msg;
      alerta('Salvo',true);
      location='questoes.php';
    }).catch(e=>alerta(e));
}

/* ═════════ DOM READY ═════════ */
document.addEventListener('DOMContentLoaded',()=>{
  /* LISTA */
  if(dom('tabelaQuestoes')){
    dom('btnFiltrar')?.addEventListener('click',()=>listar(1));
    dom('btnLimpar') ?.addEventListener('click', ()=>{
      ['filtroMateria','filtroAssunto','filtroTexto']
        .forEach(id=> { const el=dom(id); if(el) el.value=''; });
      listar(1);
    });
    dom('btnNovaQuestao')?.addEventListener('click', () => openQ(null, 'criar'));
    listar();
  }

  /* CRUD */
  formQuestao = dom('formQuestao');
  if(formQuestao){
    // inicialização do Summernote
    $jq?.fn.summernote && $jq('.summernote').summernote({ height:180 });
    $jq?.fn.summernote && $jq('.summernote-small').summernote({
      height:120,
      toolbar:[ ['style',['bold','italic','underline']], ['para',['ul','ol']] ]
    });

    // preview de imagem ao selecionar
    dom('imagem')?.addEventListener('change', e=>{
      const f=e.target.files[0], img=dom('previewImagemQuestao');
      if(f){
        const rd=new FileReader();
        rd.onload=v=>{
          img.src=v.target.result;
          img.style.display='block';
        };
        rd.readAsDataURL(f);
      } else img.style.display='none';
    });

    // modo inicial (url ?editar : criar)
    const id = idURL();
    modoCRUD = qp('modo') || (id?'editar':'criar');
    if(id) carregar(id);

    // cabeçalho inicial
    dom('tituloCrudQuestoes').textContent =
      modoCRUD==='visualizar' ? 'Visualizar questão'
    : modoCRUD==='editar'     ? 'Editar questão'
    : 'Nova questão';

    // bind do submit
    if(modoCRUD !== 'visualizar') {
      formQuestao.addEventListener('submit',salvar);
    } else {
      lockForm();
      dom('btnCancelarQuestao')?.classList.add('d-none');
    }
	//Cancelar
	 const btnCancelarTudo = dom('btnCancelarTudo');
	  if (btnCancelarTudo) {
		btnCancelarTudo.addEventListener('click', e => {
		  e.preventDefault();
		  // 1) limpa todos os campos
		  resetForm();
		  // 2) oculta o box de CRUD
		  const box = dom('boxCrud');
		  if (box) box.classList.add('oculto');
		});
		}
    // cancelar
    dom('btnCancelarQuestao')?.addEventListener('click', e=>{
      e.preventDefault();
      dom('boxCrud')?.classList.add('oculto');
    });
  }
});

function snippetHTML(src, limite = 200) {
  if (!src) return '';
  const div = document.createElement('div');
  div.innerHTML = String(src).replace(/\n/g, '<br>');
  let html = div.innerHTML;

  if (html.length > limite) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    let txt = tmp.textContent.slice(0, limite) + '…';
    html = txt.replace(/\n/g, '<br>');
  }
  return html;
}
