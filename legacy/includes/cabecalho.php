<?php
require('cabecalho-fun.php');
?>
<header class="top-nav">
	<!-- Botão de menu hamburguer para mobile e desktop -->
    <button class="menu-toggle" id="menu-toggle" aria-label="Alternar menu">
        <i class="fa fa-bars"></i>
    </button>
	
	<!-- Área de alerta -->
	<div id="alertas-area" class="oculto">
		 <?php if (isset($sucesso)): ?>
                    <div class="alert alert-success">
                        <?php echo $sucesso; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger">
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>
	</div>
	
	<!-- Área do usuário -->
	<div class="user-area">
		<div class="user-img">
			<img src="<?= $dataUri ?>" alt="Avatar" style="width:40px; height:40px; border-radius:50%;" />
		</div>
		<span class="user-name"><?= htmlspecialchars($nome); ?></span>
		<button class="user-dropdown-toggle" onclick="toggleUserMenu()" aria-label="Abrir menu do usuário">
			<i class="fa fa-chevron-down"></i>
		</button>
		<div id="user-menu" class="user-menu">
			<?php if ($perfil === 'Professor'): ?>
				<a href="meu-cadastro-professor.php"><i class="fa fa-user"></i> Meu Cadastro</a>
			<?php endif; ?>
				<a href="/termos-uso.html" target="_blank" rel="noopener"><i class="fa fa-file-contract"></i> Termos de Uso</a>
				<a href="/politica-privacidade.html" target="_blank" rel="noopener"><i class="fa fa-user-shield"></i> Política de Privacidade</a>
				<a href="#" id="btn-sair"><i class="fa fa-sign-out-alt"></i> Sair</a>
				
		</div>
	</div>
</header>

<!-- Scripts Cabecalho -->
<script src="js/sair.js"></script>
<script src="js/pusaber.js"></script>
<?php include __DIR__ . '/modal-geral.php'; ?>

