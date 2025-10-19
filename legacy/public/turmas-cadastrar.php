<?php 
require('conexao.php');

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (!empty($nome)) {
        try {
            $inserir = $conexao->prepare("INSERT INTO turmas (nome, login) VALUES (:nome, :login)");
            $inserir->bindValue(':nome', $nome);
            $inserir->bindValue(':login', $login);
            
            if ($inserir->execute()) {
                echo "<script>alert('Turma Cadastrada com sucesso!')</script>";
                echo "<script>window.location.href = 'pesquisar-turmas.php';</script>";
                exit();
            } else {
                echo "<script>alert('Erro ao cadastrar turma!')</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Erro no banco de dados: " . addslashes($e->getMessage()) . "')</script>";
        }
    } else {
        echo "<script>alert('Por favor, informe o nome da turma!')</script>";
    }
}
?>