<?php
// videos.php - Solução alternativa para embedar vídeos do YouTube sem API

// Configurações
$channels = [
    'matematica' => [
        'url' => 'https://www.youtube.com/@ProfSilvester/videos',
        'titulo' => 'Geografia',
        'subtitulo' => 'Conteúdo exclusivo do canal Prof. Silvester',
        'cor' => '#0057B7'
    ],
    'fisica' => [
        'url' => 'https://www.youtube.com/@professorboaro/videos',
        'titulo' => 'Física',
        'subtitulo' => 'Conteúdo exclusivo do canal Prof. Boaro',
        'cor' => '#D32F2F'
    ],
    'biologia' => [
        'url' => 'https://www.youtube.com/@professorsamuelcunha/videos',
        'titulo' => 'Biologia',
        'subtitulo' => 'Conteúdo exclusivo do canal Prof. Samuel Cunha',
        'cor' => '#388E3C'
    ],
    'quimica' => [
        'url' => 'https://www.youtube.com/@paulovalim/videos',
        'titulo' => 'Química',
        'subtitulo' => 'Conteúdo exclusivo do canal Prof. Paulo Valim',
        'cor' => '#7B1FA2'
    ],
    'ingles-infantil' => [
        'url' => 'https://www.youtube.com/@AmigoMumu/videos',
        'titulo' => 'Inglês Infantil',
        'subtitulo' => 'Conteúdo educativo do canal Amigo Mumu',
        'cor' => '#FFA000'
    ],
    'literatura-infantil' => [
        'url' => 'https://www.youtube.com/@HistoriasKids/videos',
        'titulo' => 'Literatura Infantil',
        'subtitulo' => 'Contos e histórias do canal Histórias Kids',
        'cor' => '#0288D1'
    ],
    'historia' => [
        'url' => 'https://www.youtube.com/@hojeediadehistoria4052/videos',
        'titulo' => 'História',
        'subtitulo' => 'Aulas e conteúdos do canal Hoje é Dia de História',
        'cor' => '#5D4037'
    ]
];

$maxVideos = 50;
$cacheTime = 3600; // 1 hora de cache

// Função principal com tratamento de erros
function getYouTubeVideos($channelUrl, $maxVideos, $cacheFile, $cacheTime) {
    // Verifica cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // Busca os vídeos
    $videos = fetchVideosFromYouTube($channelUrl, $maxVideos);
    
    // Se conseguiu buscar, salva no cache
    if (!empty($videos)) {
        file_put_contents($cacheFile, json_encode($videos));
    }
    
    return $videos;
}

// Função para extrair vídeos da página
function fetchVideosFromYouTube($channelUrl, $maxVideos) {
    $options = [
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
        ]
    ];
    
    try {
        $context = stream_context_create($options);
        $html = file_get_contents($channelUrl, false, $context);
        
        if (!$html) {
            throw new Exception("Não foi possível acessar o YouTube");
        }

        // Extrai dados JSON
        preg_match('/var ytInitialData = (\{.*?\});<\/script>/s', $html, $matches);
        
        if (!isset($matches[1])) {
            throw new Exception("Dados dos vídeos não encontrados");
        }

        $data = json_decode($matches[1], true);
        $videos = [];
        
        // Navega na estrutura complexa do YouTube
        $contents = $data['contents']['twoColumnBrowseResultsRenderer']['tabs'][1]['tabRenderer']['content']['richGridRenderer']['contents'];
        
        foreach ($contents as $index => $content) {
            if ($index >= $maxVideos) break;
            
            if (isset($content['richItemRenderer']['content']['videoRenderer'])) {
                $video = $content['richItemRenderer']['content']['videoRenderer'];
                
                // Tratamento para thumbnails
                $thumbnails = $video['thumbnail']['thumbnails'];
                $thumbnail = end($thumbnails)['url'];
                
                $videos[] = [
                    'title' => $video['title']['runs'][0]['text'],
                    'thumbnail' => $thumbnail,
                    'link' => 'https://www.youtube.com' . $video['navigationEndpoint']['commandMetadata']['webCommandMetadata']['url'],
                    'video_id' => $video['videoId'],
                    'views' => $video['viewCountText']['simpleText'] ?? 'Visualizações indisponíveis',
                    'date' => $video['publishedTimeText']['simpleText'] ?? 'Data indisponível'
                ];
            }
        }
        
        return $videos;
        
    } catch (Exception $e) {
        // Log do erro (opcional)
        // file_put_contents('youtube_error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return [];
    }
}

// Obtém o tema da URL ou define padrão como Geografia
$tema = isset($_GET['tema']) && array_key_exists($_GET['tema'], $channels) ? $_GET['tema'] : 'matematica';
$channelConfig = $channels[$tema];
$cacheFile = 'youtube_cache_' . $tema . '.json';

