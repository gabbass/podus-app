<?php
require('conexao.php');

// Consulta para obter todos os planejamentos
$query = "SELECT id, escola, professor, ano_escolar, periodo, data_criacao FROM planejador ORDER BY data_criacao DESC";
$stmt = $conexao->prepare($query);
$stmt->execute();
$planejamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Planejamentos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            line-height: 1.6;
        }

        .header {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .search-container {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .button-container {
            text-align: center;
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        button, .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        button:hover, .btn:hover {
            background-color: #45a049;
        }

        .btn-edit {
            background-color: #2196F3;
        }

        .btn-edit:hover {
            background-color: #0b7dda;
        }

        .btn-delete {
            background-color: #f44336;
        }

        .btn-delete:hover {
            background-color: #d32f2f;
        }

        .btn-view {
            background-color: #ff9800;
        }

        .btn-view:hover {
            background-color: #e68a00;
        }

        .no-results {
            background-color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            
            th, td {
                padding: 8px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 600px) {
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pesquisar Planejamentos</h1>
    </div>

    <div class="search-container">
        <form method="GET" action="">
            <div class="form-group">
                <label for="professor">Professor:</label>
                <input type="text" id="professor" name="professor" placeholder="Filtrar por professor...">
            </div>
            <div class="form-group">
                <label for="ano">Ano Escolar:</label>
                <select id="ano" name="ano">
                    <option value="">Todos</option>
                    <option value="6º ano">6º ano</option>
                    <option value="7º ano">7º ano</option>
                    <option value="8º ano">8º ano</option>
                    <option value="9º ano">9º ano</option>
                </select>
            </div>
            <div class="button-container">
                <button type="submit">Filtrar</button>
                <button type="button" onclick="window.location.href='planejador-pesquisar.php'">Limpar Filtros</button>
                <button type="button" onclick="window.location.href='index.php'">Novo Planejamento</button>
            </div>
        </form>
    </div>

    <?php if (count($planejamentos) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Escola</th>
                    <th>Professor</th>
                    <th>Ano Escolar</th>
                    <th>Período</th>
                    <th>Data Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planejamentos as $planejamento): ?>
                    <tr>
                        <td><?= $planejamento['id'] ?></td>
                        <td><?= htmlspecialchars($planejamento['escola']) ?></td>
                        <td><?= htmlspecialchars($planejamento['professor']) ?></td>
                        <td><?= htmlspecialchars($planejamento['ano_escolar']) ?></td>
                        <td><?= htmlspecialchars($planejamento['periodo']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($planejamento['data_criacao'])) ?></td>
                        <td>
                            <a href="planejador-visualizar.php?id=<?= $planejamento['id'] ?>" class="btn btn-view">Visualizar</a>
                            <a href="planejador-editar.php?id=<?= $planejamento['id'] ?>" class="btn btn-edit">Editar</a>
                            <a href="planejador-excluir.php?id=<?= $planejamento['id'] ?>" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja excluir este planejamento?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-results">
            <p>Nenhum planejamento encontrado.</p>
            <div class="button-container">
                <button type="button" onclick="window.location.href='index.php'">Criar Novo Planejamento</button>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>