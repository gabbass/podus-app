<?php 
require('sessao-adm.php');
require('conexao.php');

try {
    // Contar Professores (perfil Professor)
    $stmt_professores = $conexao->prepare("SELECT COUNT(*) AS total FROM login WHERE perfil = 'Professor'");
    $stmt_professores->execute();
    $professores = $stmt_professores->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar Questões
    $stmt_questoes = $conexao->prepare("SELECT COUNT(*) AS total FROM questoes");
    $stmt_questoes->execute();
    $questoes = $stmt_questoes->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar Artigos
    $stmt_artigos = $conexao->prepare("SELECT COUNT(*) AS total FROM artigo");
    $stmt_artigos->execute();
    $artigos = $stmt_artigos->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar Turmas
    $stmt_turmas = $conexao->prepare("SELECT COUNT(*) AS total FROM turmas");
    $stmt_turmas->execute();
    $turmas = $stmt_turmas->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar Alunos (perfil Aluno)
    $stmt_alunos = $conexao->prepare("SELECT COUNT(*) AS total FROM login WHERE perfil = 'Aluno'");
    $stmt_alunos->execute();
    $alunos = $stmt_alunos->fetch(PDO::FETCH_ASSOC)['total'];

} catch(PDOException $e) {
    echo "Erro ao buscar dados: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css ">
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

        /* Sidebar */
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

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }

        /* Top Navigation */
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
        .search-bar {
            display: flex;
            align-items: center;
        }
        .search-bar input {
            padding: 8px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            outline: none;
            width: 250px;
            transition: all 0.3s;
        }
        .search-bar input:focus {
            border-color: var(--primary-blue);
        }
        .search-bar button {
            background: none;
            border: none;
            margin-left: -30px;
            color: var(--dark-gray);
            cursor: pointer;
        }
        .user-area {
            display: flex;
            align-items: center;
        }
        .user-area .notification {
            position: relative;
            margin-right: 20px;
            color: var(--dark-gray);
            cursor: pointer;
        }
        .user-area .notification .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--primary-orange);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .user-area .user-img {
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
        .user-area .user-name {
            font-weight: 500;
            color: var(--dark-gray);
        }

        /* Content */
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

        /* Cards */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            cursor: pointer;
        }
        .card-title {
            font-size: 1rem;
            color: var(--dark-gray);
            font-weight: 500;
        }
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .card-icon.blue {
            background: linear-gradient(to right, var(--primary-blue), var(--dark-blue));
        }
        .card-icon.orange {
            background: linear-gradient(to right, var(--primary-orange), var(--dark-orange));
        }
        .card-icon.green {
            background: linear-gradient(to right, #28a745, #1e7e34);
        }
        .card-icon.purple {
            background: linear-gradient(to right, #6f42c1, #5a2d91);
        }
        .card-icon.cyan {
            background: linear-gradient(to right, #17a2b8, #117a8b);
        }
        .card-body h2 {
            font-size: 2.2rem;
            color: var(--dark-blue);
            margin-bottom: 5px;
        }

        @media (max-width: 1200px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
            .top-nav {
                position: sticky;
                top: 0;
                z-index: 100;
            }
        }
        @media (max-width: 768px) {
            .card-container {
                grid-template-columns: 1fr;
            }
            .top-nav {
                padding: 15px;
            }
            .search-bar {
                order: 2;
                width: 100%;
                margin-top: 15px;
            }
            .search-bar input {
                width: 100%;
            }
            .user-area {
                order: 1;
            }
            .content {
                padding: 15px;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
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
            <li><a href="pesquisar-professores"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
            <li><a href="pesquisar-questoes"><i class="fas fa-question-circle"></i> <span>Questões</span></a></li>
            <li><a href="pesquisar-artigos"><i class="fas fa-file-alt"></i> <span>Artigos</span></a></li>
            <li><a href="estatisticas" class="active"><i class="fas fa-chart-bar"></i> <span>Estatísticas</span></a></li>
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
            <div class="user-area">
                <div class="notification" style='display:none'>
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="user-img">AD</div>
                <div class="user-name">Admin</div>
            </div>
            
        </div>

        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Estatísticas Gerais</h1>
                    <p>Dados consolidados do sistema</p>
                </div>
            </div>

            <!-- Cards -->
            <div class="card-container">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Professores</div>
                        <div class="card-icon blue"><i class="fas fa-chalkboard-teacher"></i></div>
                    </div>
                    <div class="card-body">
                        <h2><?= htmlspecialchars($professores ?? 0) ?></h2>
                        <p>Professores cadastrados</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Questões</div>
                        <div class="card-icon orange"><i class="fas fa-question-circle"></i></div>
                    </div>
                    <div class="card-body">
                        <h2><?= htmlspecialchars($questoes ?? 0) ?></h2>
                        <p>Questões cadastradas</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Artigos</div>
                        <div class="card-icon green"><i class="fas fa-eye"></i></div>
                    </div>
                    <div class="card-body">
                        <h2><?= htmlspecialchars($artigos ?? 0) ?></h2>
                        <p>Artigos Cadastrados</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Turmas</div>
                        <div class="card-icon purple"><i class="fas fa-school"></i></div>
                    </div>
                    <div class="card-body">
                        <h2><?= htmlspecialchars($turmas ?? 0) ?></h2>
                        <p>Turmas cadastradas</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Alunos</div>
                        <div class="card-icon cyan"><i class="fas fa-user-graduate"></i></div>
                    </div>
                    <div class="card-body">
                        <h2><?= htmlspecialchars($alunos ?? 0) ?></h2>
                        <p>Alunos cadastrados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Menu toggle for mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
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