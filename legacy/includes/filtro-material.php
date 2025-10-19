<h3>Filtrar</h3>
<p>Busque por descrição, turma ou matéria</p>
<form id="filtrosFormMateriais" class="filtros-form" autocomplete="off">
    <div class="filtros-row">
        <div class="filtro-group">
            <label for="descricao-filtro">Descrição</label>
            <input type="text" id="descricao-filtro" name="descricao"
                   placeholder="Filtrar por descrição"
                   value="<?php echo htmlspecialchars($filtro_descricao ?? ''); ?>">
        </div>
        <div class="filtro-group">
            <label for="turma-filtro">Turma</label>
            <select id="turma-filtro" name="turma">
                <option value="">Todas</option>
            </select>
        </div>
        <div class="filtro-group">
            <label for="materia-filtro">Matéria</label>
            <select id="materia-filtro" name="materia">
                <option value="">Todas</option>
            </select>
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
