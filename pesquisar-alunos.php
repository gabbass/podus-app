<?php 
require('sessao-professor.php');
require('includes/alunos.php');
$tituloPagina = "Meus alunos - Universo do Saber"; include 'includes/head.php';
?>
<body>
    <?php include 'includes/menu.php'; ?>
<div class="main-content" id="main-content">
	<?php include 'includes/cabecalho.php'; ?>
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
			<?php include 'includes/crud-aluno.php'; ?>
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
<script src="js/alunosc.js"></script>

</body>
</html>
<?php include 'includes/foot.php'; ?>