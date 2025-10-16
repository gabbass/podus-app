<?php
require('conexao.php');

// Verifica se há um termo de pesquisa
$termo_pesquisa = isset($_GET['pesquisa']) ? $_GET['pesquisa'] : '';

// Consulta para obter todos os planejamentos com filtro de pesquisa
$sql = "SELECT * FROM planejador WHERE 
        curso LIKE :pesquisa OR 
        ano LIKE :pesquisa 
        ORDER BY data DESC";

$stmt = $conexao->prepare($sql);
$stmt->bindValue(':pesquisa', '%'.$termo_pesquisa.'%', PDO::PARAM_STR);
$stmt->execute();
$planejamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Planejamentos - Universo do Saber</title>
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
            padding: 20px;
        }
        
        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 20px;
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
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
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
            background-color: #d1d5db;
        }
        
        .btn-batch {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-batch:hover {
            background-color: #5a6268;
        }
        
        .btn-generate {
            background-color: #28a745;
            color: white;
        }
        
        .btn-generate:hover {
            background-color: #218838;
        }
        
        /* Filtros */
        .filtros-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filtros-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filtro-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filtro-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-gray);
            font-weight: 500;
        }
        
        .filtro-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
        }
        
        .btn-filtrar {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-filtrar:hover {
            background-color: var(--dark-blue);
        }
        
        .btn-batch-edit {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-batch-edit:hover {
            background-color: #138496;
        }
        
        .contador-registros {
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--dark-gray);
        }
        
        .contador-registros strong {
            color: var(--primary-blue);
        }
        
        /* Tabela */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-top: 20px;
            overflow-x: auto;
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
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        th:hover {
            background-color: rgba(0, 87, 183, 0.1);
        }
        
        th.sorted-asc::after {
            content: " ↑";
            font-size: 0.8em;
        }
        
        th.sorted-desc::after {
            content: " ↓";
            font-size: 0.8em;
        }
        
        tr:hover {
            background-color: rgba(0, 87, 183, 0.05);
        }
        
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        
        .checkbox-header {
            width: 40px;
            text-align: center;
        }
        
        .checkbox-item {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 5px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-action i {
            margin-right: 5px;
        }
        
        .btn-edit {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-edit:hover {
            background-color: var(--dark-blue);
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
        }
        
        .btn-view {
            background-color: #28a745;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #218838;
        }
        
        .status-active {
            color: #28a745;
        }
        
        .status-inactive {
            color: #dc3545;
        }
        
        /* Barra de pesquisa */
        .search-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .search-button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: var(--dark-blue);
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .top-nav {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }
            
            .filtros-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .filtro-group {
                width: 100%;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-button {
                width: 100%;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            
            .btn-action {
                padding: 4px 8px;
                font-size: 0.8rem;
                margin-bottom: 5px;
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
    </div>
    
    <!-- Content -->
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Gestão de Planejamentos</h1>
                <p>Visualize, edite ou exclua planejamentos cadastrados</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-generate" onclick="gerarPlanejador()">
                    <i class="fas fa-file-pdf"></i> Gerar Planejador
                </button>
                <a href="planejador-cadastrar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Novo
                </a>
            </div>
        </div>
        
        <!-- Barra de pesquisa -->
        <div class="search-container">
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="pesquisa" 
                    class="search-input" 
                    placeholder="Pesquisar por curso ou ano..."
                    value="<?php echo htmlspecialchars($termo_pesquisa); ?>"
                >
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i> Pesquisar
                </button>
            </form>
        </div>
        
        <!-- Tabela de planejamentos -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-header">
                            <input type="checkbox" id="select-all" class="checkbox-item">
                        </th>
                        <th width='15%'>Data</th>
                        <th width='25%'>Curso</th>
                        <th width='10%'>Ano</th>
                        <th width='15%'>Tipo</th>
                        <th width='10%'>Sequencial</th>
                        <th width='25%'>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($planejamentos) > 0): ?>
                        <?php foreach ($planejamentos as $planejamento): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="checkbox-item" value="<?php echo $planejamento['id']; ?>">
                                </td>
                                <td><?php echo date('d/m/Y', ($planejamento['data'])); ?></td>
                                <td><?php echo htmlspecialchars($planejamento['curso']); ?></td>
                                <td><?php echo htmlspecialchars($planejamento['ano']); ?></td>
                                <td><?php echo htmlspecialchars($planejamento['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($planejamento['sequencial']); ?></td>
                                <td>
                                    <button class="btn-action btn-view" onclick="window.location.href='visualizar-planejador.php?id=<?php echo $planejamento['id']; ?>'">
                                        <i class="fas fa-eye"></i> 
                                    </button>
                                    <button class="btn-action btn-edit" onclick="window.location.href='editar-planejador.php?id=<?php echo $planejamento['id']; ?>'">
                                        <i class="fas fa-edit"></i> 
                                    </button>
                                    <button class="btn-action btn-delete" onclick="confirmarExclusao(<?php echo $planejamento['id']; ?>)">
                                        <i class="fas fa-trash"></i> 
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">
                                <?php echo $termo_pesquisa ? 
                                    'Nenhum resultado encontrado para sua pesquisa.' : 
                                    'Nenhum planejamento cadastrado ainda.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este planejamento?')) {
                window.location.href = 'excluir-planejador.php?id=' + id;
            }
        }

        // Selecionar todos os checkboxes
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item:not(#select-all)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Função para gerar planejador com os itens selecionados
        function gerarPlanejador() {
            const checkboxes = document.querySelectorAll('.checkbox-item:checked:not(#select-all)');
            const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
            
            if (ids.length === 0) {
                alert('Selecione pelo menos um planejamento para gerar.');
                return;
            }
            
            window.location.href = 'gerando-planejador.php?ids=' + ids.join(',');
        }
    </script>
</body>
</html>