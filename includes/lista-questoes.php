<table id="tabelaQuestoes">
  <thead>
    <tr>
	  <th><input type="checkbox" id="checkTodos"></th>
      <th onclick="mudarOrdenacao('id')"       data-campo="id">ID</th>
      <th onclick="mudarOrdenacao('questao')"  data-campo="questao">Enunciado</th>
      <th onclick="mudarOrdenacao('materia')"  data-campo="materia">Matéria</th>
      <th onclick="mudarOrdenacao('assunto')"  data-campo="assunto">Assunto</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody id="tbodyQuestoes"></tbody>
</table>

<ul class="pagination" id="paginacaoQuestoes"></ul>
