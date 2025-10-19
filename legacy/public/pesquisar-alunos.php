<?php 
require('sessao-professor.php');
require(dirname(__DIR__) . '/includes/alunos.php');
$tituloPagina = "Meus alunos - Universo do Saber"; include dirname(__DIR__) . '/includes/head.php';
?>
<body>
    <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
<div class="main-content" id="main-content">
	<?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>
	<div class="content-container" id="content-container">
		<div class="container">
		<div class="page-header">
			<div class="page-title">
                <h1>Meus alunos</h1>
                <p>Visualize, edite ou exclua alunos cadastrados</p>
			</div>
			<button type="button" class="btn btn-primary" onclick="abrirCadastroAluno()">
						<i class="fas fa-plus"></i> Novo aluno
			</button>
        	</div>
		
		<!-- Criar, Ler e Editar-->
		<div class="segundo-container oculto destaque">
			<?php include dirname(__DIR__) . '/includes/crud-aluno.php'; ?>
		</div>
		<!-- Filtros -->
        <div class="filtros-container">
            <?php include dirname(__DIR__) . '/includes/filtro-alunos.php'; ?>
        </div>
        <!-- Tabela de alunos -->
        <div class="table-container">
           <?php include dirname(__DIR__) . '/includes/listar-alunos.php'; ?>
        </div>
		</div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/rodape.php'; ?>
<script src="js/alunosc.js"></script>

</body>
</html>
<?php include dirname(__DIR__) . '/includes/foot.php'; ?>