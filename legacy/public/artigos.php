<?php 
require('conexao.php');

try {
    // Busca todos os artigos no banco de dados
    $stmt = $conexao->query("SELECT id, nome, texto, imagem, fonte, data FROM artigo ORDER BY data DESC");
    $artigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar artigos.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - Portal Universo do Saber</title>
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
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background: #0057B7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: #003d82;
        }
        
        h1 {
            color: #0057B7;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .articles-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .article-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .article-image {
            height: 180px;
            overflow: hidden;
        }
        
        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .article-content {
            padding: 15px;
        }
        
        .article-content h3 {
            margin: 0 0 10px;
            color: #0057B7;
        }
        
        .article-content p {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .article-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 0.8rem;
        }
        
        .read-more {
            display: inline-block;
            color: #0057B7;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .no-articles {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Voltar para a Página Inicial
    </a>
    
    <h1>Artigos Educacionais</h1>
    
    <div class="articles-container">
        <?php if (count($artigos) > 0): ?>
            <?php foreach ($artigos as $artigo): ?>
                <div class="article-card">
                    <?php if (!empty($artigo['imagem'])): ?>
                        <div class="article-image">
                            <img src="<?= htmlspecialchars($artigo['imagem']) ?>" alt="<?= htmlspecialchars($artigo['nome']) ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="article-content">
                        <h3><?= htmlspecialchars($artigo['nome']) ?></h3>
                        
                        <?php 
                        $textoResumo = strip_tags($artigo['texto']);
                        $textoResumo = substr($textoResumo, 0, 120);
                        if (strlen($artigo['texto']) > 120) {
                            $textoResumo .= '...';
                        }
                        echo '<p>' . htmlspecialchars($textoResumo) . '</p>';
                        ?>
                        
                        <div class="article-meta">
                            <?php if (!empty($artigo['fonte'])): ?>
                            <span><i class="fas fa-book"></i> <?= htmlspecialchars(mb_strimwidth($artigo['fonte'], 0, 30, '...')) ?></span>
                            <?php endif; ?>
                            <a href="artigo-detalhes.php?id=<?= $artigo['id'] ?>" class="read-more">Ler mais →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-articles">
                <p><i class="fas fa-book-open"></i> Nenhum artigo disponível no momento.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>