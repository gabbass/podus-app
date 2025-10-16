<?php
require('conexao.php');
require('sessao-professor.php');

// Verifica se foi passado um ID válido para exclusão
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID de planejamento inválido.";
    header('Location: planejador.php');
    exit;
}

$id = $_GET['id'];

try {
    // Prepara e executa a consulta de exclusão
    $stmt = $conexao->prepare("DELETE FROM planejador WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Verifica se alguma linha foi afetada
        if ($stmt->rowCount() > 0) {
            
            echo "<script>alert('Planejamento excluído com sucesso!')</script>";
            echo "<script>location.assign('planejador')</script>";
            
        } else {
            $_SESSION['mensagem_erro'] = "Nenhum planejamento encontrado com o ID fornecido.";
        }
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao tentar excluir o planejamento.";
    }
    
} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao excluir planejamento: " . $e->getMessage();
}

// Redireciona de volta para a página principal
header('Location: planejador.php');
exit;
?>