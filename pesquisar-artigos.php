<?php 
require('sessao-adm.php');
require('conexao.php');

$mensagem = '';
$artigos = [];

try {
    $stmt = $conexao->query("SELECT * FROM artigo ORDER BY data DESC");
    $artigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem = "<div class='alert alert-danger'>Erro ao carregar artigos: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Artigos - Universo do Saber</title>
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
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transform: translateX(-100%);
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .sidebar-header {
            padding: 20px;
            background: linear-gradient(to right, var(--primary-blue), var(--dark-blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .close-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
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
        
        /* Main Content */
        .main-content {
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
            z-index: 100;
        }
        
        .menu-toggle {
            background: none;
            border: none;
            color: var(--dark-gray);
            font-size: 1.5rem;
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
            max-width: 1200px;
            margin: 0 auto;
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
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
        }
        
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }
        
        .btn-secondary:hover {
            background-color: #d1d7e0;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th,
        table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        table th {
            background-color: var(--light-gray);
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .actions button {
            margin-right: 5px;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-edit {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .img-thumbnail {
            max-width: 60px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            .top-nav {
                padding: 15px;
            }
            
            .close-sidebar {
                display: block;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-left: 250px;
            }
            
            .sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Universo do Saber</h3>
            <button class="close-sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard"><i class="fas fa-home"></i> <span>Início</span></a></li>
            <li><a href="cadastrar-professores"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
            <li><a href="pesquisar-questoes"><i class="fas fa-question-circle"></i> <span>Questões</span></a></li>
            <li><a href="pesquisar-artigos"><i class="fas fa-file-alt"></i> <span>Artigos</span></a></li>
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
                    <h1>Artigos Cadastrados</h1>
                    <p>Visualize, edite ou exclua artigos do banco de dados</p>
                </div>
                <a href="cadastrar-artigos" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Artigo
                </a>
            </div>
            
            <div class="card">
                <?= $mensagem ?>

                <?php if (!empty($artigos)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Data</th>
                                <th>Fonte</th>
                                <th>Imagem</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($artigos as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['nome']) ?></td>
                                    <td><?= date('d/m/Y H:i', ($a['data'])) ?></td>
                                    <td><?= htmlspecialchars($a['fonte']) ?: '-' ?></td>
                                    <td>
                                        <?php if (!empty($a['imagem']) && file_exists($a['imagem'])): ?>
                                            <img src="<?= $a['imagem'] ?>" alt="Imagem do artigo" class="img-thumbnail">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <button class="btn-edit"><a href="editar-artigos.php?id=<?=$a['id']?>" style="color: white; text-decoration: none;">Editar</a></button>
                                        <button class="btn-delete" onclick="if(confirm('Tem certeza que deseja excluir este artigo?')) { window.location.href='excluir-artigos.php?id=<?=$a['id']?>'; }">Excluir</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum artigo encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Menu toggle for mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        const closeSidebar = document.querySelector('.close-sidebar');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
        });
        
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                event.target !== menuToggle && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>