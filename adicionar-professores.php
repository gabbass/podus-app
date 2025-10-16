<?php

require('conexao.php');
require('sessao-adm.php');

// Incluir a biblioteca PHPMailer
use PHPMailer\PHPMailer\PHPMailerh;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Funções auxiliares
function gerarSenha($tamanho = 5) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyz1234567890';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $senha;
}

function gerarLogin($nome) {
    $primeiroNome = explode(' ', $nome)[0];
    $numeros = rand(1000, 9999);
    return strtolower($primeiroNome) . $numeros;
}


function enviarEmail($email, $login, $senha) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP da Hostinger
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP da Hostinger
        $mail->SMTPAuth = true;              // Habilitar autenticação
        $mail->Username = 'contato@heliosander.com.br'; // Seu e-mail completo
        $mail->Password = 'L=Pq;8|U1x';        // Senha do e-mail
        $mail->SMTPSecure = 'ssl';           // SSL obrigatório
        $mail->Port = 465;                   // Porta SSL
        
        // Remetente deve ser o mesmo e-mail da autenticação
        $mail->setFrom('contato@heliosander.com.br', 'Portal Universo do Saber');
        $mail->addAddress($email);           // Destinatário
        
        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Portal Universo do Saber';
       $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #0057b7;'>Seus dados de acesso ao Universo do Saber</h2>
        
        <div style='background: #f5f7fa; padding: 15px; border-radius: 5px;'>
            <p><strong style='color: #003d7a;'>Login:</strong> $login</p>
            <p><strong style='color: #003d7a;'>Senha:</strong> $senha</p>
        </div>
        
        <p style='margin-top: 20px;'>Para acessar o portal, clique no botão abaixo:</p>
        
        <a href='https://portaluniversodosaber.com.br/portal/login-professor' 
           style='display: inline-block; background: #0057b7; color: white; 
                  padding: 10px 20px; text-decoration: none; border-radius: 5px;
                  margin: 15px 0;'>
            Acessar Portal
        </a>
        
        <p>Se o botão não funcionar, copie e cole este link no seu navegador:<br>
        <span style='color: #0057b7;'>https://portaluniversodosaber.com.br/portal/login-professor</span></p>
        
        <p style='margin-top: 20px; font-size: 0.9em; color: #6c757d;'>
            Atenciosamente,<br>
            Equipe Portal Universo do Saber
        </p>
    </div>
