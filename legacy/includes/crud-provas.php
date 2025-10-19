<?php
require_once __DIR__.'/../conexao-bncc.php';
if (!isset($pdo) && isset($conexao_bncc)) $pdo = $conexao_bncc;
$materias = $pdo
    ->query("SELECT DISTINCT nome FROM bncc_componentes ORDER BY nome")
    ->fetchAll(PDO::FETCH_COLUMN);
?>

<head>
    <!-- jQuery e Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- Choices.js CSS -->
   <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <!-- Choices.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</head>
<div id="containerCrudProva">
	<div class="page-title">
            <h2 id="tituloCrudProvas">Criar provas</h2>
            <p id="subtituloCrudProvas">Visualize, crie e edite suas provas.</p>
    </div>
  <form id="formProvas">
    <input type="hidden" id="idProva" name="id">
	
    <div class="row g-3 mb-3">
      <div class="col-md form-group">
        <label for="turma" class="form-label">Turma</label>
        <select id="turma" name="turma" class="form-control" required>
          <option value="">Selecione...</option>
          <?php foreach($turmas as $t): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md form-group">
        <label for="escola" class="form-label">Escola <small>(opcional)</small></label>
        <input type="text" id="escola" name="escola" class="form-control">
      </div>
    </div>

   <div class="col-md form-group">
      <label for="materia" class="form-label">Matéria</label>
      <select id="materia" name="materia" class="form-control" required>
        <option value="">Selecione...</option>
       <!-- opções serão preenchidas por AJAX em js/provas.js -->
     </select>
    </div>

    <div class="mb-3 form-group">
      <label for="lista_quest" class="form-label">Questões da Prova <br>
        <span class="text-muted">Segure Ctrl/Cmd e clique para selecionar mais</span>
      </label>
      <select id="lista_quest" name="lista_quest[]" class="form-control" multiple required size="7">
        <?php foreach($questoes as $q): ?>
        <option value="<?= $q['id'] ?>"><?= $q['id'] ?> - <?= esc(substr(strip_tags($q['questao']),0,40)) ?>…</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="text-end">
      <button type="submit" id="btnSalvarProva" class="btn btn-primary">
        <i class="fas fa-save"></i> Salvar
      </button>
      <button type="button" id="btnCancelarProva" class="btn btn-cancelar">
        <i class="fas fa-times"></i> Cancelar
      </button>
    </div>
  </form>
</div>