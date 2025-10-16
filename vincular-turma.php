<?php
require('sessao-professor.php');
require('conexao.php');

// Verifica se há questões selecionadas
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    header('Location: questoes-professor.php?erro=nenhuma_questao_selecionada');
}


$questoes_ids = explode(',', $_GET['ids']);

// Consulta as questões selecionadas
$placeholders = implode(',', array_fill(0, count($questoes_ids), '?'));
$sql = "SELECT id, materia, assunto FROM questoes WHERE id IN ($placeholders)";
$stmt = $conexao->prepare($sql);
$stmt->execute($questoes_ids);
$questoes_selecionadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta as turmas do professor
$sql_turmas = "SELECT id, nome FROM turmas WHERE login = ?";
$stmt_turmas = $conexao->prepare($sql_turmas);
$stmt_turmas->execute([$_SESSION['login']]);
$turmas = $stmt_turmas->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Questões a Turma - Universo do Saber</title>
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
        
        /* Formulário */
        .form-container {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(0, 87, 183, 0.2);
        }
        
        /* Lista de questões */
        .questoes-list {
            margin-top: 30px;
            border-top: 1px solid var(--medium-gray);
            padding-top: 20px;
        }
        
        .questoes-list h3 {
            margin-bottom: 15px;
            color: var(--dark-blue);
        }
        
        .questao-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .questao-item:hover {
            background-color: rgba(0, 87, 183, 0.05);
        }
        
        .questao-info {
            flex: 1;
        }
        
        .questao-id {
            font-weight: 600;
            color: var(--primary-blue);
            margin-right: 10px;
        }
        
        /* Mensagens de erro/sucesso */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Top Navigation -->
        <div class="top-nav">
            <a href="questoes-professor" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            
           
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Vincular Questões a Turma</h1>
                    <p>Selecione a turma e defina os detalhes da prova</p>
                </div>
            </div>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="form-container">
                <div class="form-group">
                    <label for="turma_id">Turma</label>
                    <select name="turma_id" id="turma_id" class="form-control" required>
                        <option value="">Selecione uma turma...</option>
                        <?php foreach ($turmas as $turma): ?>
                            <option value="<?php echo $turma['id']; ?>"><?php echo htmlspecialchars($turma['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="titulo_prova">Título da Prova</label>
                    <input type="text" name="titulo_prova" id="titulo_prova" class="form-control" required placeholder="Ex: Prova Bimestral de Matemática">
                </div>
                
                <div class="form-group">
                    <label for="data_prova">Data da Prova</label>
                    <input type="date" name="data_prova" id="data_prova" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="questoes-list">
                    <h3>Questões Selecionadas (<?php echo count($questoes_selecionadas); ?>)</h3>
                    
                    <?php foreach ($questoes_selecionadas as $questao): ?>
                        <div class="questao-item">
                            <div class="questao-info">
                                <span class="questao-id">#<?php echo $questao['id']; ?></span>
                                <span><?php echo htmlspecialchars($questao['materia']); ?> - <?php echo htmlspecialchars($questao['assunto']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-link"></i> Vincular Questões
                </button>
            </form>
        </div>
    </div>

    <script>
        // Define a data mínima como hoje
        document.getElementById('data_prova').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>