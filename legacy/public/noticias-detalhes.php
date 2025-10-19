<?php
// Funções para buscar notícias
function formatDate($dateStr, $source) {
    if ($source === 'Porvir') {
        $date = DateTime::createFromFormat('d/m/Y', trim($dateStr));
        return $date ? $date : new DateTime();
    } else { // G1
        if (strpos($dateStr, 'h') !== false) {
            return new DateTime(); // Hoje
        }
        $date = DateTime::createFromFormat('d/m/Y', trim($dateStr));
        return $date ? $date : new DateTime();
    }
}

function fetchNews($limit = 30) {
    $news = [];
    
    // Porvir
    $porvirUrl = "https://porvir.org/ultimas/";
    $porvirHtml = @file_get_contents($porvirUrl);
    
    if ($porvirHtml !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($porvirHtml);
        $xpath = new DOMXPath($dom);
        
        $titles = $xpath->query("//h3[contains(@class, 'post-title')]/a");
        $dates = $xpath->query("//div[contains(@class, 'post-date')]");
        $links = $xpath->query("//h3[contains(@class, 'post-title')]/a/@href");
        $images = $xpath->query("//div[contains(@class, 'post-thumbnail')]/img/@src");
        
        for ($i = 0; $i < min($limit/2, $titles->length); $i++) {
            $date = formatDate($dates->item($i)->nodeValue, 'Porvir');
            $news[] = [
                'day' => $date->format('d'),
                'month' => $date->format('M'),
                'title' => trim($titles->item($i)->nodeValue),
                'link' => $links->item($i)->nodeValue,
                'source' => 'Porvir',
                'full_date' => $date->format('Y-m-d'),
                'image' => $images->item($i) ? $images->item($i)->nodeValue : 'img/placeholder-news.jpg'
            ];
        }
    }
    
    // G1 Educação
    $g1Url = "https://g1.globo.com/educacao/";
    $g1Html = @file_get_contents($g1Url);
    
    if ($g1Html !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($g1Html);
        $xpath = new DOMXPath($dom);
        
        $titles = $xpath->query("//a[contains(@class, 'feed-post-link')]");
        $dates = $xpath->query("//span[contains(@class, 'feed-post-datetime')]");
        $images = $xpath->query("//img[contains(@class, 'bstn-fd-picture-image')]/@src");
        
        for ($i = 0; $i < min($limit/2, $titles->length); $i++) {
            $dateStr = $dates->item($i) ? $dates->item($i)->nodeValue : 'hoje';
            $date = formatDate($dateStr, 'G1');
            $news[] = [
                'day' => $date->format('d'),
                'month' => $date->format('M'),
                'title' => trim($titles->item($i)->nodeValue),
                'link' => $titles->item($i)->getAttribute('href'),
                'source' => 'G1 Educação',
                'full_date' => $date->format('Y-m-d'),
                'image' => $images->item($i) ? $images->item($i)->nodeValue : 'img/placeholder-news.jpg'
            ];
        }
    }
    
    // Ordenar por data mais recente
    usort($news, function($a, $b) {
        return strtotime($b['full_date']) - strtotime($a['full_date']);
    });
    
    return array_slice($news, 0, $limit);
}

// Buscar mais notícias
$allNews = fetchNews(30);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mais Notícias sobre Educação - Universo do Saber</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    .news-details {
        padding: 60px 0;
        background-color: #f9f9f9;
    }
    
    .main-title {
        color: #0057B7;
        text-align: center;
        margin-bottom: 15px;
    }
    
    .subtitle {
        text-align: center;
        color: #555;
        margin-bottom: 40px;
        font-size: 1.2rem;
    }
    
    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .news-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }
    
    .news-card:hover {
        transform: translateY(-5px);
    }
    
    .news-image {
        height: 200px;
        overflow: hidden;
    }
    
    .news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .news-card:hover .news-image img {
        transform: scale(1.05);
    }
    
    .news-content {
        padding: 20px;
    }
    
    .news-date-source {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: #777;
    }
    
    .news-date {
        color: #0057B7;
        font-weight: 600;
    }
    
    .news-content h3 {
        margin-top: 0;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .news-content h3 a {
        color: #333;
        text-decoration: none;
    }
    
    .news-content h3 a:hover {
        color: #0057B7;
    }
    
    .read-more {
        display: inline-block;
        margin-top: 15px;
        color: #0057B7;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .read-more:hover {
        text-decoration: underline;
    }
    
    .read-more i {
        margin-left: 5px;
        font-size: 0.8rem;
    }
    
    .back-to-home {
        text-align: center;
        margin-top: 40px;
    }
    
    @media (max-width: 768px) {
        .news-grid {
            grid-template-columns: 1fr;
        }
        
        .news-image {
            height: 150px;
        }
    }
    </style>
</head>
<body>
    <!-- Inclua o mesmo header do index.php aqui -->
    
    <section class="news-details">
        <div class="container">
            <h1 class="main-title">Notícias sobre Educação</h1>
            <p class="subtitle">As últimas notícias dos principais portais educacionais</p>
            
            <div class="news-grid">
                <?php foreach ($allNews as $newsItem): ?>
                <div class="news-card">
                    <div class="news-image">
                        <img src="<?= htmlspecialchars($newsItem['image']) ?>" alt="<?= htmlspecialchars($newsItem['title']) ?>">
                    </div>
                    <div class="news-content">
                        <div class="news-date-source">
                            <span class="news-date"><?= htmlspecialchars($newsItem['day']) ?>/<?= htmlspecialchars($newsItem['month']) ?></span>
                            <span class="news-source"><?= htmlspecialchars($newsItem['source']) ?></span>
                        </div>
                        <h3><a href="<?= htmlspecialchars($newsItem['link']) ?>" target="_blank"><?= htmlspecialchars($newsItem['title']) ?></a></h3>
                        <a href="<?= htmlspecialchars($newsItem['link']) ?>" target="_blank" class="read-more">Ler notícia completa <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="back-to-home">
                <a href="index.php" class="btn-primary"><i class="fas fa-arrow-left"></i> Voltar para a página inicial</a>
            </div>
        </div>
    </section>
    
    <!-- Inclua o mesmo footer do index.php aqui -->
</body>
</html>