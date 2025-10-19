<?php
session_start(); // Inicia a sessão
session_destroy(); // Destrói a sessão
$tituloPagina = "Até breve!"; include dirname(__DIR__) . '/includes/head.php';
echo "<script>location.assign('index-portal')</script>";
exit(); // Encerra o script
?>
<body>
<div class="main-content" id="main-content">
	<div class="content-container" id="content-container">
		<div class="page-header">
			<div class="page-title">
			<h1>Até breve!</h1>
			<p>Você está saindo. Esperemos te ver novamente em breve!</p>
			</div>
		</div>
	</div>
</div>
</body>
<?php include dirname(__DIR__) . '/includes/foot.php'; ?>