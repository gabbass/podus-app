<?php
require 'sessao-adm-professor.php';
require 'conexao.php';
$tituloPagina = "Notas de provas - Portal Universo do Saber";
include dirname(__DIR__) . '/includes/head.php';
?>
<body>
  <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
  <div class="main-content" id="main-content">
    <?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>

    <div class="content-container" id="content-container">
      <div class="container">
        <!-- Cabeçalho original da página -->
        <div class="page-header">
			<div class="page-title">
				<h1>Notas de provas</h1>
				<p>Visualize as notas dos alunos</p>
			</div>
        </div>
        <!-- Filtros -->
        <div class="filtros-container" id="filtrosContainerProvas">
          <?php include dirname(__DIR__) . '/includes/filtro-notas-ap.php'; ?>
        </div>
        <!-- Lista -->
        <div class="table-container" id="listaContainerProvas">
          <?php include dirname(__DIR__) . '/includes/lista-notas-ap.php'; ?>
        </div>
        
    </div><!-- /.container -->
</div><!-- /.main-content -->
</div>
<!-- ===========================================================
     POP-UP (detalhamento das respostas)
=========================================================== -->
<div class="popup-bg" id="popup-bg">
    <div class="popup-content" id="popup-content">
        <span class="fechar" onclick="fecharPopup()">&times;</span>
        <div id="popup-load"  style="text-align:center;margin:50px;">Carregando...</div>
        <div id="popup-dados" style="display:none"></div>
    </div>
</div>
  <script src="js/notas-ap.js"></script>
</body>
<?php include dirname(__DIR__) . '/includes/rodape.php'; ?>
<?php include dirname(__DIR__) . '/includes/modal-geral.php'; ?>
