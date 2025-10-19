<?php 
require('conexao.php');

// Verifica se o ID foi passado na URL
if (!isset($_GET['id'])) {
    header('Location: artigos.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: artigos.php');
    exit();
}

try {
    // Busca o artigo no banco de dados
    $stmt = $conexao->prepare("SELECT * FROM artigo WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$artigo) {
        header('Location: artigos.php');
        exit();
    }
    
    // Formata a data para exibição
    $dataFormatada = date('d/m/Y H:i', $artigo['data']);
    
} catch (PDOException $e) {
    die("Erro ao carregar artigo.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artigo['nome']) ?> - Portal Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        
        .article-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .article-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .article-header h1 {
            color: #0057B7;
            margin-bottom: 10px;
        }
        
        .article-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .article-image {
            text-align: center;
            margin: 20px 0;
        }
        
        .article-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            text-align:justify;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #0057B7;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="article-container">
        <div class="article-header">
            <h1><?= htmlspecialchars($artigo['nome']) ?></h1>
            <div class="article-meta">
                <span><i class="far fa-calendar-alt"></i> <?= $dataFormatada ?></span>
                <?php if (!empty($artigo['fonte'])): ?>
                    <span style="margin-left: 15px;"><i class="fas fa-book"></i> Fonte: <?= htmlspecialchars($artigo['fonte']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($artigo['imagem'])): ?>
            <div class="article-image">
                <img src="<?= htmlspecialchars($artigo['imagem']) ?>" alt="<?= htmlspecialchars($artigo['nome']) ?>">
            </div>
        <?php endif; ?>
        
        <div class="article-content">
            <?= nl2br(htmlspecialchars($artigo['texto'])) ?>
        </div>
        
        <a href="artigos.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar para Artigos</a>
    </div>
</body>
</html>