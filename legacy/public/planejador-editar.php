<?php
require('conexao.php');

if (!isset($_GET['id']) {
    header('Location: planejador-pesquisar.php');
    exit();
}

$id = $_GET['id'];

// Buscar o planejamento no banco de dados
$query = "SELECT * FROM planejador WHERE id = ?";
$stmt = $conexao->prepare($query);
$stmt->execute([$id]);
$planejamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$planejamento) {
    header('Location: planejador-pesquisar.php');
    exit();
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dados = [
            ':objetivo_geral' => $_POST['objetivoGeral'],
            ':objetivo_especifico' => $_POST['objetivoEspecifico'],
            ':unidade_tematica' => $_POST['unidadeTematica'],
            ':objetos_conhecimento' => $_POST['objConhecimento'],
            ':habilidades' => $_POST['habilidades'],
            ':conteudos' => $_POST['conteudos'],
            ':metodologia' => $_POST['metodologia'],
            ':observacao' => $_POST['observacao'],
            ':escola' => $_POST['escola'],
            ':professor' => $_POST['professor'],
            ':ano_escolar' => $_POST['anoEscolar'],
            ':carga_horaria' => $_POST['cargaHoraria'],
            ':periodo' => $_POST['periodo'],
            ':id' => $id
        ];

        $query = "UPDATE planejador SET 
            objetivo_geral = :objetivo_geral,
            objetivo_especifico = :objetivo_especifico,
            unidade_tematica = :unidade_tematica,
            objetos_conhecimento = :objetos_conhecimento,
            habilidades = :habilidades,
            conteudos = :conteudos,
            metodologia = :metodologia,
            observacao = :observacao,
            escola = :escola,
            professor = :professor,
            ano_escolar = :ano_escolar,
            carga_horaria = :carga_horaria,
            periodo = :periodo
            WHERE id = :id";

        $stmt = $conexao->prepare($query);
        $stmt->execute($dados);

        $mensagem = "Planejamento atualizado com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar planejamento: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Planejamento</title>
    <style>
        /* Manter os mesmos estilos do arquivo principal */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 10px;
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

        .info-section {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .info-section p {
            margin-bottom: 8px;
        }

        h2 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .table-container {
            margin-bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: top;
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

        textarea, input[type="text"] {
            width: 100%;
            height: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-family: inherit;
            font-size: 0.9rem;
        }

        input[type="text"] {
            height: auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
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
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }

        button:hover, .btn:hover {
            background-color: #45a049;
        }

        .btn-cancel {
            background-color: #f44336;
        }

        .btn-cancel:hover {
            background-color: #d32f2f;
        }

        .mensagem {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .sucesso {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .erro {
            background-color: #f2dede;
            color: #a94442;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            
            h2 {
                font-size: 1.2rem;
            }
            
            .table-container {
                padding: 10px;
            }
            
            th, td {
                padding: 8px;
                font-size: 0.9rem;
            }
            
            .info-section {
                padding: 12px;
            }
            
            textarea {
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Editar Planejamento</h1>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem sucesso"><?= $mensagem ?></div>
    <?php endif; ?>
    
    <?php if (!empty($erro)): ?>
        <div class="mensagem erro"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="info-section">
            <h2>Informações Gerais</h2>
            <div class="form-group">
                <label for="escola">Escola:</label>
                <input type="text" id="escola" name="escola" value="<?= htmlspecialchars($planejamento['escola']) ?>" required>
            </div>
            <div class="form-group">
                <label for="professor">Professor:</label>
                <input type="text" id="professor" name="professor" value="<?= htmlspecialchars($planejamento['professor']) ?>" required>
            </div>
            <div class="form-group">
                <label for="anoEscolar">Ano Escolar:</label>
                <input type="text" id="anoEscolar" name="anoEscolar" value="<?= htmlspecialchars($planejamento['ano_escolar']) ?>" required>
            </div>
            <div class="form-group">
                <label for="cargaHoraria">Carga Horária:</label>
                <input type="text" id="cargaHoraria" name="cargaHoraria" value="<?= htmlspecialchars($planejamento['carga_horaria']) ?>" required>
            </div>
            <div class="form-group">
                <label for="periodo">Período:</label>
                <input type="text" id="periodo" name="periodo" value="<?= htmlspecialchars($planejamento['periodo']) ?>" required>
            </div>
        </div>

        <div class="info-section">
            <h2>Objetivos</h2>
            <div class="form-group">
                <label for="objetivoGeral">Objetivo Geral:</label>
                <textarea id="objetivoGeral" name="objetivoGeral" required><?= htmlspecialchars($planejamento['objetivo_geral']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="objetivoEspecifico">Objetivos Específicos:</label>
                <textarea id="objetivoEspecifico" name="objetivoEspecifico" required><?= htmlspecialchars($planejamento['objetivo_especifico']) ?></textarea>
            </div>
        </div>

        <div class="table-container">
            <h2>Unidade Temática</h2>
            <table>
                <thead>
                    <tr>
                        <th>Unidade Temática</th>
                        <th>Objetos de conhecimento</th>
                        <th>Habilidades</th>
                        <th>Conteúdos</th>
                        <th>Metodologia</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text" name="unidadeTematica" value="<?= htmlspecialchars($planejamento['unidade_tematica']) ?>" required>
                        </td>
                        <td>
                            <textarea name="objConhecimento" required><?= htmlspecialchars($planejamento['objetos_conhecimento']) ?></textarea>
                        </td>
                        <td>
                            <textarea name="habilidades" required><?= htmlspecialchars($planejamento['habilidades']) ?></textarea>
                        </td>
                        <td>
                            <textarea name="conteudos" required><?= htmlspecialchars($planejamento['conteudos']) ?></textarea>
                        </td>
                        <td>
                            <textarea name="metodologia" required><?= htmlspecialchars($planejamento['metodologia']) ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="info-section">
            <div class="form-group">
                <label for="observacao">Observação:</label>
                <textarea id="observacao" name="observacao"><?= htmlspecialchars($planejamento['observacao']) ?></textarea>
            </div>
        </div>

        <div class="button-container">
            <button type="submit">Salvar Alterações</button>
            <a href="planejador-visualizar.php?id=<?= $id ?>" class="btn">Cancelar</a>
            <a href="planejador-pesquisar.php" class="btn btn-cancel">Voltar para Lista</a>
        </div>
    </form>
</body>
</html>