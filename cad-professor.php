<?php
require('conexao.php');


$login_professor = $_SESSION['login'];

// Busca os dados do professor
$query = "SELECT * FROM login WHERE login = :login AND perfil = 'Professor'";
$stmt = $conexao->prepare($query);
$stmt->bindParam(':login', $login_professor);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou o professor, redireciona
if (!$professor) {
    header('Location: dashboard-professores');
    exit;
}

// Processa o formulário de edição
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($telefone) ) {
        $erro = 'Todos os campos são obrigatórios!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } elseif (!empty($nova_senha) && $nova_senha !== $confirmar_senha) {
        $erro = 'A nova senha e a confirmação não coincidem!';
    } else {
        try {
            // Atualiza os dados
            if (!empty($nova_senha)) {
                $query_update = "UPDATE login SET 
                                nome = :nome, 
                                email = :email, 
                                telefone = :telefone, 
                                senha = :senha 
                                WHERE id = :id";
                
                $senha_hash = md5($nova_senha);
            } else {
                $query_update = "UPDATE login SET 
                                nome = :nome, 
                                email = :email, 
                                telefone = :telefone 
                                WHERE id = :id";
            }
            
            $stmt_update = $conexao->prepare($query_update);
            $stmt_update->bindParam(':nome', $nome);
            $stmt_update->bindParam(':email', $email);
            $stmt_update->bindParam(':telefone', $telefone);
            $stmt_update->bindParam(':id', $professor['id'], PDO::PARAM_INT);
            
            if (!empty($nova_senha)) {
                $stmt_update->bindParam(':senha', $senha_hash);
            }
            
            if ($stmt_update->execute()) {
                $mensagem = 'Dados atualizados com sucesso!';
                // Atualiza os dados locais para exibir
                $professor['nome'] = $nome;
                $professor['email'] = $email;
                $professor['telefone'] = $telefone;

                if (!empty($nova_senha)) {
                    $professor['senha'] = $senha_hash;
                }
            } else {
                $erro = 'Erro ao atualizar os dados!';
            }
        } catch (PDOException $e) {
            $erro = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }
}
?>