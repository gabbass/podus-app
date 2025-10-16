<?php
require('conexao.php');
require('sessao-professor.php');

// Verifica se foi passado um ID válido para exclusão
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID de planejamento inválido.";
    header('Location: planejador-mensal');
    exit;
}

$id = $_GET['id'];

try {
    // Prepara e executa a consulta de exclusão
    $stmt = $conexao->prepare("DELETE FROM planejadormensal WHERE id = :id AND login = :login");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':login', $_SESSION['login'], PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        // Verifica se alguma linha foi afetada
        if ($stmt->rowCount() > 0) {
            $_SESSION['mensagem_sucesso'] = "Planejamento excluído com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Nenhum planejamento encontrado ou você não tem permissão para excluí-lo.";
        }
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao tentar excluir o planejamento.";
    }
    
} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao excluir planejamento: " . $e->getMessage();
}

// Redireciona de volta para a página principal
header('Location: planejador-mensal');
exit;
?>