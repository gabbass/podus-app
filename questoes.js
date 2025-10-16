/*  js/questoes.js  –  Controle da listagem de questões via AJAX
 *  Autor: Universo Correções Rápidas
 */
 

/* ─── Estado global ─────────────────────────────────────────── */
let paginaAtual   = 1;
let totalPaginas  = 1;
let ordemAtual    = { campo: 'criacao', direcao: 'desc' };

/* ─── Utilidades -------------------------------------------------- */
function qs(id) { return document.getElementById(id); }

function escapeHTML(str){
  return String(str).replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}

/* ─── Monta linha da tabela -------------------------------------- */
function criaLinha(q) {
  const tr = document.createElement('tr');

  /* Coluna id (checkbox) */
  const tdId = document.createElement('td');
  tdId.textContent = q.id;
  tr.appendChild(tdId);

  /* Enunciado */
  const tdEnun = document.createElement('td');
  tdEnun.innerHTML = escapeHTML(q.enunciado);
  tr.appendChild(tdEnun);

  /* Matéria / Assunto */
  const tdMat  = document.createElement('td');
  tdMat.textContent = q.materia;
  tr.appendChild(tdMat);
  const tdAss  = document.createElement('td');
  tdAss.textContent = q.assunto;
  tr.appendChild(tdAss);

  /* Ações */
  const tdAcoes = document.createElement('td');
  if (q.pode_editar) {
      const btnEdit = document.createElement('button');
      btnEdit.textContent = 'Editar';
      btnEdit.className = 'btn btn-sm btn-primary';
      btnEdit.onclick = () => window.location = `includes/crud-questoes.php?id=${id}`;
      tdAcoes.appendChild(btnEdit);

      const btnDel = document.createElement('button');
      btnDel.textContent = 'Excluir';
      btnDel.className = 'btn btn-sm btn-danger ms-1';
      btnDel.onclick = () => excluirQuestao(q.id);
      tdAcoes.appendChild(btnDel);
  } else {
      tdAcoes.textContent = '—';
  }
  tr.appendChild(tdAcoes);

  return tr;
}

/* ─── Carrega página --------------------------------------------- */
async function carregarQuestoes(pag=1){
  try{
      const params = new URLSearchParams({
        acao:'listar',
        page: pag,
        ordenar_por: ordemAtual.campo,
        ordem: ordemAtual.direcao,
        materia: qs('filtroMateria').value || 0,
        assunto: qs('filtroAssunto').value || 0
      });
      const resp = await fetch(`includes/api-questoes.php?${params}`);
      const json = await resp.json();
      if (!json.sucesso) throw new Error(json.msg || 'Falha na API');

      /* Renderiza */
      const tbody = qs('tbodyQuestoes');
      tbody.innerHTML = '';
      json.questoes.forEach(q => tbody.appendChild(criaLinha(q)));

      /* Paginação */
      paginaAtual  = json.pagina;
      totalPaginas = json.paginas;
      renderizarPaginacao();
  } catch(e){
      console.error(e);
      mostrarAlerta('Erro ao carregar questões.', danger);
  }
}

/* ─── Paginação --------------------------------------------------- */
function renderizarPaginacao(){
  const ul = qs('paginacaoQuestoes');
  ul.innerHTML = '';

  for (let p=1; p<=totalPaginas; p++){
      const li = document.createElement('li');
      li.className = 'page-item' + (p === paginaAtual ? ' active' : '');
      const a = document.createElement('a');
      a.textContent = p;
      a.href = '#';
      a.className = 'page-link';
      a.onclick = (ev)=>{ ev.preventDefault(); carregarQuestoes(p); };
      li.appendChild(a);
      ul.appendChild(li);
  }
}

/* ─── Ordenação --------------------------------------------------- */
function mudarOrdenacao(campo){
  if (ordemAtual.campo === campo){
      ordemAtual.direcao = (ordemAtual.direcao === 'asc' ? 'desc' : 'asc');
  } else {
      ordemAtual = {campo,direcao:'asc'};
  }
  carregarQuestoes(1);
}

/* ─── Exclusão ---------------------------------------------------- */
async function excluirQuestao(id){
  if (!confirm('Confirma excluir esta questão?')) return;
  try{
      const form = new FormData();
      form.append('acao','excluir');
      form.append('id', id);

      const resp = await fetch('includes/api-questoes.php', {
          method:'POST', body:form
      });
      const json = await resp.json();
      if (!json.sucesso) throw new Error(json.msg||'Falha');
      carregarQuestoes(paginaAtual);
  } catch(e){
      console.error(e);
      mostrarAlerta('Erro ao excluir.',danger);
  }
}

/* ─── Inicializa -------------------------------------------------- */
document.addEventListener('DOMContentLoaded', ()=>{
  qs('btnFiltrar').onclick = ()=> carregarQuestoes(1);
  qs('btnLimpar').onclick  = ()=>{
      qs('filtroMateria').value = '';
      qs('filtroAssunto').value = '';
      carregarQuestoes(1);
  };
  carregarQuestoes(1);
});