";
        
        // Configurações adicionais
        $mail->SMTPDebug = 2;                // Habilita debug
        ob_start();                          // Inicia buffer para capturar debug
        $mail->send();
        $debugOutput = ob_get_clean();       // Captura o debug
        
        // Log do envio
        file_put_contents('mail_log.txt', date('Y-m-d H:i:s') . " - Enviado para $email\n$debugOutput\n", FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        // Log de erros
        $errorMsg = date('Y-m-d H:i:s') . " - Erro para $email: " . $e->getMessage() . "\n";
        file_put_contents('mail_errors.txt', $errorMsg, FILE_APPEND);
        return false;
    }
}
// Processar formulário
$mensagemSucesso = '';
$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $escola = $_POST['escola'] ?? '';
    $plano = $_POST['plano'] ?? '';
    
    $login = gerarLogin($nome);
    $senha = gerarSenha();
    $senhaMd5 = md5($senha); // Convertendo para MD5
    
    try {
        $query = "INSERT INTO login (nome, login, senha, perfil, email, telefone, escola, plano) 
                  VALUES (:nome, :login, :senha, 'Professor', :email, :telefone, :escola, :plano)";
        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':senha', $senhaMd5);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':escola', $escola);
        $stmt->bindParam(':plano', $plano);
        
        if ($stmt->execute()) {
            // Enviar e-mail com as credenciais
            $emailEnviado = enviarEmail($email, $login, $senha);
            
            $mensagemSucesso = "Professor cadastrado com sucesso!<br><br>
                               <strong>Login:</strong> $login<br>
                               <strong>Senha:</strong> $senha<br><br>
                               Estas informações foram enviadas para o e-mail do professor.";
            
            if (!$emailEnviado) {
                $mensagemSucesso .= "<br><span style='color:red'>Aviso: O e-mail não pôde ser enviado automaticamente. Por favor, envie manualmente as credenciais.</span>";
            }
        } else {
            $mensagemErro = "Erro ao cadastrar professor. Por favor, tente novamente.";
        }
    } catch (PDOException $e) {
        $mensagemErro = "Erro no banco de dados: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Professor - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0057b7;
            --primary-orange: #ffa500;
            --dark-blue: #003d7a;
            --dark-orange: #cc8400;
            --light-gray: #f5f7fa;
            --medium-gray: #e1e5eb;
            --dark-gray: #6c757d;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: var(--light-gray);
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }
        .sidebar-header {
            padding: 20px;
            background: linear-gradient(to right, var(--primary-blue), var(--dark-blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu li {
            list-style: none;
            margin-bottom: 5px;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--dark-gray);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(0, 87, 183, 0.1);
            color: var(--primary-blue);
            border-left: 4px solid var(--primary-blue);
        }
        .sidebar-menu a i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--dark-gray);
        }
        .user-area {
            display: flex;
            align-items: center;
        }
        .user-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        .user-name {
            font-weight: 500;
            color: var(--dark-gray);
        }
        .content {
            padding: 30px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }
        .page-title p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        .btn i {
            margin-right: 8px;
        }
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }
        .btn-secondary:hover {
            background-color: #d1d7e0;
        }
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-blue);
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-blue);
            outline: none;
        }
        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .btn-submit:hover {
            background-color: var(--dark-blue);
        }
        .btn-submit i {
            margin-right: 8px;
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
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .btn {
                margin-top: 15px;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-graduation-cap"></i>
            <h3>Universo do Saber</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard"><i class="fas fa-home"></i> <span>Início</span></a></li>
            <li><a href="#" class="active"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
            <li><a href="pesquisar-questoes"><i class="fas fa-question-circle"></i> <span>Questões</span></a></li>
            <li><a href="cadastrar-artigos"><i class="fas fa-file-alt"></i> <span>Artigos</span></a></li>
            <li><a href="estatisticas"><i class="fas fa-chart-bar"></i> <span>Estatísticas</span></a></li>
            <li><a href="sair"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Adicionar Professor</h1>
                    <p>Preencha os dados do novo professor</p>
                </div>
                <a href="dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <div class="form-container">
                <?php if (!empty($mensagemSucesso)): ?>
                    <div class="alert alert-success">
                        <?= $mensagemSucesso ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($mensagemErro)): ?>
                    <div class="alert alert-danger">
                        <?= $mensagemErro ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="escola">Escola</label>
                        <input list="escolas" id="escola" name="escola" required>
                        <datalist id="escolas">
                            <?php
                            $escolas = [
                                "Escola Estadual Professor João Silva",
                                "Colégio Dom Pedro II",
                                "Instituto Federal de Educação",
                                "Escola Municipal Ana Maria",
                                "Colégio Santa Maria",
                                "Escola Técnica Federal",
                                "Colégio Objetivo",
                                "Escola Nova Era",
                                "Colégio Progresso",
                                "Escola Modelo"
                            ];
                            
                            foreach ($escolas as $escola) {
                                echo "<option value=\"" . htmlspecialchars($escola) . "\">";
                            }
                            ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="plano">Plano</label>
                        <select id="plano" name="plano" required>
                            <option value="">Selecione um plano</option>
                            <option value="Mensal">Mensal</option>
                            <option value="Semestral">Semestral</option>
                            <option value="Anual">Anual</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Cadastrar Professor
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Menu toggle for mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            
            e.target.value = value;
        });

        // Fechar sidebar ao clicar fora em dispositivos móveis
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            if (window.innerWidth <= 1200 && 
                !sidebar.contains(event.target) && 
                event.target !== menuToggle && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>