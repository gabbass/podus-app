<?php 
require('sessao-professor.php');
require('includes/alunos.php');
$tituloPagina = "Meus alunos - Universo do Saber"; include 'includes/head.php';
?>
<body>
    <?php include 'menu.php'; ?>
<div class="main-content" id="main-content">
	<?php include 'cabecalho.php'; ?>
	<div class="content-container" id="content-container">
		<div class="container">
		<div class="page-header">
			<div class="page-title">
                <h1>Meus alunos</h1>
                <p>Visualize, edite ou exclua alunos cadastrados</p>
			</div>
            <a href="cadastrar-alunos" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Aluno
			</a>
		</div>
		<!-- Filtros -->
        <div class="filtros-container">
            <?php include 'includes/filtro-alunos.php'; ?>
        </div>
        <!-- Tabela de alunos -->
        <div class="table-container">
           <?php include 'includes/listar-alunos.php'; ?>
        </div>
		</div>
    </div>
</div>
<?php include 'includes/rodape.php'; ?>
<script src="alunosc.js"></script>

</body>
</html>
<?php include 'includes/foot.php'; ?>