// Obtém os vídeos (com cache)
$videos = getYouTubeVideos($channelConfig['url'], $maxVideos, $cacheFile, $cacheTime);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vídeos Educacionais - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --cor-primaria: <?= $channelConfig['cor'] ?>;
            --cor-primaria-escura: <?= adjustBrightness($channelConfig['cor'], -20) ?>;
            --cor-primaria-clara: <?= adjustBrightness($channelConfig['cor'], 20) ?>;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
            background-color: var(--cor-primaria);
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            margin: 0;
            font-size: 2.2rem;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .video-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .video-thumbnail {
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
        }
        .video-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }
        .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: rgba(255, 0, 0, 0.8);
            font-size: 3rem;
            opacity: 0.8;
            transition: opacity 0.3s, transform 0.3s;
        }
        .video-card:hover .play-icon {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
        }
        .video-info {
            padding: 15px;
        }
        .video-title {
            font-size: 1rem;
            margin: 0 0 10px 0;
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .video-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--cor-primaria);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: var(--cor-primaria-escura);
        }
        .error-message {
            text-align: center;
            padding: 20px;
            background-color: #ffebee;
            border-radius: 8px;
            color: #c62828;
            margin-bottom: 20px;
        }
        .theme-selector {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .theme-btn {
            padding: 8px 16px;
            background-color: #e0e0e0;
            color: #333;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
            white-space: nowrap;
        }
        .theme-btn:hover {
            background-color: #d0d0d0;
            transform: translateY(-2px);
        }
        .theme-btn.active {
            background-color: var(--cor-primaria);
            color: white;
            border-color: var(--cor-primaria-escura);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .theme-icon {
            margin-right: 5px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            font-size: 1.2rem;
            color: var(--cor-primaria);
        }
        @media (max-width: 768px) {
            .videos-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .theme-selector {
                gap: 8px;
            }
            .theme-btn {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
            .header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Vídeos Educacionais - <?= $channelConfig['titulo'] ?></h1>
            <p><?= $channelConfig['subtitulo'] ?></p>
        </div>
        
        <div class="theme-selector">
            <a href="?tema=Geografia" class="theme-btn <?= $tema === 'Geografia' ? 'active' : '' ?>">
                <i class="fa-solid fa-globe theme-icon"></i>Geografia
            </a>
            <a href="?tema=fisica" class="theme-btn <?= $tema === 'fisica' ? 'active' : '' ?>">
                <i class="fas fa-atom theme-icon"></i>Física
            </a>
            <a href="?tema=biologia" class="theme-btn <?= $tema === 'biologia' ? 'active' : '' ?>">
                <i class="fas fa-dna theme-icon"></i>Biologia
            </a>
            <a href="?tema=quimica" class="theme-btn <?= $tema === 'quimica' ? 'active' : '' ?>">
                <i class="fas fa-flask theme-icon"></i>Química
            </a>
            <a href="?tema=ingles-infantil" class="theme-btn <?= $tema === 'ingles-infantil' ? 'active' : '' ?>">
                <i class="fas fa-language theme-icon"></i>Inglês Infantil
            </a>
            <a href="?tema=literatura-infantil" class="theme-btn <?= $tema === 'literatura-infantil' ? 'active' : '' ?>">
                <i class="fas fa-book-open theme-icon"></i>Literatura Infantil
            </a>
            <a href="?tema=historia" class="theme-btn <?= $tema === 'historia' ? 'active' : '' ?>">
                <i class="fas fa-landmark theme-icon"></i>História
            </a>
        </div>
        
        <?php if (!empty($videos)): ?>
            <div class="videos-grid">
                <?php foreach ($videos as $video): ?>
                    <div class="video-card">
                        <a href="<?= htmlspecialchars($video['link']) ?>" target="_blank" class="video-link">
                            <div class="video-thumbnail">
                                <img src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                                <div class="play-icon">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                            </div>
                            <div class="video-info">
                                <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                                <div class="video-meta">
                                    <span><?= htmlspecialchars($video['views']) ?></span>
                                    <span><?= htmlspecialchars($video['date']) ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="error-message">
                <p>Não foi possível carregar os vídeos no momento. Por favor, tente novamente mais tarde.</p>
                <p>Você pode acessar diretamente o <a href="<?= $channelConfig['url'] ?>" target="_blank" style="color: var(--cor-primaria);">canal no YouTube</a>.</p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center;">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Voltar para a página inicial
            </a>
        </div>
    </div>
    
    <?php
    // Função para ajustar brilho da cor (usada no CSS)
    function adjustBrightness($hex, $steps) {
        $steps = max(-255, min(255, $steps));
        
        // Converte HEX para RGB
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));
        
        // Ajusta os componentes
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Converte de volta para HEX
        $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
        $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
        $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
        
        return '#' . $r_hex . $g_hex . $b_hex;
    }
    ?>
</body>
</html>