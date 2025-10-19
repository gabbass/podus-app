<h3>Filtrar</h3>
<p>Procure digitando um texto do que busca</p>
<form class="filtros-form"method="get" class="filtros-form" id="filtrosForm">
	<div class="filtros-row">
		<div class="filtro-group">
			<label for="assunto">Buscar texto</label>
			<input type="text" id="filtroTexto" name="filtroTexto" placeholder="Buscar por texto">
		</div>
    	<div class="filtro-group">
			<label for="materia">Matéria</label>
			<input type="text" id="materia" name="materia" placeholder="Filtrar por matéria ou ID" value="<?php echo htmlspecialchars($filtro_materia); ?>">
		</div>
        <div class="filtro-group">
			<label for="assunto">Assunto</label>
			<input type="text" id="assunto" name="assunto" placeholder="Filtrar por assunto ou nível de ensino" value="<?php echo htmlspecialchars($filtro_assunto); ?>">
		</div>
    </div>             
	<div class="filtros-actions">
        <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
		
		<button type="button" class="btn-limpar"><i class="fas fa-xmark"></i> Limpar</button>
    </div>          
</form>
           
<div class="contador-registros">
	Exibindo <strong><?php echo $total_registros; ?></strong> registro(s) encontrado(s)
</div>