<?php 
require('sessao-professor.php');
require('includes/turmas.php');
?>

<body>
	<!-- Menu-->
    <?php include 'includes/menu.php'; ?>
    <!-- Content -->
	<div class="main-content" id="main-content">
		<!-- Título e Cabecalho -->
		<?php $tituloPagina = "Minhas turmas - Universo do Saber"; include 'includes/cabecalho.php'; ?>
		<div class="content-container" id="content-container">
			<!-- Conteúdo -->
			<div class="container">
				<!-- Intro -->
				<div class="page-header">
					<div class="page-title">
						<h1>Minhas turmas</h1>
						<p>Visualize, edite ou exclua turmas cadastradas</p>
					</div>
					<button type="button" class="btn btn-primary" onclick="abrirCadastroTurma()">
						<i class="fas fa-plus"></i> Nova Turma
					</button>
				</div>
				
        		<!-- Filtros -->
				<div class="filtros-container">
					<?php include 'includes/filtro-turmas.php'; ?>
				</div>
				
				<!-- Criar, Ler e Editar-->
				<div class="segundo-container oculto">
					<?php include 'includes/crud-turmas.php'; ?>
				</div>
				
				<!-- Tabela de turmas -->
				<div class="table-container">
						<div id="lista-turmas">
							<?php include 'includes/listar-turmas.php'; ?>
						</div>
				</div>
			</div>
		</div>	
	</div>
	<!-- Script de turmas-->
	<script src="js/turmas.js"></script> 
	
	<!-- Scripts geraais-->
     <?php include 'includes/rodape.php'; ?>
	 <?php include 'includes/modal-geral.php'; ?>
</body>
