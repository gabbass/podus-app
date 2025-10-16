<?php 
require('sessao-professor.php');//sessao
require('includes/alunos.php'); //php de acoes principais
$tituloPagina = "Jogos Pedagógicos - Portal Universo do Saber"; //nome da pagina
include 'includes/head.php';//cabecalho da pagina
?>
<!--Corpo-->
<body>
    <?php include 'includes/menu.php'; ?>
    <div class="main-content" id="main-content">
	<?php include 'includes/cabecalho.php'; ?>
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
    <?php include 'includes/rodape.php'; ?>
	<?php include 'includes/modal-geral.php'; ?>
	
	<!-- Scripts especificos-->
	<script src="js/turmas.js"></script> 
	
</body>

<!--Footer-->
<?php include 'includes/foot.php'; ?>