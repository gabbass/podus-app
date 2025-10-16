<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require(__DIR__ . '/../sessao-professor.php');
require(__DIR__ . '/../conexao.php');

header('Content-Type: application/json');

$query = "SELECT * FROM login WHERE login = :login AND perfil = 'Professor'";
$stmt = $conexao->prepare($query);
$stmt->bindParam(':login', $login);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Para depuração
    // file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);

    $nome           = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email          = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone       = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $nova_senha     = trim($_POST['nova_senha'] ?? '');
    $confirmar_senha= trim($_POST['confirmar_senha'] ?? '');

    if (empty($nome) || empty($email) || empty($telefone)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos são obrigatórios!']);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail inválido!']);
        exit();
    }
    if (!empty($nova_senha) && $nova_senha !== $confirmar_senha) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'A nova senha e a confirmação não coincidem!']);
        exit();
    }

    try {
        if (!empty($nova_senha)) {
            $senha_hash = md5($nova_senha); // Manter md5 para legado
            $query_update = "UPDATE login SET 
                                nome = :nome, 
                                email = :email, 
                                telefone = :telefone, 
                                senha = :senha 
                            WHERE id = :id";
        } else {
            $query_update = "UPDATE login SET 
                                nome = :nome, 
                                email = :email, 
                                telefone = :telefone 
                            WHERE id = :id";
        }

        $stmt_update = $conexao->prepare($query_update);
        $stmt_update->bindValue(':nome', $nome);
        $stmt_update->bindValue(':email', $email);
        $stmt_update->bindValue(':telefone', $telefone);
        $stmt_update->bindValue(':id', $professor['id'], PDO::PARAM_INT);

        if (!empty($nova_senha)) {
            $stmt_update->bindValue(':senha', $senha_hash);
        }

        if ($stmt_update->execute()) {
            echo json_encode([
                'sucesso'  => true,
                'mensagem' => 'Dados atualizados com sucesso!'
            ]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar os dados!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
    }
    exit();
}

// Opcional: Resposta para requisições que não sejam POST
echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
exit();
?>