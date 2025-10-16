<?php
require 'sessao-adm-professor.php';
require 'conexao.php';     
$tituloPagina = "Ver Prova - Portal Universo do Saber";
include 'includes/head.php';
?>
<body>
  <?php include 'includes/menu.php'; ?>
  <div class="main-content" id="main-content">
    <?php include 'includes/cabecalho.php'; ?>

    <div class="content-container" id="content-container">
      <div class="container">
        <!-- Cabeçalho original da página -->
        <div class="page-header">
          <div class="page-title">
            <h1>Ver Prova</h1>
            <p>Visualize e imprima a foto suas provas.</p>
          </div>
		  <div class="btn-group" id="grupoBotoesImpressao">
				<!-- Botões serão inseridos via JS -->
			</div>

		</div>
			<div id="cabecalho">
				 <?php include 'includes/provas-visualizar-cabecalho.php'; ?>
		   	</div>
			<div class="oculto" id="resposta-prova"></div>
			<div class="oculto" id="resposta-notas"></div>
        

      </div>
    </div>
  </div>

    <script src="/portal/js/visua-provas.js"></script>
</body>
<?php include 'includes/rodape.php'; ?>
<?php include 'includes/modal-geral.php'; ?>