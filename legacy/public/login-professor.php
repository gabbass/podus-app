<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Professor - Universo do Saber</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos específicos para a página de login do professor */
        .login-container {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }
        
        .login-left {
            flex: 1;
            background-color: #FFA500;
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
            color: #FFA500; /* Laranja do professor */
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .login-title p {
            color: #777;
        }
        
        .login-form .form-group {
            margin-bottom: 25px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
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
            border-color: #FFA500; /* Laranja do professor */
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background-color: #FFA500; /* Laranja do professor */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn:hover {
            background-color: #e69500;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: #FFA500; /* Laranja do professor */
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
            <h1>Bem-vindo ao Portal do Professor</h1>
            <p>Acesse sua área exclusiva para gerenciar suas turmas, planejar aulas, criar avaliações e acompanhar o desempenho dos alunos.</p>
        </div>
        
        <div class="login-right">
            <div class="login-logo">
                <img src="img/logo.png" alt="Universo do Saber">
            </div>
            
            <div class="login-title">
                <h2>Área do Professor</h2>
                <p>Informe seus dados de acesso</p>
            </div>
            
            <form class="login-form" action="login.php" method="post">
                <div class="form-group">
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" placeholder="Digite seu login" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="password-container">
                        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Entrar</button>
            </form>
            
            <div class="login-footer">
                <p><a href="esqueci-senha.php">Esqueceu sua senha?</a></p>
                <p>É aluno? <a href="login-aluno.php">Acesse aqui</a></p>
            </div>
        </div>
    </div>

    <script>
        // Mostrar/ocultar senha
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('senha');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>