<?php
require('conexao.php');

// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pesquisar-professores.php');
    exit;
}

$id_professor = $_GET['id'];

// Busca os dados do professor para confirmar
$query = "SELECT * FROM login WHERE id = :id AND perfil = 'Professor'";
$stmt = $conexao->prepare($query);
$stmt->bindParam(':id', $id_professor, PDO::PARAM_INT);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou o professor, redireciona
if (!$professor) {
    header('Location: pesquisar-professores.php');
    exit;
}

// Processa a exclusão se confirmada
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'sim') {
        try {
            $query_delete = "DELETE FROM login WHERE id = :id";
            $stmt_delete = $conexao->prepare($query_delete);
            $stmt_delete->bindParam(':id', $id_professor, PDO::PARAM_INT);
            
            if ($stmt_delete->execute()) {
                $mensagem = 'Professor excluído com sucesso!';
                // Redireciona após 2 segundos
                header('Refresh: 2; URL=pesquisar-professores.php');
            } else {
                $erro = 'Erro ao excluir o professor!';
            }
        } catch (PDOException $e) {
            $erro = 'Erro no banco de dados: ' . $e->getMessage();
        }
    } else {
        // Se não confirmou, volta para a lista
        header('Location: pesquisar-professores.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Professor - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Mantém o mesmo estilo da página de pesquisa */
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
        }
        
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
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
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
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
            margin-left: 250px;
        }
        
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .user-area {
            display: flex;
            align-items: center;
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
        
        .content {
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--dark-blue);
            font-weight: 600;
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
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .confirmation-box {
            text-align: center;
            padding: 20px;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .professor-info {
            margin-bottom: 20px;
        }
        
        .professor-info p {
            margin-bottom: 8px;
        }
        
        .professor-info strong {
            color: var(--dark-blue);
        }
        
        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
            
            .sidebar {
                display: none;
            }
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
            
            .button-group {
                flex-direction: column;
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
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Início</span></a></li>
            <li><a href="pesquisar-professores.php"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
            <li><a href="cadastrar-aluno.php"><i class="fas fa-user-graduate"></i> <span>Alunos</span></a></li>
            <li><a href="cadastrar-turmas.php"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
            <li><a href="#"><i class="fas fa-cog"></i> <span>Configurações</span></a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="user-area">
                <div class="user-img">AD</div>
                <div class="user-name">Admin</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Excluir Professor</h1>
                </div>
                <a href="pesquisar-professores.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <?= $erro ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <?php if (!isset($_POST['confirmar'])): ?>
                    <div class="confirmation-box">
                        <h3>Confirmar Exclusão</h3>
                        <p>Tem certeza que deseja excluir este professor?</p>
                        
                        <div class="professor-info">
                            <p><strong>ID:</strong> <?= htmlspecialchars($professor['id']) ?></p>
                            <p><strong>Nome:</strong> <?= htmlspecialchars($professor['nome']) ?></p>
                            <p><strong>Login:</strong> <?= htmlspecialchars($professor['login']) ?></p>
                            <p><strong>E-mail:</strong> <?= htmlspecialchars($professor['email']) ?></p>
                            <p><strong>Escola:</strong> <?= htmlspecialchars($professor['escola']) ?></p>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="button-group">
                                <button type="submit" name="confirmar" value="sim" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Confirmar Exclusão
                                </button>
                                <a href="pesquisar-professores.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>