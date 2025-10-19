<?php
require(__DIR__ . '/../sessao-professor.php');
require(__DIR__ . '/../conexao.php');

$login = $_SESSION['login'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $stmt = $conexao->prepare("SELECT nome, email, telefone, escola FROM login WHERE login = :login AND perfil = 'Professor'");
    $stmt->bindParam(':login', $login);
    $stmt->execute();
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($professor) {
        echo json_encode(['sucesso' => true, 'dados' => $professor]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Professor não encontrado']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $nome     = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($nome) || empty($email) || empty($telefone)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos são obrigatórios!']);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail inválido!']);
        exit();
    }

    try {
        $stmt = $conexao->prepare("UPDATE login SET nome = :nome, email = :email, telefone = :telefone WHERE login = :login AND perfil = 'Professor'");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telefone', $telefone);
        $stmt->bindValue(':login', $login);
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Dados atualizados com sucesso!']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar os dados!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
    }
    exit();
}

?>
