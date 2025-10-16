<?php
require('sessao-professor.php');
require('conexao.php');


$id_aluno = $_GET['id'];

// Verifica se o aluno existe e é realmente um aluno (perfil = 'Aluno')
try {
    $sql = "SELECT id FROM login WHERE id = :id AND perfil = 'Aluno'";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_aluno, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: pesquisar-alunos.php?erro=aluno_nao_encontrado");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao verificar aluno: " . $e->getMessage());
}

// Processa a exclusão
try {
    $sql = "DELETE FROM login WHERE id = :id AND perfil = 'Aluno'";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_aluno, PDO::PARAM_INT);
    $stmt->execute();
    
    // Redireciona com mensagem de sucesso
    header("Location: pesquisar-alunos.php?excluido=1");
    exit();
} catch (PDOException $e) {
    // Redireciona com mensagem de erro
    header("Location: pesquisar-alunos.php?erro=exclusao");
    exit();
}