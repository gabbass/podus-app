<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecione Seu Perfil - Universo do Saber</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos específicos para a página de seleção */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }
        
        .profile-container {
            display: flex;
            height: 100vh;
        }
        
        .profile-option {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.5s ease;
            cursor: pointer;
        }
        
        .profile-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .profile-option:hover::before {
            background: rgba(0, 0, 0, 0.2);
        }
        
        .profile-option.student {
            background: linear-gradient(rgba(0, 87, 183, 0.7), rgba(0, 87, 183, 0.7)), url('images/student-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .profile-option.teacher {
            background: linear-gradient(rgba(255, 165, 0, 0.7), rgba(255, 165, 0, 0.7)), url('images/teacher-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .profile-content {
            position: relative;
            z-index: 2;
            padding: 0 30px;
            max-width: 500px;
            transform: translateY(0);
            transition: all 0.4s ease;
        }
        
        .profile-option:hover .profile-content {
            transform: translateY(-10px);
        }
        
        .profile-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .profile-option:hover .profile-icon {
            transform: scale(1.1);
        }
        
        .profile-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .profile-description {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .profile-btn {
            display: inline-block;
            padding: 15px 40px;
            background-color: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .profile-option:hover .profile-btn {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(5px);
        }
        
        .logo-corner {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 3;
        }
        
        .logo-corner img {
            height: 100px;
            cursor:pointer!important;

        }
        
        @media (max-width: 992px) {
            .profile-container {
                flex-direction: column;
                height: auto;
            }
            
            .profile-option {
                height: 50vh;
                padding: 60px 20px;
            }
            
            .profile-title {
                font-size: 2rem;
            }
            
            .logo-corner {
                top: 20px;
                left: 20px;

            }
            
            .logo-corner img {
                height: 40px;
            }
        }
        
        @media (max-width: 576px) {
            .profile-title {
                font-size: 1.8rem;
            }
            
            .profile-description {
                font-size: 1rem;
            }
            
            .profile-btn {
                padding: 12px 30px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Logo -->
        <div class="logo-corner" onclick="location.assign('index')">
            <img src="img/logo-hor-semi-branco.webp" alt="Universo do Saber">
        </div>
        
        <!-- Opção Aluno -->
        <a href="login-aluno.php" class="profile-option student">
            <div class="profile-content">
                <div class="profile-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h2 class="profile-title">Você é Aluno?</h2>
                <p class="profile-description">Acesse seu portal com sua matrícula e acompanhe suas atividades, notas e materiais de estudo.</p>
                <div class="profile-btn">Acessar como Aluno</div>
            </div>
        </a>
        
        <!-- Opção Professor -->
        <a href="login-professor.php" class="profile-option teacher">
            <div class="profile-content">
                <div class="profile-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h2 class="profile-title">Você é Professor?</h2>
                <p class="profile-description">Acesse sua área exclusiva para gerenciar turmas, planejar aulas e criar avaliações.</p>
                <div class="profile-btn">Acessar como Professor</div>
            </div>
        </a>
    </div>
</body>
</html>