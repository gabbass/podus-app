<?php 
require('sessao-adm.php');
require('conexao.php');

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $texto = $_POST['texto'] ?? '';
    $fonte = $_POST['fonte'] ?? '';
    $data = $_POST['data'] ??  0;
    $data = strtotime($data);

    // Validar campos obrigatórios
    if (empty($nome) || empty($texto)) {
        $mensagem = "<div class='alert alert-danger'>Nome e texto são obrigatórios.</div>";
    } else {
        try {
            // Inserir artigo sem imagem ainda
            $stmt_insert = $conexao->prepare("INSERT INTO artigo (nome, texto, fonte, data) VALUES (?, ?, ?, ?)");
            $stmt_insert->execute([$nome, $texto, $fonte,$data]);

            // Pegar o ID do artigo recém-inserido
            $artigo_id = $conexao->lastInsertId();

            // Pasta específica do artigo
            $pasta_artigo = "artigos/" . $artigo_id;
            if (!is_dir($pasta_artigo)) {
                mkdir($pasta_artigo, 0777, true);
            }

            // Upload da imagem
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $imagem_nome = basename($_FILES['imagem']['name']);
                $imagem_tipo = strtolower(pathinfo($imagem_nome, PATHINFO_EXTENSION));
                $caminho_destino = $pasta_artigo . '/' . uniqid() . '.' . $imagem_tipo;

                // Verificar tipo de imagem
                $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($imagem_tipo, $tipos_permitidos)) {
                    $mensagem .= "<div class='alert alert-warning'>Tipo de imagem não permitido.</div>";
                } else {
                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_destino)) {
                        // Atualizar registro com caminho da imagem
                        $stmt_update_imagem = $conexao->prepare("UPDATE artigo SET imagem = ? WHERE id = ?");
                        $stmt_update_imagem->execute([$caminho_destino, $artigo_id]);
                        $mensagem .= "<div class='alert alert-success'>Artigo cadastrado com sucesso!</div>";
                    } else {
                        $mensagem .= "<div class='alert alert-danger'>Erro ao fazer upload da imagem.</div>";
                    }
                }
            } else {
                $mensagem .= "<div class='alert alert-info'>Artigo cadastrado, mas sem imagem.</div>";
            }

        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger'>Erro ao salvar artigo: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Artigo - Universo do Saber</title>
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
        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        /* Form */
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
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
            resize: vertical;
        }
        .form-group textarea {
            min-height: 150px;
        }
        /* Alerts */
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
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
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
            <li><a href="pesquisar-artigos" class="active"><i class="fas fa-file-alt"></i> <span>Artigos</span></a></li>
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
                    <h1>Cadastrar Artigo</h1>
                    <p>Preencha os campos abaixo para cadastrar um novo artigo</p>
                </div>
                <a href="dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if ($mensagem): ?>
                <?= $mensagem ?>
            <?php endif; ?>
            
            <div class="card">
                <form method="post" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label for="data">Data:</label>
                        <input type="date" name="data" id="data" required>
                    </div>

                    
                    <div class="form-group">
                        <label for="nome">Título do Artigo:</label>
                        <input type="text" name="nome" id="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="texto">Texto do Artigo:</label>
                        <textarea name="texto" id="texto" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="fonte">Fonte (opcional):</label>
                        <input type="text" name="fonte" id="fonte">
                    </div>
                    
                    <div class="form-group">
                        <label for="imagem">Imagem do Artigo:</label>
                        <input type="file" name="imagem" id="imagem" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Artigo
                    </button>
                    <button type="button" class="btn btn-primary" onclick="location.assign('pesquisar-artigos')">
                        <i class="fas fa-search"></i> Pesquisar Artigo
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