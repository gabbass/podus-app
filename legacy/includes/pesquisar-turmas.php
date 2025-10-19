<?php 
require('sessao-professor.php');
require(__DIR__ . '/turmas.php');
?>

<body>
	<!-- Menu-->
    <?php include __DIR__ . '/menu.php'; ?>
    <!-- Content -->
	<div class="main-content" id="main-content">
		<!-- Título e Cabecalho -->
		<?php $tituloPagina = "Minhas turmas - Universo do Saber"; include __DIR__ . '/cabecalho.php'; ?>
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
					<?php include __DIR__ . '/filtro-turmas.php'; ?>
				</div>
				
				<!-- Criar, Ler e Editar-->
				<div class="segundo-container oculto">
					<?php include __DIR__ . '/crud-turmas.php'; ?>
				</div>
				
				<!-- Tabela de turmas -->
				<div class="table-container">
						<div id="lista-turmas">
							<?php include __DIR__ . '/listar-turmas.php'; ?>
						</div>
				</div>
			</div>
		</div>	
	</div>
	<!-- Script de turmas-->
	<script src="js/turmas.js"></script> 
	
	<!-- Scripts geraais-->
     <?php include __DIR__ . '/rodape.php'; ?>
	 <?php include __DIR__ . '/modal-geral.php'; ?>
</body>
