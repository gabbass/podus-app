<?php 
require('sessao-professor.php');
require('includes/alunos.php');

$tituloPagina = "Minhas turmas - Universo do Saber"; include 'includes/head.php';
?>

<body>
	<!-- Menu-->
    <?php include 'includes/menu.php'; ?>
    <!-- Content -->
	<div class="main-content" id="main-content">
		<!-- Cabecalho -->
		<?php include 'includes/cabecalho.php'; ?>
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
				
				<!-- Criar, Ler e Editar-->
				<div class="segundo-container oculto destaque">
					<?php include 'includes/crud-turmas.php'; ?>
				</div>
				
        		<!-- Filtros -->
				<div class="filtros-container">
					<?php include 'includes/filtro-turmas.php'; ?>
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
	
	<!-- Scripts gerais-->
    <?php include 'includes/rodape.php'; ?>
	<?php include 'includes/modal-geral.php'; ?>
	
	<!-- Scripts especificos-->
	<script src="js/turmas.js"></script> 
</body>
<?php include 'includes/foot.php'; ?>