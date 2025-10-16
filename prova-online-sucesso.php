<?php
require('sessao-professor.php');
require('conexao.php');

$codigo_prova = $_GET['codigo'] ?? '';

if (empty($codigo_prova)) {
    header('Location: gestao-questoes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prova Online Criada - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Adicione seus estilos aqui */
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .code-display {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Prova Online Criada com Sucesso!</h1>
        <p>Compartilhe o código abaixo com seus alunos para que eles possam acessar a prova:</p>
        
        <div class="code-display">
            <?php echo htmlspecialchars($codigo_prova); ?>
        </div>
        
        <p>Os alunos podem acessar a prova em: <strong>seusite.com.br/prova-aluno.php</strong></p>
        
        <a href="gestao-questoes.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Voltar para Gestão de Questões
        </a>
    </div>
</body>
</html>