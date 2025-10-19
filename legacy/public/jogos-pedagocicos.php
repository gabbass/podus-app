<?php 
require('sessao-professor.php');//sessao
require(dirname(__DIR__) . '/includes/alunos.php'); //php de acoes principais
$tituloPagina = "Jogos Pedagógicos - Portal Universo do Saber"; //nome da pagina
include dirname(__DIR__) . '/includes/head.php';//cabecalho da pagina
?>
<!--Corpo-->
<body>
    <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
    <div class="main-content" id="main-content">
	<?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>
		<div class="content-container" id="content-container">
			<div class="container">
				<div class="page-title">
					<h1>Jogos pedagógicos</h1>
					<p>Página em projeto</p>
				</div>
			</div>
		</div>
	</div>
	<!-- Scripts gerais-->
    <?php include dirname(__DIR__) . '/includes/rodape.php'; ?>
	<?php include dirname(__DIR__) . '/includes/modal-geral.php'; ?>
	
	<!-- Scripts especificos-->
	<script src="js/turmas.js"></script> 
	
</body>

<!--Footer-->
<?php include dirname(__DIR__) . '/includes/foot.php'; ?>