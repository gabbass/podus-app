<?php
require('sessao-professor.php');
require('conexao.php');

header('Content-Type: application/json');

// Verificar se o ID do material foi fornecido
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID inválido!']);
    exit();
}

$id = $_POST['id'];

try {
    // Primeiro obtemos o caminho do arquivo para excluí-lo fisicamente
    $query = "SELECT caminho_arquivo FROM materiais_pedagogicos 
              WHERE id = :id AND login_professor = :login";
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':login', $_SESSION['login']);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Material não encontrado!']);
        exit();
    }

    $material = $stmt->fetch(PDO::FETCH_ASSOC);

    // Excluir o arquivo físico se existir
    if (file_exists($material['caminho_arquivo'])) {
        unlink($material['caminho_arquivo']);
    }

    // Excluir o registro do banco de dados
    $query_delete = "DELETE FROM materiais_pedagogicos 
                    WHERE id = :id AND login_professor = :login";
    $stmt_delete = $conexao->prepare($query_delete);
    $stmt_delete->bindValue(':id', $id);
    $stmt_delete->bindValue(':login', $_SESSION['login']);
    $stmt_delete->execute();

    echo json_encode(['success' => true, 'message' => 'Material excluído com sucesso!']);
    exit();

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir material: ' . $e->getMessage()]);
    exit();
}
