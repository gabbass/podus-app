<?php 
require('sessao-professor.php');
require('dashboard_stats.php');
$tituloPagina = "Dashboard - Universo do Saber"; include dirname(__DIR__) . '/includes/head.php';
?>
<body>
	<?php include dirname(__DIR__) . '/includes/menu.php'; ?>
	<div class="main-content" id="main-content">
		<?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>
		<div class="content-container" id="content-container">
			<!-- Conteudo -->
			<div class="container">
				<div class="page-header">
					<div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Bem-vindo ao Portal Universo do Saber</p>
					</div>
				</div>
				
				<!-- Cards -->
				<div class="card-container">
				   <!-- Card 1 -->
					<div class="card">
						<div class="card-header" onclick="location.assign('questoes-professor')" >
							<div class="card-title">Questões</div>
							<div class="card-icon blue"><i class="fas fa-question-circle"></i></div>
						</div>
						<div class="card-body">
							<h2><?php echo $total_questoes; ?></h2>
							<p>Total de questões cadastradas</p>
						</div>
						
					</div>
					
					<!-- Card 2 -->
					<div class="card">
						<div class="card-header" onclick="location.assign('pesquisar-alunos')">
							<div class="card-title">Alunos</div>
							<div class="card-icon orange"><i class="fas fa-user-graduate"></i></div>
						</div>
						<div class="card-body">
							<h2><?php echo $total_alunos; ?></h2>
							<p>Total de alunos matriculados</p>
						</div>
						
					</div>
					
					<!-- Card 3 -->
					<div class="card">
						<div class="card-header">
							<div class="card-title" onclick="location.assign('pesquisar-turmas')">Turmas</div>
							<div class="card-icon green"><i class="fas fa-users"></i></div>
						</div>
						<div class="card-body">
							<h2><?php echo $total_turmas; ?></h2>
							<p>Turmas ativas</p>
						</div>
					
					</div>
				</div>
				<!-- Gráfico de Pizza - Alunos por Turma -->
				<div class="segundo-container">
					<div class="chart-header">
						<div class="chart-title">Distribuição de Alunos por Turma</div>
					</div>
					<div class="chart-wrapper">
						<canvas id="alunosTurmaChart"></canvas>
					</div>
				</div>
			</div>
        </div>
    </div>
	<!-- Passe dados PHP para o JS -->
	<script>
		window.turmas = <?php echo json_encode($turmas); ?>;
		window.quantidades = <?php echo json_encode($quantidades); ?>;
		window.cores = <?php echo json_encode($cores); ?>;
	</script>
		<!-- Scripts gerais-->
    <?php include dirname(__DIR__) . '/includes/rodape.php'; ?>
	<!-- Biblioteca ChartJS -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<!-- Script do Grafico-->
	<script src="js/grafico_pizza.js"></script> 

</body>
<?php include dirname(__DIR__) . '/includes/foot.php'; ?>