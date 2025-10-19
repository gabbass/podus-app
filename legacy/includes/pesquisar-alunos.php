<?php 
require('sessao-professor.php');
require(__DIR__ . '/alunos.php');
$tituloPagina = "Meus alunos - Universo do Saber"; include __DIR__ . '/head.php';
?>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
<div class="main-content" id="main-content">
	<?php include __DIR__ . '/cabecalho.php'; ?>
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
            <?php include __DIR__ . '/filtro-alunos.php'; ?>
        </div>
        <!-- Tabela de alunos -->
        <div class="table-container">
           <?php include __DIR__ . '/listar-alunos.php'; ?>
        </div>
		</div>
    </div>
</div>
<?php include __DIR__ . '/rodape.php'; ?>
<script src="alunosc.js"></script>

</body>
</html>
<?php include __DIR__ . '/foot.php'; ?>