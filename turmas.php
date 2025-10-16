<?php
require('sessao-professor.php');
require('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);
    header('Content-Type: application/json');
    $login = $_SESSION['login'];

    // VERIFICA SE É EXCLUSÃO
    if (isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
        $id_turma = filter_input(INPUT_POST, 'id-turma', FILTER_SANITIZE_NUMBER_INT);

        if (empty($id_turma) || empty($login)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID ou login inválido!']);
            exit();
        }

        try {
            $excluir = $conexao->prepare("DELETE FROM turmas WHERE id = :id_turma AND login = :login");
            $excluir->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
            $excluir->bindValue(':login', $login);
            if ($excluir->execute()) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir turma!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
        }
        exit();
    }

    // EDIÇÃO OU CADASTRO (como já estava)
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_turma = filter_input(INPUT_POST, 'id-turma', FILTER_SANITIZE_NUMBER_INT);

    if (empty($nome) || empty($login)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Por favor, informe o nome da turma!']);
        exit();
    }

    try {
        if (!empty($id_turma)) {
            // Edição
            $atualizar = $conexao->prepare("UPDATE turmas SET nome = :nome WHERE id = :id_turma AND login = :login");
            $atualizar->bindValue(':nome', $nome);
            $atualizar->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
            $atualizar->bindValue(':login', $login);
            if ($atualizar->execute()) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar turma!']);
            }
        } else {
            // Cadastro
            $inserir = $conexao->prepare("INSERT INTO turmas (nome, login) VALUES (:nome, :login)");
            $inserir->bindValue(':nome', $nome);
            $inserir->bindValue(':login', $login);
            if ($inserir->execute()) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar turma!']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
    }
    exit();
}
?>
