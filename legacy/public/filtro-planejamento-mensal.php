<?php
$termo_pesquisa = $_GET['pesquisa'] ?? '';
?>
<h3>Filtrar</h3>
<p>Procure digitando um texto do que busca</p>
<form class="filtros-form" id="filtroPlanejamentosMensais">
  <div class="filtros-row">
		<div class="filtro-group">
		<label for="materia">Pesquisa</label>
		<input 
			type="text" 
			name="pesquisa" 
			class="search-input" 
			placeholder="Pesquisar por curso ou período..."
			value="<?php echo htmlspecialchars($termo_pesquisa); ?>">
		</div>
	</div>
	<div class="filtros-actions">
		<button type="submit" class="btn-filtrar">
			<i class="fas fa-search"></i> Filtrar
		</button>
		        <button type="button" class="btn-limpar" id="btnLimparFiltroMateriais">
            <i class="fas fa-xmark"></i> Limpar
        </button>
	</div>
</form>
<div class="contador-registros" id="contadorRegistrosMateriais">
    Exibindo <strong><?php echo $total_registros ?? 0; ?></strong> registro(s) encontrado(s)
</div>
