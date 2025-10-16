<?php
require 'sessao-adm-professor.php';
require 'conexao.php';
$tituloPagina = "Provas - Portal Universo do Saber";
include 'includes/head.php';
?>
<body>
  <?php include 'includes/menu.php'; ?>
  <div class="main-content" id="main-content">
    <?php include 'includes/cabecalho.php'; ?>

    <div class="content-container" id="content-container">
      <div class="container">
        <!-- Cabeçalho original da página -->
        <div class="page-header d-flex justify-content-between align-items-end">
          <div class="page-title">
            <h1>Provas</h1>
            <p>Visualize, crie e edite suas provas.</p>
          </div>
		  <div class="btn-group">
		   <button type="button" class="btn btn-primary" id="btnNovaProva">
              <i class="fas fa-plus"></i> Nova Prova
            </button>
			</div>
        </div>

           <!-- Formulário CRUD (inicialmente oculto) -->
        <div id="boxCrudProva" class="segundo-container oculto destaque">
		<div id="crudLoading" class="overlay oculto">
  <div class="spinner-border" role="status"></div>
</div>
          <?php include 'includes/crud-provas.php'; ?>
        </div>

        <!-- Filtros -->
        <div class="filtros-container" id="filtrosContainerProvas">
          <?php include 'includes/filtro-provas.php'; ?>
        </div>

        <!-- Lista -->
        <div class="table-container" id="listaContainerProvas">
          <?php include 'includes/lista-provas.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="js/provas.js"></script>
</body>
<?php include 'includes/rodape.php'; ?>
<?php include 'includes/modal-geral.php'; ?>