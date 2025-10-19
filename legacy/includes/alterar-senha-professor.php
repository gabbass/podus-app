<?php
require(__DIR__ . '/../sessao-professor.php');
require(__DIR__ . '/../conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $login = $_SESSION['login'];

    $nova_senha      = trim($_POST['nova_senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

    if (empty($nova_senha) || empty($confirmar_senha)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos de senha!']);
        exit();
    }
    if ($nova_senha !== $confirmar_senha) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'As senhas não coincidem!']);
        exit();
    }

    try {
        $senha_hash = md5($nova_senha); // Recomendo password_hash() para futuro!
        $stmt = $conexao->prepare("UPDATE login SET senha = :senha WHERE login = :login AND perfil = 'Professor'");
        $stmt->bindValue(':senha', $senha_hash);
        $stmt->bindValue(':login', $login);
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso!']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar a senha!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
    }
    exit();
}
?>
