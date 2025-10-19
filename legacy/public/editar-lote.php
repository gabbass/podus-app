<?php
require('sessao-adm-professor.php');
require('conexao.php');

// Verifica se há IDs para edição
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    header("Location: questoes");
    exit();
}

$ids = explode(',', $_GET['ids']);
$ids = array_filter($ids, 'is_numeric'); // Filtra apenas números

if (empty($ids)) {
    header("Location: questoes");
    exit();
}

// Busca os valores atuais das questões selecionadas para referência
try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT DISTINCT materia, grau_escolar, assunto, status FROM questoes WHERE id IN ($placeholders)";
    $stmt = $conexao->prepare($sql);
    $stmt->execute($ids);
    $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extrai valores únicos para cada campo
    $materias = array_unique(array_column($valores, 'materia'));
    $graus_escolares = array_unique(array_column($valores, 'grau_escolar'));
    $assuntos = array_unique(array_column($valores, 'assunto'));
    $status_options = array_unique(array_column($valores, 'status'));
    
} catch (PDOException $e) {
    die("Erro ao buscar questões: " . $e->getMessage());
}

// Processa o formulário de edição em lote
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos_para_atualizar = [];
    $params = [];
    
    if (!empty($_POST['materia'])) {
        $campos_para_atualizar[] = "materia = :materia";
        $params[':materia'] = $_POST['materia'];
    }
    
    if (!empty($_POST['assunto'])) {
        $campos_para_atualizar[] = "assunto = :assunto";
        $params[':assunto'] = $_POST['assunto'];
    }
    
    if (!empty($_POST['grau_escolar'])) {
        $campos_para_atualizar[] = "grau_escolar = :grau_escolar";
        $params[':grau_escolar'] = $_POST['grau_escolar'];
    }
    
    if (!empty($_POST['status'])) {
        $campos_para_atualizar[] = "status = :status";
        $params[':status'] = $_POST['status'];
    }
    
    // Só executa se houver campos para atualizar
    if (!empty($campos_para_atualizar)) {
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE questoes SET " . implode(', ', $campos_para_atualizar) . " WHERE id IN ($placeholders)";
            
            $stmt = $conexao->prepare($sql);
            
            // Adiciona os parâmetros na ordem correta
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Adiciona os IDs
            foreach ($ids as $index => $id) {
                $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            header("Location: questoes?editado_lote=1&itens=" . count($ids));
            exit();
            
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar questões: " . $e->getMessage();
        }
    } else {
        $erro = "Nenhum campo selecionado para atualização.";
    }
}

// Busca valores existentes para os datalists
try {
    $sql_materias = "SELECT DISTINCT materia FROM questoes ORDER BY materia";
    $todas_materias = $conexao->query($sql_materias)->fetchAll(PDO::FETCH_COLUMN);
    
    $sql_graus = "SELECT DISTINCT grau_escolar FROM questoes ORDER BY grau_escolar";
    $todos_graus = $conexao->query($sql_graus)->fetchAll(PDO::FETCH_COLUMN);
    
    $sql_assuntos = "SELECT DISTINCT assunto FROM questoes ORDER BY assunto";
    $todos_assuntos = $conexao->query($sql_assuntos)->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Erro ao buscar opções: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edição em Lote - Universo do Saber</title>
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
        .container {
            max-width: 1000px;
            margin: 0 auto;
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
        }
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-box {
            background-color: rgba(0, 87, 183, 0.1);
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-gray);
        }


.form-group input[type="text"],
    .form-group input[list],
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--medium-gray);
        border-radius: 4px;
        font-size: 1rem;
    }
    
    
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .checkbox-group input {
            margin-right: 10px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-col {
            flex: 1;
        }
        .error-message {
            color: #dc3545;
            margin-top: 5px;
            font-size: 0.9rem;
        }
        .current-value {
            font-size: 0.9rem;
            color: var(--dark-gray);
            font-style: italic;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Edição em Lote</h1>
                <p>Editando <?php echo count($ids); ?> questão(ões) selecionada(s)</p>
            </div>
            <a href="pesquisar-questoes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="info-box">
            <p><strong>Instruções:</strong> Preencha apenas os campos que deseja atualizar. Os campos deixados em branco não serão modificados.</p>
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="error-message"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="materia">Matéria</label>
                        <input list="materias" id="materia" name="materia">
                        <datalist id="materias">
                            <?php foreach ($todas_materias as $m): ?>
                                <option value="<?php echo htmlspecialchars($m); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (count($materias) === 1): ?>
                            <div class="current-value">Valor atual: <?php echo htmlspecialchars(reset($materias)); ?></div>
                        <?php elseif (count($materias) > 1): ?>
                            <div class="current-value">Valores atuais: <?php echo htmlspecialchars(implode(', ', $materias)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="assunto">Assunto</label>
                        <input list="assuntos" id="assunto" name="assunto">
                        <datalist id="assuntos">
                            <?php foreach ($todos_assuntos as $a): ?>
                                <option value="<?php echo htmlspecialchars($a); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (count($assuntos) === 1): ?>
                            <div class="current-value">Valor atual: <?php echo htmlspecialchars(reset($assuntos)); ?></div>
                        <?php elseif (count($assuntos) > 1): ?>
                            <div class="current-value">Valores atuais: <?php echo htmlspecialchars(implode(', ', $assuntos)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="grau_escolar">Grau Escolar</label>
                        <input list="grausEscolares" id="grau_escolar" name="grau_escolar">
                        <datalist id="grausEscolares">
                            <?php foreach ($todos_graus as $g): ?>
                                <option value="<?php echo htmlspecialchars($g); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (count($graus_escolares) === 1): ?>
                            <div class="current-value">Valor atual: <?php echo htmlspecialchars(reset($graus_escolares)); ?></div>
                        <?php elseif (count($graus_escolares) > 1): ?>
                            <div class="current-value">Valores atuais: <?php echo htmlspecialchars(implode(', ', $graus_escolares)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">-- Não alterar --</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                        <?php if (count($status_options) === 1): ?>
                            <div class="current-value">Valor atual: <?php echo htmlspecialchars(reset($status_options)); ?></div>
                        <?php elseif (count($status_options) > 1): ?>
                            <div class="current-value">Valores atuais: <?php echo htmlspecialchars(implode(', ', $status_options)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Aplicar Edições
                </button>
            </div>
        </form>
    </div>
</body>
</html>