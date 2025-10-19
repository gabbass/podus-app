<?php
require('conexao.php');
require('sessao-adm.php');

// Consulta os professores no banco de dados
$query = "SELECT id, login, nome, email, plano, escola FROM login WHERE perfil = 'Professor'";
$stmt = $conexao->prepare($query);
$stmt->execute();
$professores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processa pesquisa se houver termo
$termo_pesquisa = isset($_GET['search']) ? trim($_GET['search']) : '';
$professores_filtrados = $professores;

if (!empty($termo_pesquisa)) {
    $termo = strtolower($termo_pesquisa);
    $professores_filtrados = array_filter($professores, function($professor) use ($termo) {
        return (
            stripos(strtolower($professor['nome']), $termo) !== false ||
            stripos(strtolower($professor['plano']), $termo) !== false ||
            stripos(strtolower($professor['escola']), $termo) !== false
        );
    });
}

// Processa ordenação
$ordenar_por = isset($_GET['order']) ? $_GET['order'] : 'nome';
$direcao = isset($_GET['dir']) ? $_GET['dir'] : 'asc';

usort($professores_filtrados, function($a, $b) use ($ordenar_por, $direcao) {
    $valorA = $a[$ordenar_por];
    $valorB = $b[$ordenar_por];
    
    if (is_numeric($valorA) && is_numeric($valorB)) {
        return $direcao === 'asc' ? $valorA - $valorB : $valorB - $valorA;
    } else {
        return $direcao === 'asc' 
            ? strcmp(strtolower($valorA), strtolower($valorB))
            : strcmp(strtolower($valorB), strtolower($valorA));
    }
});

// Determina a direção oposta para o link de ordenação
$direcao_oposta = $direcao === 'asc' ? 'desc' : 'asc';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Professores - Universo do Saber</title>
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
        
          /* Adicione esta regra CSS para garantir que os links nos cabeçalhos da tabela permaneçam brancos */
    th a, th a:hover, th a:visited, th a:active {
        color: white !important;
        text-decoration: none;
    }
        
        a:link,th{
            color:white;
            text-decoration:none;
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
        /* Search Form */
        .search-form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-group {
            display: flex;
            align-items: center;
        }
        .form-group input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
            margin-right: 10px;
        }
        .form-group button {
            padding: 12px 20px;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        .form-group button:hover {
            background-color: var(--dark-blue);
        }
        /* Results Table */
        .results-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px;
            text-align: left;
            cursor: pointer;
            user-select: none;
        }
        th:hover {
            background-color: var(--dark-blue);
            color:white;
            text-decoration:none;
        }
        th i {
            margin-left: 5px;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: rgba(0, 87, 183, 0.05);
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            font-size: 0.9rem;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn.open {
            background-color: var(--primary-blue);
            color: white;
        }
        .action-btn.edit {
            background-color: var(--primary-orange);
            color: white;
        }
        .action-btn.delete {
            background-color: #dc3545;
            color: white;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .results-count {
            margin-top: 15px;
            font-size: 0.9rem;
            color: var(--dark-gray);
            text-align: right;
        }
        /* Responsive */
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
            .form-group {
                flex-direction: column;
            }
            .form-group input {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
            .form-group button {
                width: 100%;
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
        <h1>Pesquisar Professores</h1>
    </div>
    <div>
        <a href="dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <a href="adicionar-professores" class="btn btn-primary" style="margin-left: 10px;">
            <i class="fas fa-plus"></i> Adicionar Professor
        </a>
    </div>
</div>
            <div class="search-form">
                <form method="get" action="">
                    <div class="form-group">
                        <input type="text" name="search" value="<?= htmlspecialchars($termo_pesquisa) ?>" placeholder="Digite o nome, plano ou nome do professor">
                        <button type="submit"><i class="fas fa-search"></i> Pesquisar</button>
                    </div>
                </form>
            </div>
            
            <div class="results-table">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Id</th>
                            <th>
                                <a href="?search=<?= urlencode($termo_pesquisa) ?>&order=plano&dir=<?= $ordenar_por === 'plano' ? $direcao_oposta : 'asc' ?>">
                                    Plano 
                                    <?php if ($ordenar_por === 'plano'): ?>
                                        <i class="fas fa-sort-<?= $direcao === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($termo_pesquisa) ?>&order=nome&dir=<?= $ordenar_por === 'nome' ? $direcao_oposta : 'asc' ?>">
                                    Nome 
                                    <?php if ($ordenar_por === 'nome'): ?>
                                        <i class="fas fa-sort-<?= $direcao === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($termo_pesquisa) ?>&order=email&dir=<?= $ordenar_por === 'email' ? $direcao_oposta : 'asc' ?>">
                                    Email 
                                    <?php if ($ordenar_por === 'email'): ?>
                                        <i class="fas fa-sort-<?= $direcao === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($termo_pesquisa) ?>&order=login&dir=<?= $ordenar_por === 'login' ? $direcao_oposta : 'asc' ?>">
                                    Login 
                                    <?php if ($ordenar_por === 'login'): ?>
                                        <i class="fas fa-sort-<?= $direcao === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($termo_pesquisa) ?>&order=escola&dir=<?= $ordenar_por === 'escola' ? $direcao_oposta : 'asc' ?>">
                                    Escola 
                                    <?php if ($ordenar_por === 'escola'): ?>
                                        <i class="fas fa-sort-<?= $direcao === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professores_filtrados as $professor): ?>
                        <tr>
                            <td><input type="checkbox" class="rowCheckbox"></td>
                            <td><?= htmlspecialchars($professor['id']) ?></td>
                                                        <td><?= htmlspecialchars($professor['plano']) ?></td>

                            <td><?= htmlspecialchars($professor['nome']) ?></td>
                            <td><?= htmlspecialchars($professor['email']) ?></td>
                            <td><?= htmlspecialchars($professor['login']) ?></td>
                            <td><?= htmlspecialchars($professor['escola']) ?></td>
                            <td>
                                
                                <a href="editar-professores.php?id=<?= $professor['id'] ?>" class="action-btn edit"><i class="fas fa-edit"></i> Editar</a>
                                <a href="excluir-professores.php?id=<?= $professor['id'] ?>" class="action-btn delete"><i class="fas fa-trash"></i> Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="results-count">
                    Total de registros encontrados: <span id="totalCount"><?= count($professores_filtrados) ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Selecionar todos os checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.rowCheckbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

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