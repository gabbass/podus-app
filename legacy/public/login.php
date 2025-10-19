<?php
require('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS));
    $senhaDigitada = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_SPECIAL_CHARS));

    $stmt = $conexao->prepare("SELECT * FROM login WHERE login = :login");
    $stmt->bindValue(":login", $login);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashArmazenado = $usuario['senha'];

        $loginValido = false;

        // Caso 1: hash moderno (password_hash)
        if (password_verify($senhaDigitada, $hashArmazenado)) {
            $loginValido = true;
        }
        // Caso 2: hash antigo em md5 (32 caracteres hexadecimais)
        elseif (strlen($hashArmazenado) === 32 && md5($senhaDigitada) === $hashArmazenado) {
            $loginValido = true;

            // Atualiza para hash moderno
            $novoHash = password_hash($senhaDigitada, PASSWORD_DEFAULT);
            $stmtAtualiza = $conexao->prepare("UPDATE login SET senha = :nova WHERE id = :id");
            $stmtAtualiza->bindParam(':nova', $novoHash);
            $stmtAtualiza->bindParam(':id', $usuario['id']);
            $stmtAtualiza->execute();
        }

        if ($loginValido) {
            session_start();

            $_SESSION['login'] = $usuario['login'];
            $_SESSION['perfil'] = $usuario['perfil'];
            $_SESSION['escola'] = $usuario['escola'];
            $_SESSION['nome'] = $usuario['nome'];

            switch ($usuario['perfil']) {
                case 'Administrador':
                    header('Location: dashboard');
                    break;
                case 'Professor':
                    header('Location: dashboard-professor');
                    break;
                case 'Aluno':
                    header('Location: dashboard-alunos');
                    break;
            }
            exit;
        } else {
            echo "<script>alert('Usuário ou senha incorretos!')</script>";
            echo "<script>location.assign('index')</script>";
        }
    } else {
        echo "<script>alert('Usuário ou senha incorretos!')</script>";
        echo "<script>location.assign('index')</script>";
    }
}
?>
