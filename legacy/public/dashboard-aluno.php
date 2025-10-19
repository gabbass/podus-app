<?php
require('conexao.php');
require('sessao-aluno.php');
// Consulta para contar o total de questões
$query_questoes = "SELECT COUNT(*) as total FROM questoes";
$stmt_questoes = $conexao->prepare($query_questoes);
$stmt_questoes->execute();
$total_questoes = $stmt_questoes->fetch(PDO::FETCH_ASSOC)['total'];
// Consulta para contar alunos da escola do professor logado
$perfil = 'Aluno';
$query_alunos = "SELECT COUNT(*) as total FROM login
                 WHERE escola = :escola AND login = :login AND perfil = :perfil";
$stmt_alunos = $conexao->prepare($query_alunos);
$stmt_alunos->bindValue(':escola', $_SESSION['escola']);
$stmt_alunos->bindValue(':login', $_SESSION['login']);
$stmt_alunos->bindValue(':perfil', $perfil);
$stmt_alunos->execute();
$total_alunos = $stmt_alunos->fetch(PDO::FETCH_ASSOC)['total'];
// Consulta para contar turmas da escola do professor logado
$query_turmas = "SELECT COUNT(*) as total FROM turmas
                 WHERE escola = :escola AND login = :login";
$stmt_turmas = $conexao->prepare($query_turmas);
$stmt_turmas->bindValue(':escola', $_SESSION['escola']);
$stmt_turmas->bindValue(':login', $_SESSION['login']);
$stmt_turmas->execute();
$total_turmas = $stmt_turmas->fetch(PDO::FETCH_ASSOC)['total'];
// BUSCA PROVAS DO ALUNO (tabela 'provas' + codigo_prova)
$query_provas = "
SELECT
    po.id AS id_prova_online,
    po.data,
    po.materia,
    p.tentativa_max,
    p.tentativa_feita
FROM provas_online po
INNER JOIN provas p
      ON po.turma = p.turma
      AND po.materia = p.materia
      AND p.matricula = :matricula
GROUP BY po.id, po.data, po.materia, p.tentativa_max, p.tentativa_feita
ORDER BY po.data DESC
";
$stmt_provas = $conexao->prepare($query_provas);
$stmt_provas->bindValue(':matricula', $_SESSION['matricula']);
$stmt_provas->execute();
$provasAluno = $stmt_provas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Universo do Saber</title>
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
        /* Lista de provas */
        .chart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .chart-title {
            font-size: 1.2rem;
            color: var(--dark-blue);
            font-weight: 600;
        }
        .tabela-provas {
            border-collapse: collapse;
            margin: 0 auto 0 auto;
            width: 100%;
            background: white;
            border-radius: 9px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,87,183,0.035);
            font-size: 1rem;
        }
        .tabela-provas thead th {
            background: linear-gradient(to right, #0057b7, #003d7a);
            color: white;
            padding: 12px 7px;
            border: none;
            font-weight: 600;
        }
        .tabela-provas tbody td {
            padding: 13px 7px;
            border-bottom: 1px solid #eee;
        }
        .tabela-provas tbody tr:last-child td {
            border-bottom: none;
        }
        .tabela-provas tbody tr:hover {
            background: #f5f7fa;
        }
        .tabela-provas .btn {
            padding: 8px 14px;
            border-radius: 4px;
            border: none;
            color: white;
            background: #0057b7;
            font-size: .98em;
            transition: background 0.2s;
            text-decoration: none;
        }
        .tabela-provas .btn:hover {
            background: #003d7a;
        }
   /* Mobile Menu Toggle */
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
            display: none;
            background: none;
            border: none;
            color: var(--dark-gray);
            font-size: 1.5rem;
            cursor: pointer;
            margin-right: 15px;
        }
            .top-nav {
                position: sticky;
                top: 0;
                z-index: 100;
            }
        }
        @media (max-width: 768px) {
            .chart-container {padding: 10px;}
            .content {padding: 10px;}
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
            <li><a href="dashboard-aluno.php" class="active"><i class="fas fa-home"></i> <span>Início</span></a></li>
            <!-- <li><a href="#"><i class="fas fa-edit"></i> <span>Fazer Prova</span></a></li> -->
			<li><a href="material-apoio.php"><i class="fas fa-book"></i> <span>Material de apoio</span></a></li>
            <li><a href="nota-provas.php"><i class="fas fa-clipboard-list"></i> <span>Ver Notas</span></a></li>
            <!-- <li><a href="material-pedagogico-aluno"><i class="fas fa-book"></i> <span>Material Pedagógico</span></a></li> -->
            <li><a href="sair"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
        </ul>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
         <div class="top-nav">
            <!--<button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>-->
            <div class="user-area" style='display:none'>
               <!-- <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>-->
                <div class="user-img"><?php echo substr($_SESSION['nome_login'], 0, 2); ?></div>
                <div class="user-name"><?php echo $_SESSION['nome_login']; ?></div>
            </div>
        </div>
        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Bem-vindo ao Portal Universo do Saber</p>
                </div>
            </div>
            <!-- Lista de provas -->
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Minhas Provas</div>
                </div>
                <div class="lista">
    <?php if (empty($provasAluno)): ?>
        <p style="text-align:center; color:#299556; margin:23px 0 0 0;">Nenhuma prova disponível.</p>
    <?php else: ?>
        <table class="tabela-provas">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Matéria</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($provasAluno as $prova): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($prova['data'])) ?></td>
                    <td><?= htmlspecialchars($prova['materia']) ?></td>
                    <td>
  <?php if ($prova['tentativa_feita'] < $prova['tentativa_max']): ?>
    <a class="btn" href="responder-prova.php?id=<?= $prova['id_prova_online'] ?>">
      <i class="fas fa-edit"></i> Responder
    </a>
  <?php else: ?>
    <span style="color:#D8002F; font-weight:600;">Tentativas máximas atingidas</span>
  <?php endif; ?>
</td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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