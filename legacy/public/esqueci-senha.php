<?php
require('conexao.php');

// Incluir a biblioteca PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    try {
        // Consulta o banco de dados
        $query = "SELECT login, senha FROM login WHERE email = :email AND perfil = 'Professor'";
        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);
            $login = $dados['login'];

            // Gera uma nova senha temporária
            $novaSenha = gerarSenha(8);
            $senhaSegura = password_hash($novaSenha, PASSWORD_DEFAULT);

            // Atualiza a senha no banco de dados
            $updateQuery = "UPDATE login SET senha = :senha WHERE email = :email";
            $updateStmt = $conexao->prepare($updateQuery);
            $updateStmt->bindParam(':senha', $senhaSegura);
            $updateStmt->bindParam(':email', $email);
            $updateStmt->execute();

            // Envia o e-mail com as novas credenciais
          if (enviarEmail($email, $login, $novaSenha)) {
    $mensagem = "<div class='alert alert-success'>Login e senha reenviados por e-mail!</div>";
} else {
    $mensagem = "<div class='alert alert-danger'>Erro ao enviar e-mail. Tente novamente mais tarde.</div>";
}
        } else {
            $mensagem = "<div class='alert alert-danger'>E-mail não encontrado ou não é um professor cadastrado.</div>";
        }

    } catch (PDOException $e) {
        $mensagem = "<div class='alert alert-danger'>Erro no banco de dados: " . $e->getMessage() . "</div>";
    }
}

function enviarEmail($email, $login, $senha) {
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->isSMTP();
        $mail->Host = 'smtp.titan.email';
        $mail->SMTPAuth = true;
        $mail->Username = 'contato@portaluniversodosaber.com.br';
        $mail->Password = 'jPpb0#4w@15';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('contato@portaluniversodosaber.com.br', 'Portal Universo do Saber - Esqueci a senha');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Recuperação de Senha - Universo do Saber') . '?=';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #0057b7;'>Recuperação de Senha</h2>
                <p>Segue abaixo seus dados de acesso atualizados:</p>
                <div style='background: #f5f7fa; padding: 15px; border-radius: 5px;'>
                    <p><strong style='color: #003d7a;'>Login:</strong> $login</p>
                    <p><strong style='color: #003d7a;'>Nova Senha:</strong> $senha</p>
                </div>
                <p style='margin-top: 20px;'>Para acessar o portal, clique no botão abaixo:</p>
                <a href='https://portaluniversodosaber.com.br/portal/login-professor.php' 
                   style='display: inline-block; background: #0057b7; color: white; 
                          padding: 10px 20px; text-decoration: none; border-radius: 5px;
                          margin: 15px 0;'>
                    Acessar Portal
                </a>
                <p style='margin-top: 20px; font-size: 0.9em; color: #6c757d;'>
                    Atenciosamente,<br>
                    Equipe Universo do Saber
                </p>
            </div>
        ";

        $mail->AltBody = "Recuperação de Senha\n\nLogin: $login\nNova Senha: $senha\n\nAcesse o portal em: https://portaluniversodosaber.com.br/portal/login-professor.php";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}

function gerarSenha($tamanho = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $senha;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
        }
        h1 {
            color: #0057b7;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #003d7a;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
            border-radius: 4px;
            font-size: 1rem;
			box-sizing: border-box; 
        }
        input[type="email"]:focus {
            border-color: #0057b7;
            outline: none;
        }
		
        .btn {
            background-color: #0057b7;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s;
        }
        .btn:hover {
            background-color: #003d7a;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
				
				.alert-success {
			background-color: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .text-center {
            text-align: center;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0057b7;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Recuperar Senha</h1>
        
        <?php echo $mensagem; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="email">E-mail cadastrado</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> Reenviar Senha
            </button>
        </form>
        
        <div class="text-center">
            <a href="login-professor.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar para o login
            </a>
        </div>
    </div>
</body>
</html>