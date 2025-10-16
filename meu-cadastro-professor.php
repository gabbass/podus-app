<?php
require('sessao-professor.php');
$tituloPagina = "Meu cadastro - Universo do Saber"; include 'includes/head.php';
?>

<body>
	<!-- Menu-->
    <?php include 'includes/menu.php'; ?>
	<!-- Content -->
    <div class="main-content" id="main-content">
	<!-- Cabecalho -->
	<?php include 'includes/cabecalho.php'; ?>
		<div class="content-container" id="content-container">
			<div class="container">
				<div class="page-title">
                    <h1>Meu Cadastro</h1>
                    <p>Atualize suas informações pessoais</p>
                </div>
			<div class="segundo-container">
			<form id="form-cadastro" class="form-bloco" method="POST" action="">
				<h3>Alterar Informações</h3>
                <p>Altere suas informacoes básicas</p>
				<div class="form-group">
					<label for="nome">Nome Completo</label>
					<input type="text" id="nome" name="nome" value="" required>
				</div>
				<div class="form-group">
					<label for="email">E-mail</label>
					<input type="email" id="email" name="email" value="" required>
				</div>
				<div class="form-group">
					<label for="telefone">Telefone</label>
					<input type="text" id="telefone" name="telefone" value="" required>
				</div>
				<div class="form-group">
					<label for="escola">Escola</label>
					<input type="text" id="escola" name="escola" value="" disabled>
				</div>
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-save"></i> Salvar Alterações
				</button>
			</form>
			</div>

			<div class="segundo-container">
			<form id="form-senha" method="POST" action="">			
                    <h3>Alterar Senha</h3>
                    <p>Preencha apenas se desejar alterar sua senha</p>
                    
                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Alterar senha
                    </button>
            </form>
			</div>
			</div>
        </div>
    </div>
 </div>

	<!-- Scripts gerais-->
     <?php include 'includes/rodape.php'; ?>
	 <!-- Scripts especificos-->
<script src="js/cadastro.js"></script> 

</body>		

<?php include 'includes/foot.php'; ?>