
<form method="get" class="filtros-form" id="filtrosForm">
    <div class="filtro-group">
        <label for="nome-filtro">Nome</label>
        <input type="text" id="nome-filtro" name="nome" placeholder="Filtrar por nome" value="<?php echo htmlspecialchars($filtro_nome ?? ''); ?>">
        <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
		<button type="button" class="btn-limpar" id="btnLimparFiltro" disabled>Limpar</button>

    </div>
</form>
<div class="contador-registros" id="contadorRegistros">
<div id="lista-turmas">
    Exibindo <strong><?php echo $total_registros ?? 0; ?></strong> registro(s) encontrado(s)</div>
</div>

