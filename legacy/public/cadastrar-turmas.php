<?php 
require('sessao-professor.php');
require('turmas-cadastrar.php');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Turma - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
    <div class="main-content">
        <?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>        
        <!-- Conteúdo -->
		<div class="content-container" id="content-container">
			<div class="container">
				<div class="page-title">
					<h1>Cadastrar Nova Turma</h1>
					<p>Preencha o nome da nova turma</p>
				</div>
				
				<div class="card-form">
					<form action="#" method="post">
						<div class="form-group">
							<label for="nome">Nome da Turma</label>
							<input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: 3º Ano A - Matutino" required>
						</div>
						<div class="form-actions">
							<button type="submit" class="btn btn-primary">
								<i class="fas fa-save"></i> Salvar Turma
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script src="pusaber.js"></script>
</body>
</html>