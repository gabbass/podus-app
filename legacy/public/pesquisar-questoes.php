<?php 
require('sessao-adm.php');
require('conexao.php');

// Inicializa variáveis de filtro e ordenação
$filtro_materia = $_GET['materia'] ?? '';
$filtro_assunto_grau = $_GET['assunto_grau'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'data_desc';
$coluna_ordenacao = 'data';
$direcao_ordenacao = 'DESC';

// Define a ordenação com base no parâmetro
switch($ordenacao) {
    case 'id_asc':
        $coluna_ordenacao = 'id';
        $direcao_ordenacao = 'ASC';
        break;
    case 'id_desc':
        $coluna_ordenacao = 'id';
        $direcao_ordenacao = 'DESC';
        break;
    case 'materia_asc':
        $coluna_ordenacao = 'materia';
        $direcao_ordenacao = 'ASC';
        break;
    case 'materia_desc':
        $coluna_ordenacao = 'materia';
        $direcao_ordenacao = 'DESC';
        break;
    case 'assunto_asc':
        $coluna_ordenacao = 'assunto';
        $direcao_ordenacao = 'ASC';
        break;
    case 'assunto_desc':
        $coluna_ordenacao = 'assunto';
        $direcao_ordenacao = 'DESC';
        break;
    case 'data_asc':
        $coluna_ordenacao = 'data';
        $direcao_ordenacao = 'ASC';
        break;
    default:
        $coluna_ordenacao = 'data';
        $direcao_ordenacao = 'DESC';
}

// Prepara a consulta SQL com filtros
$sql = "SELECT * FROM questoes WHERE 1=1";
$params = [];

if (!empty($filtro_materia)) {
    // Verifica se o filtro é numérico (ID) ou texto (matéria)
    if (is_numeric($filtro_materia)) {
        $sql .= " AND id = :id";
        $params[':id'] = $filtro_materia;
    } else {
        $sql .= " AND materia LIKE :materia";
        $params[':materia'] = '%' . $filtro_materia . '%';
    }
}

if (!empty($filtro_assunto_grau)) {
    $sql .= " AND (assunto LIKE :assunto OR grau_escolar LIKE :grau_escolar)";
    $params[':assunto'] = '%' . $filtro_assunto_grau . '%';
    $params[':grau_escolar'] = '%' . $filtro_assunto_grau . '%';
}

$sql .= " ORDER BY $coluna_ordenacao $direcao_ordenacao";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_registros = count($questoes);
} catch (PDOException $e) {
    die("Erro ao consultar questões: " . $e->getMessage());
}

// Função para gerar URL com parâmetros
function buildUrl($params) {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        $currentParams[$key] = $value;
    }
    return '?' . http_build_query($currentParams);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Questões - Universo do Saber</title>
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
        <a href="dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        
        <div class="user-area">
            <div class="user-img">AD</div>
            <div class="user-name">Admin</div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Gestão de Questões</h1>
                <p>Visualize, edite ou exclua questões do banco de dados</p>
            </div>
            <a href="cadastrar-questao" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Questão
            </a>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-container">
            <form method="get" class="filtros-form" id="filtrosForm">
                <div class="filtro-group">
                    <label for="materia">Matéria ou ID</label>
                    <input type="text" id="materia" name="materia" placeholder="Filtrar por matéria ou ID" value="<?php echo htmlspecialchars($filtro_materia); ?>">
                </div>
                
                <div class="filtro-group">
                    <label for="assunto_grau">Assunto ou Nível de Ensino</label>
                    <input type="text" id="assunto_grau" name="assunto_grau" placeholder="Filtrar por assunto ou nível" value="<?php echo htmlspecialchars($filtro_assunto_grau); ?>">
                </div>
                
                <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
                <button type="button" class="btn-batch-edit" id="btnEditarLote"><i class="fas fa-edit"></i> Editar em Lote</button>
            </form>
            
            <div class="contador-registros">
                Exibindo <strong><?php echo $total_registros; ?></strong> registro(s) encontrado(s)
            </div>
        </div>
        
        <!-- Tabela de questões -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-header" width='5%'>
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th onclick="ordenar('id')" class="<?php echo $coluna_ordenacao == 'id' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>" width='10%'>
                            ID
                        </th>
                        <th width='10%' onclick="ordenar('data')" class="<?php echo $coluna_ordenacao == 'data' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Data
                        </th>
                        <th width='15%' onclick="ordenar('materia')" class="<?php echo $coluna_ordenacao == 'materia' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Matéria
                        </th>
                        <th width='12%'onclick="ordenar('assunto')" class="<?php echo $coluna_ordenacao == 'assunto' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Assunto
                        </th>
                        <th width='15%'>Nível de Ensino</th>
                        <th width='15%'>Tipo</th>
                        <th width='22%'>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($questoes) > 0): ?>
                        <?php foreach($questoes as $questao): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="checkbox-item" value="<?php echo $questao['id']; ?>">
                                </td>
                                <td><?php echo $questao['id']; ?></td>
                                <td><?php echo date('d/m/Y', ($questao['data'])); ?></td>
                                <td><?php echo htmlspecialchars($questao['materia']); ?></td>
                                <td><?php echo htmlspecialchars($questao['assunto']); ?></td>
                                <td><?php echo htmlspecialchars($questao['grau_escolar']); ?></td>
                                <td><?php echo htmlspecialchars($questao['tipo']); ?></td>
                               
                                <td>
                                    <button class="btn-action btn-view" onclick="abrirQuestao(<?php echo $questao['id']; ?>)">
                                        <i class="fas fa-eye"></i> 
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editarQuestao(<?php echo $questao['id']; ?>)">
                                        <i class="fas fa-edit"></i> 
                                    </button>
                                    <button class="btn-action btn-delete" onclick="confirmarExclusao(<?php echo $questao['id']; ?>)">
                                        <i class="fas fa-trash-alt"></i> 
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Nenhuma questão encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Função para limpar os filtros
        function limparFiltros() {
            window.location.href = window.location.pathname;
        }
        
        // Função para ordenar a tabela
        function ordenar(coluna) {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            // Verifica se já está ordenando por esta coluna (inverte a direção)
            if (params.get('ordenacao') === `${coluna}_asc`) {
                params.set('ordenacao', `${coluna}_desc`);
            } else {
                params.set('ordenacao', `${coluna}_asc`);
            }
            
            window.location.href = url.pathname + '?' + params.toString();
        }
        
        // Funções para ações
        function abrirQuestao(id) {
            window.location.href = `visualizar-questao?id=${id}`;
        }
        
        function editarQuestao(id) {
            window.location.href = `editar-questao?id=${id}`;
        }
        
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.')) {
                window.location.href = `excluir-questao?id=${id}`;
            }
        }
        
        // Selecionar todos os checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Edição em lote
        document.getElementById('btnEditarLote').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item:checked');
            const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
            
            if (ids.length === 0) {
                alert('Selecione pelo menos uma questão para editar em lote.');
                return;
            }
            
            window.location.href = `editar-lote.php?ids=${ids.join(',')}`;
        });
    </script>
</body>
</html>