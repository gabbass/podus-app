<?php
require_once dirname(__DIR__) . '/public/sessao-adm-professor.php';
require_once dirname(__DIR__) . '/public/conexao.php';
if (!isset($pdo) && isset($conexao)) $pdo = $conexao;

try {
    $materias = $pdo->query("SELECT DISTINCT materia FROM questoes ORDER BY materia")
                    ->fetchAll(PDO::FETCH_COLUMN);
    $assuntos = $pdo->query("SELECT DISTINCT assunto FROM questoes ORDER BY assunto")
                    ->fetchAll(PDO::FETCH_COLUMN);
    $graus    = $pdo->query("SELECT DISTINCT grau_escolar FROM questoes ORDER BY grau_escolar")
                    ->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $materias = $assuntos = $graus = [];
}
?>
<head>
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- jQuery e Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <!-- Choices.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</head>
<div class="content-container sub-container" id="containerCrudQuestao">
  <h2 id="tituloCrudQuestoes">Nova questão</h2>

  <form id="formQuestao" enctype="multipart/form-data">
    <input type="hidden" id="idQuestao" name="id">

    <!-- Matéria / Assunto -->
    <div class="row g-3 mb-3">
      <div class="col-md">
        <label for="materia" class="form-label">Matéria</label>
        <input list="dlMaterias" id="materia" name="materia" class="form-control" required>
        <datalist id="dlMaterias">
          <?php foreach ($materias as $m) echo "<option value=\"".htmlspecialchars($m)."\"></option>"; ?>
        </datalist>
      </div>
      <div class="col-md">
        <label for="assunto" class="form-label">Assunto</label>
        <input list="dlAssuntos" id="assunto" name="assunto" class="form-control" required>
        <datalist id="dlAssuntos">
          <?php foreach ($assuntos as $a) echo "<option value=\"".htmlspecialchars($a)."\"></option>"; ?>
        </datalist>
      </div>
    </div>

    <!-- Grau / Resposta -->
    <div class="row g-3 mb-3">
      <div class="col-md">
        <label for="grau_escolar" class="form-label">Nível de Ensino</label>
        <input list="dlGraus" id="grau_escolar" name="grau_escolar" class="form-control" required>
        <datalist id="dlGraus">
          <?php foreach ($graus as $g) echo "<option value=\"".htmlspecialchars($g)."\"></option>"; ?>
        </datalist>
      </div>
      <div class="col-md">
        <label for="resposta" class="form-label">Resposta Correta (A–E)</label>
        <input type="text" id="resposta" name="resposta" maxlength="1"
               class="form-control text-uppercase" required>
      </div>
    </div>

    <!-- Enunciado -->
    <div class="mb-3">
      <label for="questao" class="form-label">Enunciado</label>
      <textarea id="questao" name="questao" class="summernote" required></textarea>
    </div>

    <!-- Imagem -->
    <div class="mb-3">
      <label for="imagem" class="form-label">Imagem (opcional)</label>
      <input class="form-control" type="file" id="imagem" name="imagem" accept="image/*">
      <img id="previewImagemQuestao" class="preview-imagem mt-2"
           style="display:none;max-width:200px;" alt="Pré-visualização">
    </div>

    <!-- Alternativas -->
    <fieldset class="mb-3">
      <legend>Alternativas</legend>
      <div class="row g-3">
        <?php foreach(['A','B','C','D','E'] as $i=>$l): ?>
          <div class="col-md-6">
            <label for="alternativa_<?= $l ?>" class="form-label">Alternativa <?= $l ?></label>
            <textarea id="alternativa_<?= $l ?>" name="alternativa_<?= $l ?>"
                      class="summernote-small" <?= $i<4?'required':'' ?>></textarea>
          </div>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <!-- Restrição / Autor / Fonte -->
    <div class="mb-3 form-check">
      <input class="form-check-input" type="checkbox" id="isRestrito" name="isRestrito">
      <label class="form-check-label" for="isRestrito">Questão restrita (visível só ao autor)</label>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label for="autor" class="form-label">Autor</label>
        <input class="form-control" type="text" id="autor" name="autor">
      </div>
      <div class="col-md-6">
        <label for="fonte" class="form-label">Fonte / Referência</label>
        <input class="form-control" type="text" id="fonte" name="fonte">
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary" id="btnSalvarQuestao">Salvar</button>
    </div>
  </form>
</div>
