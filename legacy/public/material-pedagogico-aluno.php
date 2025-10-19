<?php
require('conexao.php');
require('sessao-aluno.php');

// Filtros
$filtro_turma = isset($_GET['turma']) ? $_GET['turma'] : '';
$filtro_materia = isset($_GET['materia']) ? $_GET['materia'] : '';

$query = "SELECT id, materia, descricao, caminho_arquivo, data_envio FROM materiais_pedagogicos WHERE 1=1";

if ($filtro_turma) {
    $query .= " AND turma = :turma";
}
if ($filtro_materia) {
    $query .= " AND materia = :materia";
}
$query .= " ORDER BY data_envio DESC";

$stmt = $conexao->prepare($query);

if ($filtro_turma) {
    $stmt->bindValue(':turma', $filtro_turma);
}
if ($filtro_materia) {
    $stmt->bindValue(':materia', $filtro_materia);
}

$stmt->execute();
$materiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiais Pedagógicos - Universo do Saber</title>
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
            min-height: 100vh;
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
        
        .btn-back {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            background-color: #d1d7e0;
        }
        
        /* Filter Box */
        .filter-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filter-box form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-box select {
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            background-color: white;
            min-width: 200px;
        }
        
        /* Materiais List */
        .materiais-list {
            display: grid;
            gap: 20px;
        }
        
        .material-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .material-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .material-info h4 {
            font-size: 1.1rem;
            color: var(--dark-blue);
            margin-bottom: 8px;
        }
        
        .material-info p {
            color: var(--dark-gray);
            margin-bottom: 8px;
        }
        
        .material-info small {
            color: var(--dark-gray);
            font-size: 0.8rem;
        }
        
        .material-actions a {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-blue);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .material-actions a:hover {
            background-color: var(--dark-blue);
        }
        
        .sem-resultados {
            text-align: center;
            padding: 30px;
            color: var(--dark-gray);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .top-nav {
                padding: 15px;
            }
            
            .content {
                padding: 15px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-box form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-box select {
                width: 100%;
            }
            
            .material-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .material-actions {
                align-self: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .material-actions {
                align-self: stretch;
            }
            
            .material-actions a {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="user-area">
            <div class="user-img"><?php echo substr($_SESSION['nome_login'], 0, 2); ?></div>
            <div class="user-name"><?php echo $_SESSION['nome_login']; ?></div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="content">
        <a href="dashboard-aluno" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Voltar para o Dashboard
        </a>
        
        <div class="page-header">
            <div class="page-title">
                <h1>Materiais Pedagógicos</h1>
                <p>Materiais disponíveis para estudo</p>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filter-box">
            <form method="GET">
                <select name="materia">
                    <option value="">Todas as matérias</option>
                    <option value="Matemática" <?php echo $filtro_materia == 'Matemática' ? 'selected' : ''; ?>>Matemática</option>
                    <option value="Português" <?php echo $filtro_materia == 'Português' ? 'selected' : ''; ?>>Português</option>
                    <option value="História" <?php echo $filtro_materia == 'História' ? 'selected' : ''; ?>>História</option>
                    <option value="Geografia" <?php echo $filtro_materia == 'Geografia' ? 'selected' : ''; ?>>Geografia</option>
                </select>
                
                <select name="turma">
                    <option value="">Todas as turmas</option>
                    <option value="1A" <?php echo $filtro_turma == '1A' ? 'selected' : ''; ?>>1º Ano A</option>
                    <option value="2B" <?php echo $filtro_turma == '2B' ? 'selected' : ''; ?>>2º Ano B</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
        
        <!-- Lista de Materiais -->
        <div class="materiais-list">
            <?php if (empty($materiais)): ?>
                <p class="sem-resultados">Nenhum material disponível.</p>
            <?php else: ?>
                <?php foreach ($materiais as $material): ?>
                    <div class="material-item">
                        <div class="material-info">
                            <h4><?php echo htmlspecialchars($material['materia'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p><?php echo htmlspecialchars($material['descricao'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <small>Enviado em: <?php echo date("d/m/Y", strtotime($material['data_envio'])); ?></small>
                        </div>
                        <div class="material-actions">
                            <a href="<?php echo htmlspecialchars($material['caminho_arquivo'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                <i class="fas fa-download"></i> Baixar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>