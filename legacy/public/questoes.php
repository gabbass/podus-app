<?php
require_once __DIR__.'/sessao-adm-professor.php';

$tituloPagina = "Banco de Questões - Portal Universo do Saber";
include dirname(__DIR__) . '/includes/head.php';
?>

<body>
<?php include dirname(__DIR__) . '/includes/menu.php'; ?>

<div class="main-content" id="main-content">
  <?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>

  <div class="content-container" id="content-container">

    <!-- Cabeçalho interno -->
    <div class="container">
		<div class="page-header d-flex justify-content-between align-items-end">
			<div class="page-title">
			  <h1>Banco de Questões</h1>
			  <p>Visualize, crie e edite suas próprias questões.</p>
			</div>

			<div class="btn-group">
			  <button type="button" class="btn btn-primary" id="btnNovaQuestao">
				<i class="fas fa-plus"></i> Criar Nova Questão
			  </button>
			  <button class="btn btn-primary oculto" id="btnGerarProva">
				<i class="fas fa-file-alt"></i> Gerar prova (PDF)
			  </button>
			  <button class="btn btn-primary oculto" id="btnGerarProvaOnline">
				<i class="fas fa-link"></i> Prova on-line
			  </button>
			</div>
		  </div>
		

		<!-- CRUD embutido (inicialmente oculto) -->
		<div class="segundo-container oculto destaque" id="boxCrud">
		  <?php include dirname(__DIR__) . '/includes/crud-questoes.php'; ?>
		</div>

		<!-- Filtros -->
		<div class="filtros-container">
		  <?php include dirname(__DIR__) . '/includes/filtro-questoes.php'; ?>
		</div>

		<!-- Tabela -->
		<div class="table-container">
		  <?php include dirname(__DIR__) . '/includes/lista-questoes.php'; ?>
		</div>
	</div>
  </div><!-- /content-container -->
</div><!-- /main-content -->


<!-- variáveis globais JS -->
<script src="js/questoes.js"></script>
<script src="js/ia-questoes.js"></script>
<script src="js/gerar-prova-imp.js"></script>
</body>

<?php include dirname(__DIR__) . '/includes/rodape.php'; ?>
<?php include dirname(__DIR__) . '/includes/modal-geral.php'; ?>
