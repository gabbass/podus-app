<h3>Filtrar</h3>
<p>Procure digitando um texto do que busca</p>

<form id="formFiltroNotas" class="filtros-form" onsubmit="return false;">
	
	<div class="filtros-row">
		<div class="filtro-group">
			<label for="selectTurma">Turma</label>
			<select id="selectTurma" name="turma">
				<option value="">Carregando turmas…</option>
			</select>
		</div>

		<div class="filtro-group">
			<label for="inputBuscaNome">Buscar aluno</label>
			<input type="text"
				   id="inputBuscaNome"
				   name="nome"
				   placeholder="Nome do aluno">
		</div>

		<div class="filtro-group">
			<label for="selectMateria">Matéria</label>
			<select id="selectMateria" name="materia">
				<option value="">Selecione a turma primeiro</option>
			</select>
		</div>
	</div>

	<div class="filtros-actions">
		<button type="button" class="btn-filtrar" id="btnFiltrarNotas">
			<i class="fas fa-search"></i> Filtrar
		</button>
		
		<button type="button" class="btn-limpar" id="btnLimparNotas">
			<i class="fas fa-xmark"></i> Limpar
		</button>
	</div>
</form>

<div class="contador-registros" id="contadorRegistrosNotas" style="display:none;">
	Exibindo <strong id="qtdNotasEncontradas">0</strong> registro(s) encontrado(s)
</div>
