<?php
require('sessao-professor.php');
$tituloPagina = "Planejamento Mensal - Universo do Saber"; include __DIR__ . '/head.php';
?>

<body>
   <!-- Menu-->
   <?php include __DIR__ . '/menu.php'; ?>
   
   <!-- Content -->
   <div class="main-content" id="main-content">
   
		<!-- Cabecalho -->
		<?php include __DIR__ . '/cabecalho.php'; ?>
		
		<div class="content-container" id="content-container">
		
			<!-- Conteúdo -->
			<div class="container">
				<div class="page-header">
				
					<!-- Intro -->
					<div class="page-title">
						<h1>Planejamento mensal</h1>
						<p>Visualize, edite ou exclua planejamentos mensais cadastrados</p>
					</div>
					<div class="btn-group">
						<button class="btn btn-primary" id="btnNovoPlanejamento">
						<i class="fas fa-plus"></i> Novo planejamento
						</button>
					</div>
				</div>
								
				<!-- Criar, Ler e Editar-->
				<div class="segundo-container oculto destaque" id="crudPlanejamentoMensalContainer">
					<?php include __DIR__ . '/crud-planejamento-mensal.php'; ?>
				</div>
        
				<!-- Filtros -->
				<div class="filtros-container">
					<?php include __DIR__ . '/filtro-planejamento-mensal.php'; ?>
				</div>
			
				<!-- Tabela de planejamentos -->
				<div class="table-container">
				   <?php include __DIR__ . '/lista-planejamento-mensal.php'; ?>
				</div>
			</div>
		</div>
	</div> 
	<!-- Scripts gerais-->
    <?php include __DIR__ . '/rodape.php'; ?>
	<?php include __DIR__ . '/modal-geral.php'; ?>
	
	<!-- Scripts especificos-->
	<script src="js/planejamento-mensal.js"></script> 
	<script src="js/openai.js"></script>
	<script src="js/ai-sugestao.js"></script>
	<script src="js/download-plano.js"></script>
</body>
<?php include __DIR__ . '/foot.php'; ?>
