<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - Universo do Saber</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos específicos para a página de login */
        .login-container {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(rgba(0, 87, 183, 0.8), rgba(0, 87, 183, 0.8)), url('images/login-bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            color: white;
        }
        
        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .login-left p {
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 500px;
        }
        
        .login-right {
            width: 450px;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-logo img {
            height: 80px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-title h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .login-title p {
            color: var(--text-light);
        }
        
        .login-form .form-group {
            margin-bottom: 25px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .login-form input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .login-form input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 87, 183, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn:hover {
            background-color: #00479e;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-left {
                padding: 40px 20px;
                text-align: center;
            }
            
            .login-right {
                width: 100%;
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1>Bem-vindo ao Portal do Aluno</h1>
            <p>Acesse seu ambiente virtual para acompanhar suas atividades, materiais de estudo e muito mais. Sua jornada educacional começa aqui!</p>
        </div>
        
        <div class="login-right">
            <div class="login-logo">
                <img src="img/logo.png" alt="Universo do Saber">
            </div>
            
            <div class="login-title">
                <h2>Área do Aluno</h2>
                <p>Informe sua matrícula para acessar o sistema</p>
            </div>
            
            <form class="login-form" action="" method="POST">
                <div class="form-group">
                    <label for="matricula">Número de Matrícula</label>
                    <input type="text" id="matricula" name="matricula" placeholder="Digite sua matrícula" required>
                </div>
                
                <button type="submit" class="login-btn">Entrar</button>
            </form>
            
            <div class="login-footer">
                <p>É professor? <a href="login-professor">Acesse aqui</a></p>
                <p>Problemas para acessar? <a href="suporte.php">Entre em contato com o suporte</a></p>
            </div>
        </div>
    </div>
</body>

</html>

<?php

require('conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matricula = filter_input(INPUT_POST, 'matricula', FILTER_SANITIZE_SPECIAL_CHARS);


    $stmt = $conexao->prepare("SELECT * FROM login WHERE matricula = :matricula");
    $stmt->bindValue(':matricula', $matricula);
    $stmt->execute();
    $stmt2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $qtde = $stmt->rowCount();
    
    $login_sessao = $stmt2[0]['login'];
    $nome_sessao = $stmt2[0]['nome'];
    $perfil_sessao = $stmt2[0]['perfil'];
    $escola_sessao = $stmt2[0]['escola'];
    $matricula_sessao = $stmt2[0]['matricula'];
    $turma_sessao = $stmt2[0]['turma'];


    if($qtde > 0){
        
        session_start();
        
        
        $_SESSION['perfil'] = $perfil_sessao;
        $_SESSION['matricula'] = $matricula_sessao;
        $_SESSION['turma'] = $turma_sessao;
        

            if($perfil_sessao == 'Aluno'){
                echo "<script>location.assign('dashboard-aluno')</script>";

            }
    }else{
        echo "<script>alert('Usuário ou Senha Incorretos!')</script>";
        echo "<script>location.assign('login-aluno')</script>";
    } 

}
?>
