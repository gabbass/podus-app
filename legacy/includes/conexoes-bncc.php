<?php
$host = "localhost";
$banco = "por17324_bncc";
$usuario = "por17324_puds_bncc";
$senha = "M9rEsPV7h41p";

try {
    $pdo_bncc = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Erros lançam exceção
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Mais fácil para processar
        ]
    );
} catch (PDOException $e) {
    die("Erro ao conectar ao banco BNCC: " . $e->getMessage());
}
?>
