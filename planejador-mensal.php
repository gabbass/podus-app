<?php
require('sessao-adm-professor.php');
$tituloPagina = "Planejamento Mensal - Universo do Saber"; include 'includes/head.php';
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
				<div class="page-header">
				
					<!-- Intro -->
					<div class="page-title">
						<h1>Planejamento de aulas</h1>
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
					<?php include 'includes/crud-planejamento-mensal.php'; ?>
				</div>
        
				<!-- Filtros -->
				<div class="filtros-container">
					<?php include 'includes/filtro-planejamento-mensal.php'; ?>
				</div>
			
				<!-- Tabela de planejamentos -->
				<div class="table-container">
				   <?php include 'includes/lista-planejamento-mensal.php'; ?>
				</div>
			</div>
		</div>
	</div> 
	<!-- Scripts gerais-->
    <?php include 'includes/rodape.php'; ?>
	<?php include 'includes/modal-geral.php'; ?>
	
	<!-- Scripts especificos-->
	<script src="js/crud-linha.js"></script>
	<script src="js/openai.js"></script>
	<script src="js/ai-sugestao.js"></script>
	<script src="js/download-plano.js"></script>
	<script src="js/planejamento-mensal.js"></script> 
	<script src="js/imprimir-planejamento.js"></script>

</body>
<?php include 'includes/foot.php'; ?>
