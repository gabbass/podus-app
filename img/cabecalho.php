<?php
require('cabecalho-fun.php');
?>

<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $tituloPagina ?? 'Portal Universo do Saber' ?></title>

		<!-- Favicon tradicional (browsers desktop) -->
		<link rel="icon" href="img/favicon.ico" type="image/x-icon">

		<!-- PNGs para navegadores modernos -->
		<link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
		<link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">

		<!-- Favicon para dispositivos Apple (iOS/iPadOS) -->
		<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">

		<!-- Favicons para Android/Chrome -->
		<link rel="icon" type="image/png" sizes="192x192" href="img/android-chrome-192x192.png">
		<link rel="icon" type="image/png" sizes="512x512" href="img/android-chrome-512x512.png">


		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
		<link rel="stylesheet" href="pustyle.css">
	</head>

<header class="top-nav">
	<!-- Botão de menu hamburguer para mobile e desktop -->
    <button class="menu-toggle" id="menu-toggle" aria-label="Alternar menu">
        <i class="fa fa-bars"></i>
    </button>
	<!-- Área de alerta -->
	<div id="alertas-area">
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
			<a href="sair.php"><i class="fa fa-sign-out-alt"></i> Sair</a>
		</div>
	</div>
</header>
<script src="pusaber.js"></script>
