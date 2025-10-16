<?php
require('sessao-professor.php');
require('conexao.php');

$login_professor = $_SESSION['login'];
$mensagem = '';
$erro = '';

// Parâmetros de filtro
$filtro_matricula = isset($_GET['matricula']) ? trim($_GET['matricula']) : '';
$filtro_turma = isset($_GET['turma']) ? trim($_GET['turma']) : '';
$filtro_data = isset($_GET['data']) ? trim($_GET['data']) : '';
$filtro_materia = isset($_GET['materia']) ? trim($_GET['materia']) : '';

// Busca as provas aplicadas pelo professor com filtros
$provas = [];
$total_registros = 0;
try {
    $sql_provas = "SELECT p.*, t.nome as nome_turma, q.materia 
                   FROM provas p
                   JOIN turmas t ON p.turma = t.nome
                   JOIN questoes q ON p.id_questao = q.id
                   WHERE p.login = :login_professor";
    
    // Adiciona filtros se existirem
    if (!empty($filtro_matricula)) {
        $sql_provas .= " AND p.matricula LIKE :matricula";
    }
    if (!empty($filtro_turma)) {
        $sql_provas .= " AND p.turma LIKE :turma";
    }
    if (!empty($filtro_data)) {
        $timestamp = strtotime($filtro_data);
        $sql_provas .= " AND p.data = :data";
    }
    if (!empty($filtro_materia)) {
        $sql_provas .= " AND q.materia LIKE :materia";
    }
    
    $sql_provas .= " ORDER BY p.id DESC";
    
    $stmt = $conexao->prepare($sql_provas);
    $stmt->bindValue(':login_professor', $login_professor);
    
    if (!empty($filtro_matricula)) {
        $stmt->bindValue(':matricula', '%' . $filtro_matricula . '%');
    }
    if (!empty($filtro_turma)) {
        $stmt->bindValue(':turma', '%' . $filtro_turma . '%');
    }
    if (!empty($filtro_data)) {
        $stmt->bindValue(':data', $timestamp);
    }
    if (!empty($filtro_materia)) {
        $stmt->bindValue(':materia', '%' . $filtro_materia . '%');
    }
    
    $stmt->execute();
    $provas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_registros = count($provas);
} catch (PDOException $e) {
    $erro = "Erro ao carregar provas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provas e Notas - Universo do Saber</title>
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
        
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }
        
        .btn-secondary:hover {
            background-color: #d1d7e0;
        }
        
        /* Page Actions */
        .page-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Filtros */
        .card-filters {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-blue);
        }
        
        .filter-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .filter-control[type="date"] {
            height: 42px;
        }
        
        .filter-actions {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
        
        /* Table */
        .card-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .total-records {
            margin-bottom: 15px;
            font-weight: 500;
            color: var(--dark-blue);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        th {
            background-color: var(--light-gray);
            color: var(--dark-blue);
            font-weight: 600;
        }
        
        tr:hover {
            background-color: rgba(0, 87, 183, 0.05);
        }
        
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        
        .checkbox-cell input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: var(--dark-gray);
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            color: var(--primary-blue);
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            .top-nav {
                padding: 15px;
            }
            
            .card-table, .card-filters {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .page-actions {
                width: 100%;
            }
            
            .page-actions .btn {
                flex: 1;
                text-align: center;
            }
            
            .filter-form {
                flex-direction: column;
                gap: 15px;
            }
            
            .filter-actions {
                align-items: stretch;
            }
            
            .filter-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <a href="dashboard-professor" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        
        <div class="user-area">
            <div class="user-img">
                <?= strtoupper(substr($_SESSION['login'], 0, 1)) ?>
            </div>
            <span class="user-name"><?= $_SESSION['login'] ?></span>
        </div>
    </div>
    
    <!-- Content -->
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Provas e Notas</h1>
                <p>Visualize e gerencie as provas aplicadas e as notas dos alunos</p>
            </div>
            <div class="page-actions">
                <a href="cadastrar-prova" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Prova
                </a>
                <button type="button" class="btn btn-primary" id="gerarImpressao">
                    <i class="fas fa-print"></i> Gerar Impressão
                </button>
                <button type="button" class="btn btn-primary" id="gerarCartaoResposta">
                    <i class="fas fa-file-alt"></i> Cartão de Resposta
                </button>
            </div>
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
        
        <!-- Filtros -->
        <div class="card-filters">
            <form method="get" class="filter-form" id="filtroForm">
                <div class="filter-group">
                    <label for="matricula">Matrícula</label>
                    <input type="text" id="matricula" name="matricula" class="filter-control" 
                           value="<?= htmlspecialchars($filtro_matricula) ?>" placeholder="Filtrar por matrícula">
                </div>
                
                <div class="filter-group">
                    <label for="turma">Turma</label>
                    <input type="text" id="turma" name="turma" class="filter-control" 
                           value="<?= htmlspecialchars($filtro_turma) ?>" placeholder="Filtrar por turma">
                </div>
                
                <div class="filter-group">
                    <label for="data">Data</label>
                    <input type="date" id="data" name="data" class="filter-control" 
                           value="<?= htmlspecialchars($filtro_data) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="materia">Matéria</label>
                    <input type="text" id="materia" name="materia" class="filter-control" 
                           value="<?= htmlspecialchars($filtro_materia) ?>" placeholder="Filtrar por matéria">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="provas-e-notas" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Tabela de resultados -->
        <div class="card-table">
            <div class="total-records">
                Total de registros encontrados: <?= $total_registros ?>
            </div>
            
            <?php if ($total_registros > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selecionarTodos">
                            </th>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Turma</th>
                            <th>Matéria</th>
                            <th>Questão</th>
                            <th>Matrícula Aluno</th>
                            <th>Nota</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($provas as $prova): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="prova-checkbox" value="<?= $prova['id'] ?>">
                                </td>
                                <td><?= htmlspecialchars($prova['id']) ?></td>
                                <td><?= date('d/m/Y', $prova['data']) ?></td>
                                <td><?= htmlspecialchars($prova['nome_turma']) ?></td>
                                <td><?= htmlspecialchars($prova['materia']) ?></td>
                                <td><?= htmlspecialchars($prova['id_questao']) ?></td>
                                <td><?= htmlspecialchars($prova['matricula']) ?></td>
                                <td>
                                    <?php 
                                        $nota = $prova['nota'];
                                        $badgeClass = 'badge-';
                                        if ($nota >= 7) {
                                            $badgeClass .= 'success';
                                        } elseif ($nota >= 5) {
                                            $badgeClass .= 'warning';
                                        } else {
                                            $badgeClass .= 'danger';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= number_format($nota, 1) ?></span>
                                </td>
                                <td class="actions">
                                    <button class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhuma prova encontrada com os filtros aplicados.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Selecionar todos os checkboxes
        document.getElementById('selecionarTodos').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.prova-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Obter IDs das provas selecionadas
        function getProvasSelecionadas() {
            const checkboxes = document.querySelectorAll('.prova-checkbox:checked');
            const ids = [];
            checkboxes.forEach(checkbox => {
                ids.push(checkbox.value);
            });
            return ids;
        }
        
        // Verificar se há checkboxes selecionados antes de enviar
        function verificarSelecao(action) {
            const ids = getProvasSelecionadas();
            if (ids.length === 0) {
                alert('Por favor, selecione pelo menos uma prova para ' + action);
                return false;
            }
            return true;
        }
        
        // Gerar Impressão - Redireciona com os IDs via GET
        document.getElementById('gerarImpressao').addEventListener('click', function() {
            if (verificarSelecao('gerar impressão')) {
                const ids = getProvasSelecionadas();
                window.location.href = 'gerar-impressao.php?ids=' + ids.join(',');
            }
        });
        
        // Gerar Cartão de Resposta - Redireciona com os IDs via GET
        document.getElementById('gerarCartaoResposta').addEventListener('click', function() {
            if (verificarSelecao('gerar cartão de resposta')) {
                const ids = getProvasSelecionadas();
                window.location.href = 'cartao-de-resposta.php?ids=' + ids.join(',');
            }
        });
    </script>
</body>
</html>