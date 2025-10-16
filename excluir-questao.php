<?php
require('sessao-adm.php');
require('conexao.php');


$id_questao = $_GET['id'];

// Verifica se a questão existe
try {
    $sql = "SELECT id FROM questoes WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_questao, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: questoes");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao verificar questão: " . $e->getMessage());
}

// Processa a exclusão
try {
    $sql = "DELETE FROM questoes WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_questao, PDO::PARAM_INT);
    $stmt->execute();
    echo "<script>alert('Questão excluída com sucesso!')</script>";
    echo "<script>location.assign('pesquisar-questoes')</script>";
   
} catch (PDOException $e) {
    die("Erro ao excluir questão: " . $e->getMessage());
}
?>