<h3>Filtrar</h3>
<p>Busque por descrição, turma ou matéria</p>
<form method="get" class="filtros-form" id="filtrosForm">
  <div class="filtros-row">
		<div class="filtro-group">
			<label for="matricula">Matrícula</label>
			<input type="text" id="matricula" name="matricula" placeholder="Filtrar por matrícula" value="<?php echo htmlspecialchars($filtro_matricula); ?>">
		</div>
		
		<div class="filtro-group">
			<label for="nome">Nome</label>
			<input type="text" id="nome" name="nome" placeholder="Filtrar por nome" value="<?php echo htmlspecialchars($filtro_nome); ?>">
		</div>
	</div>
	<div class="filtros-actions">
		<button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
    </div>     
</form>
<div class="contador-registros">
	Exibindo <strong><?php echo $total_registros; ?></strong> registro(s) encontrado(s)
</div>