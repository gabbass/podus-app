<h3>Filtrar</h3>
<p>Procure digitando um texto</p>
<form id="filtrosFormProvas" class="filtros-form" method="get">
  <div class="filtros-row">
    <div class="filtro-group">
      <label for="filtroTextoProvas">Buscar texto</label>
      <input type="text" id="filtroTextoProvas" name="filtroTexto" placeholder="Buscar por texto">
    </div>
  </div>
  <div class="filtros-actions">
    <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
    <button type="button" id="btnLimparFiltroProvas" class="btn-limpar"><i class="fas fa-xmark"></i> Limpar</button>
  </div>
</form>
<div class="contador-registros">
  Exibindo <strong id="totalRegistrosProvas"><?php echo $total_registros ?? 0; ?></strong> registro(s)
</div>