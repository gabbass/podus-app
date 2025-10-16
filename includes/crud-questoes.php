<?php
require_once __DIR__.'/../sessao-adm-professor.php';
require_once __DIR__.'/../conexao-bncc.php';
if (!isset($pdo) && isset($conexao_bncc)) $pdo = $conexao_bncc;

try {
    $materias = $pdo->query("SELECT DISTINCT nome FROM bncc_componentes ORDER BY nome")
                    ->fetchAll(PDO::FETCH_COLUMN);
    $assuntos = $pdo->query("SELECT DISTINCT nome FROM bncc_objetos_conhecimento ORDER BY nome")
                    ->fetchAll(PDO::FETCH_COLUMN);
    $graus    = $pdo->query("SELECT DISTINCT nome FROM bncc_etapas ORDER BY nome")
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
   <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">

    <!-- Choices.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</head>
<div id="containerCrudQuestao">
	<div class="page-header d-flex justify-content-between align-items-end">
		<div class="page-title">
		  <h2 id="tituloCrudQuestoes">Nova questão</h2>
		  <p id="subtituloCrudQuestoes">Crie uma nova questão</p>
		</div>
		<div class="btn-group">
			<button type="button" class="btn btn-primary oculto" id="btnEditaVisualiza">
				<i class="fas fa-edit"></i> Editar
			</button>
			<button type="button" class="btn btn-primary oculto" id="btnGerarIA">
				<i class="fa-solid fa-wand-magic-sparkles"></i> Gerar por IA
			</button>
		</div>
	</div>
	<form id="formQuestao" enctype="multipart/form-data">
    <input type="hidden" id="idQuestao" name="id">

    <!-- Matéria / Assunto -->
    <div class="row g-3 mb-3">
      <div class="col-md form-group">
        <label for="materia" class="form-label">Matéria</label>
        <input list="dlMaterias" id="materia" name="materia" class="form-control" required>
        <datalist id="dlMaterias">
          <?php foreach ($materias as $m) echo "<option value=\"".htmlspecialchars($m)."\"></option>"; ?>
        </datalist>
      </div>
      <div class="col-md form-group">
        <label for="assunto" class="form-label">Assunto</label>
        <input list="dlAssuntos" id="assunto" name="assunto" class="form-control" required>
        <datalist id="dlAssuntos">
          <?php foreach ($assuntos as $a) echo "<option value=\"".htmlspecialchars($a)."\"></option>"; ?>
        </datalist>
      </div>
    </div>

    <!-- Grau / Resposta -->
    <div class="row g-3 mb-3">
		<div class="col-md form-group">
			<label for="grau_escolar" class="form-label">Nível de Ensino</label>
			<input list="dlGraus" id="grau_escolar" name="grau_escolar" class="form-control" required>
			<datalist id="dlGraus">
			  <?php foreach ($graus as $g) echo "<option value=\"".htmlspecialchars($g)."\"></option>"; ?>
			</datalist>
		</div>
		<div class="col-md form-group">
			<div class="mb-3 form-group">
			  <label for="status" class="form-label">Status</label>
			  <select id="status" name="status" class="form-control" required>
				<option value="ativo">Ativo</option>
				<option value="inativo">Inativo</option>
			  </select>
			</div>
			<div class="col-md form-check">
			<label class="form-check-label" for="isRestrito">Questão restrita (visível só ao autor)
			<input class="form-check-input" type="checkbox" id="isRestrito" name="isRestrito">
			</label>
			</div>
		</div>
	</div>

    <!-- Enunciado -->
    <div class="mb-3 form-group">
      <label for="questao" class="form-label">Enunciado</label>
      <textarea id="questao" name="questao" class="summernote" required></textarea>
    </div>

    <!-- Imagem -->
    <div class="mb-3 form-group">
      <label for="imagem" class="form-label">Imagem (opcional)</label>
      <input class="form-control" type="file" id="imagem" name="imagem" accept="image/*">
      <img id="previewImagemQuestao" class="preview-imagem mt-2"
           style="display:none;max-width:200px;" alt="Pré-visualização">
    </div>

    <!-- Alternativas -->
    <fieldset>
      <h3>Alternativas</h3>
      <div class="row form-group">
		  <?php foreach(['A','B','C','D','E'] as $i=>$l): ?>
			<div id="wrapper_alternativa_<?= $l ?>" class="form-group wrapper_alternativa">
			  <label for="alternativa_<?= $l ?>" class="form-label">Alternativa <?= $l ?></label>
			  <textarea
				id="alternativa_<?= $l ?>"
				name="alternativa_<?= $l ?>"
				class="summernote-small"
				<?= $i<4?'required':'' ?>>
			  </textarea>
			</div>
		  <?php endforeach; ?>
		</div>
    </fieldset>

    <!-- Restrição / Autor / Fonte -->
	
	
	<div class="row g-3 mb-3">
		<div class="col-md-6 form-group">
			<label for="resposta" class="form-label">Resposta Correta (A–E)</label>
			<select type="text" id="resposta" name="resposta" maxlength="1" class="form-control text-uppercase" required>
				
			</select>
			
		</div>
		
		<div class="col-md-6 form-group">
			<label for="justificativa" class="form-label">Justificativa</label>
			<textarea id="justificativa" name="justificativa" class="summernote"></textarea>
		</div>
    </div>
    
    <div class="row g-3 mb-3 form-group">
      <div class="col-md-6">
        <label for="autor" class="form-label" >Autor</label>
        <input class="form-control" type="text" id="autor" name="autor" value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-6">
        <label for="fonte" class="form-label">Fonte / Referência</label>
        <input class="form-control" type="text" id="fonte" name="fonte">
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary" id="btnSalvarQuestao">
		  <i class="fas fa-save"></i> Salvar</button>
	   <button type="button" id="btnCancelarTudo" class="btn btn-cancelar">
          <i class="fas fa-times"></i> Cancelar </button>
    </div>
  </form>
</div>
