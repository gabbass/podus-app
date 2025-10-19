<?php
require("conexao.php");

$recaptchaCheckboxKey = LegacyConfig::recaptchaSiteKey('checkbox') ?? '';
$recaptchaV3Key = LegacyConfig::recaptchaSiteKey('v3') ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universo do Saber - Educação de Qualidade</title>
		<!-- Favicon tradicional (browsers desktop) -->
		<link rel="icon" href="img/icons/favicon.ico" type="image/x-icon">

		<!-- PNGs para navegadores modernos -->
		<link rel="icon" type="image/png" sizes="16x16" href="img/icons/favicon-16x16.png">
		<link rel="icon" type="image/png" sizes="32x32" href="img/icons/favicon-32x32.png">

		<!-- Favicon para dispositivos Apple (iOS/iPadOS) -->
		<link rel="apple-touch-icon" sizes="180x180" href="img/icons/apple-touch-icon.png">

		<!-- Favicons para Android/Chrome -->
		<link rel="icon" type="image/png" sizes="192x192" href="img/icons/android-chrome-192x192.png">
		<link rel="icon" type="image/png" sizes="512x512" href="img/icons/android-chrome-512x512.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> (47) 9-8886-8374</span>
                <span><i class="fas fa-envelope"></i> contato@portaluniversodosaber.com.br</span>
            </div>
            <div class="social-icons">
                        <a href="https://www.facebook.com/profile.php?id=61575735779692&locale=pt_BR" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/portaluniversodosaberoficial/" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/everton-leite-54b997335?originalSubdomain=br" target='_blank'><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>

 <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index">
                     <img src="img/logo.png" alt="Logo da Instituição">
                </a>
            </div>

<nav class="main-nav">
    <ul>
        <li><a href="index">Home</a></li>
        <li class="dropdown">
            <a href="#institucional">Institucional </i></a>
           
        </li>
        <li class="dropdown">
            <a href="#planos">Planos</a>

        </li>
        <li><a href="#artigos-noticias">Notícias</a></li>
        <li><a href="#artigos-noticias">Artigos</a></li>
        <li><a href="#contato">Contato</a></li>
        <li class="login-btn"><a href="index-portal" target='_blank'><i class="fas fa-user"></i> Entrar</a></li>
    </ul>
</nav>

            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero Slider -->
    <section class="hero-slider">
        <div class="slider-container">
            <div class="slide active" style="background-image: linear-gradient(rgba(0, 87, 183, 0.8), rgba(0, 87, 183, 0.8)), url('img/slide1.webp');">
                <div class="container">
                    <div class="slide-content 1">
                        <h1>Educação Transformadora</h1>
                        <p>Prepare-se para o futuro com uma instituição comprometida com a excelência acadêmica</p>
                        <div class="slide-buttons">
                            <a href="#ferramentas"  id="btn1-slide-0" class="btn-primary" style='background-color:#FFA500;color:black'>Conheça Nossas Ferramentas</a>
                            <a href="#planos" id="btn2-slide-0" class="btn-secondary botaoBanner" style='color:white;border-color:white'>Assine Aqui</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!--<div class="slide" style="background-color: linear-gradient(rgba(0, 87, 183, 0.8), rgba(0, 87, 183, 0.8)), url('images/slide2.jpg');"> -->
             <div class="slide" style="background-image: linear-gradient(rgba(0, 87, 183, 0.8), rgba(0, 87, 183, 0.8)), url('img/slide2.webp');">
                <div class="container">
                    <div class="slide-content 2">
                        <h1>Soluções Inovadoras</h1>
                        <p>Onde a tecnologia encontra a educação para transformar o aprendizado!</p>
                        <div class="slide-buttons">
                            <a href="#institucional" id="btn1-slide-1" class="btn-primary" style='background-color:#FFA500;color:black'>Sobre o Portal</a>
                            <a href="#depoimentos" id="btn2-slide-1" class="btn-secondary botaoBanner" style='color:white;border-color:white'>Depoimentos</a>
                        </div>
                    </div>
                </div>
            </div>
             <div class="slide" style="background-image: linear-gradient(rgba(0, 87, 183, 0.8), rgba(0, 87, 183, 0.8)), url('img/slide3.webp');">
                <div class="container">
                    <div class="slide-content 3">
                        <h1>Ferramentas Educacionais</h1>
                        <p>Plataforma educacional digital que apoia escolas, professores e estudantes.</p>
                        <div class="slide-buttons">
                            <a href="#institucional"  id="btn1-slide-2" class="btn-primary" style='background-color:#FFA500;color:black'>Quero Conhecer</a>
                             <a href="#contato"  id="btn2-slide-2" class="btn-secondary botaoBanner" style='color:white;border-color:white'>Teste por 7 dias</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="slider-controls">
            <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
            <div class="slider-dots"></div>
            <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
        </div>
    </section>
    

 <!-- Destaques -->
<section class="highlights">
    <div class="container">
        <div class="highlight-item">
            <i class="fas fa-gamepad"></i>
            <h3>Jogos Pedagógicos</h3>
            <p>Ferramentas lúdicas para engajar e facilitar a aprendizagem</p>
        </div>
        <div class="highlight-item">
            <i class="fas fa-calendar-alt"></i>
            <h3>Planejador de Aulas</h3>
            <p>Software web para organizar e otimizar seu plano de ensino</p>
        </div>
        <div class="highlight-item">
            <i class="fas fa-edit"></i>
            <h3>Gerador de Avaliações</h3>
            <p>Crie provas e exercícios automaticamente</p>
        </div>
        <div class="highlight-item">
            <i class="fas fa-headset"></i>
            <h3>Suporte Completo</h3>
            <p>Atendimento por WhatsApp e telefone sempre que precisar</p>
        </div>
    </div>
</section>
<!-- Seção Institucional -->
<section class="institutional-section" id='institucional'>
    <div class="container">
        <div class="institutional-content" >
            <div class="institutional-text">
                <h2 class="section-title">Sobre o Portal Universo do Saber</h2>
                <p>O Portal Universo do Saber nasceu da necessidade de oferecer aos educadores ferramentas práticas e inovadoras que otimizem seu tempo e potencializem o processo de ensino-aprendizagem.</p>
                
                <div class="institutional-features">
                    <div class="feature-item">
                        <i class="fas fa-rocket"></i>
                        <h3>Missão</h3>
                        <p>Democratizar o acesso a ferramentas educacionais de qualidade, tornando o planejamento e avaliação mais eficientes para professores de todo o Brasil.</p>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-eye"></i>
                        <h3>Visão</h3>
                        <p>Ser referência nacional em soluções tecnológicas para educação, impactando positivamente a vida de milhares de educadores e alunos.</p>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-handshake"></i>
                        <h3>Valores</h3>
                        <p>Inovação, praticidade, qualidade pedagógica e suporte humanizado são os pilares que guiam nosso trabalho diário.</p>
                    </div>
                </div>
                
            </div>
            
        </div>
        
        <div class="institutional-image">
                <img src="img/institucional.jpg" alt="Equipe do Portal Universo do Saber">
                <div class="stats-overlay">
                    <div class="stat-item">
                        <span class="stat-number">+500 Professores</span>
                        <span class="stat-label">Capacidade de Atendimento</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">+1000 usuários</span>
                        <span class="stat-label">Capacidade de Usuários</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Suporte online</span>
                    </div>
                </div>
            </div>

    </div>
</section>

<!-- Ferramentas em Destaque -->
<section class="featured-courses" id='ferramentas'>
    <div class="container">
        <h2 class="section-title">Ferramentas em Destaque</h2>
        <div class="courses-grid">
            <!-- Card 1: Planejador de Aulas -->
            <div class="course-card">
                <div class="course-icon">
                    <i class="fas fa-calendar-alt fa-3x" style="color: #0057B7;"></i>
                </div>
                <div class="course-info">
                    <h3>Planejador de Aulas</h3>
                    <p>Planeje aulas com rapidez e eficiência, utilizando ferramentas automáticas que otimizam o processo e permitem que você foque na criatividade e no ensino.</p>
                    <a href="https://wa.me/554788868374
" class="btn-course">Saiba Mais</a>
                </div>
            </div>
            
            
            <div class="course-card">
                <div class="course-icon">
                    <i class="fas fa-clipboard-check fa-3x" style="color: #0057B7;"></i>                </div>
                <div class="course-info">
                    <h3>Gerador de Avaliações</h3>
                    <p>Planeje aulas com rapidez e eficiência, utilizando ferramentas automáticas que otimizam o processo e permitem que você foque na criatividade e no ensino.</p>
                    <a href="https://wa.me/554788868374
" class="btn-course">Saiba Mais</a>
                </div>
            </div>

           
        </div>
    </div>
</section>

<!-- Seção de Vídeo Manifesto (atualizada com lightbox) -->
<section class="video-manifesto">
    <div class="video-background">
        <div class="overlay"></div>
        <div class="container">
            <div class="video-content">
                <h2 class="section-title">Um Manifesto para a Educação</h2>
                <p class="video-description">Assista ao nosso manifesto e descubra como estamos transformando vidas através do conhecimento</p>
                <div class="video-wrapper">
                    <!-- Vídeo local com fallback e atributos para mobile -->
                    <video id="manifesto-video" width="100%" height="auto" playsinline controls autoplay muted loop>
                        <source src="img/video.mp4" type="video/mp4">
                        <source src="img/video.webm" type="video/webm"> <!-- Adicione um formato alternativo -->
                        Seu navegador não suporta o elemento de vídeo.
                    </video>
                </div>
                <a href="#!" class="btn-accent open-methodology">Conheça Nossa Metodologia</a>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox da Metodologia -->
<div class="methodology-lightbox" id='metodo'>
    <div class="lightbox-content">
        <button class="close-lightbox">&times;</button>
        <h2>Conheça Nossa Metodologia</h2>
        <div class="scrollable-content">
            <p>Na nossa plataforma, colocamos a tecnologia a serviço da educação, proporcionando um ambiente digital completo para professores e alunos. Com o foco na automação e na eficiência, nossa metodologia visa transformar o ensino e aprendizagem, liberando mais tempo para atividades estratégicas e pedagógicas.</p>
            
            <p>Os professores contam com ferramentas avançadas, como o gerador de avaliações, que permite criar provas e atividades de forma rápida e precisa, reduzindo erros e garantindo um processo mais confiável. Além disso, disponibilizamos um planejador de aulas prático e funcional, permitindo aos educadores organizar e estruturar suas aulas com facilidade.</p>
            
            <p>Para os alunos, oferecemos um espaço dinâmico onde podem realizar as avaliações diretamente na plataforma, promovendo uma experiência integrada e interativa. Também incluímos jogos educativos interativos, que estimulam o aprendizado de maneira lúdica e envolvente, tornando o processo de ensino mais atrativo.</p>
            
            <p>Ao automatizar tarefas e otimizar processos, nosso sistema reduz a carga operacional dos professores, criando mais oportunidades para que se concentrem em estratégias e abordagens educacionais que realmente impactam os estudantes. Nossa metodologia é uma ponte entre inovação tecnológica e prática pedagógica, garantindo qualidade, rapidez e eficácia no ensino.</p>
            
            <p class="closing">Explore o futuro da educação com nossa plataforma!</p>
        </div>
    </div>
</div>


<!-- Seção de Planos -->
<section class="pricing-section" id='planos'>
    <div class="container">
        <h2 class="section-title">Nossos Planos</h2>
        <p class="section-subtitle">Escolha o plano que melhor atende às suas necessidades educacionais</p>
        
        <div class="pricing-cards">
            <!-- Plano Mensal -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>Plano Mensal</h3>
                    <div class="price">
                        <span class="currency">R$</span>
                        <span class="amount">49,90</span>
                        <span class="period">/mês</span>
                    </div>
                </div>
                <ul class="features">
                    <li><i class="fas fa-check-circle"></i> Planejador de Aulas</li>
                    <li><i class="fas fa-check-circle"></i> Gerador de Avaliações</li>
                    <li><i class="fas fa-check-circle"></i> Recursos Pedagógicos Exclusivos</li>
                    <li><i class="fas fa-check-circle"></i> Acesso a Todas as Funcionalidades</li>
                    <li><i class="fas fa-check-circle"></i> Suporte técnico por WhatsApp</li>
                </ul>
                <a href="https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=2c93808496006df9019601ff226200cd" class="btn-primary">Assinar Plano</a>
                <div class="recommendation-badge">Mais Flexível</div>
            </div>
            
            <!-- Plano Semestral -->
            <div class="pricing-card featured">
                <div class="pricing-header">
                    <h3>Plano Semestral</h3>
                    <div class="price">
                        <span class="currency">R$</span>
                        <span class="amount">257,40</span>
                        <span class="period">/semestre</span>
                    </div>
                    <div class="savings">Economize 14%</div>
                </div>
                <ul class="features">
                    <li><i class="fas fa-check-circle"></i> Planejador de Aulas</li>
                    <li><i class="fas fa-check-circle"></i> Gerador de Avaliações</li>
                    <li><i class="fas fa-check-circle"></i> Recursos Pedagógicos Exclusivos</li>
                    <li><i class="fas fa-check-circle"></i> Acesso a Todas as Funcionalidades</li>
                    <li><i class="fas fa-check-circle"></i> Suporte técnico por WhatsApp</li>
                </ul>
                <a href="https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=2c93808496006dfa019602050f7500f3" class="btn-accent">Assinar Plano</a>
                <div class="recommendation-badge">Mais Popular</div>
            </div>
            
            <!-- Plano Anual -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>Plano Anual</h3>
                    <div class="price">
                        <span class="currency">R$</span>
                        <span class="amount">478,80</span>
                        <span class="period">/ano</span>
                    </div>
                    <div class="savings">Economize 20%</div>
                </div>
                <ul class="features">
                    <li><i class="fas fa-check-circle"></i> Planejador de Aulas</li>
                    <li><i class="fas fa-check-circle"></i> Gerador de Avaliações</li>
                    <li><i class="fas fa-check-circle"></i> Recursos Pedagógicos Exclusivos</li>
                    <li><i class="fas fa-check-circle"></i> Acesso a Todas as Funcionalidades</li>
                    <li><i class="fas fa-check-circle"></i> Suporte técnico por WhatsApp</li>
                </ul>
                <a href="https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=2c938084966c84bc01966de27d2e00cb" class="btn-primary">Assinar Plano</a>
                <div class="recommendation-badge">Melhor Custo-Benefício</div>
            </div>
        </div>
        
        <div class="pricing-footer">
            <p>Todos os planos incluem 7 dias de garantia. Cancele quando quiser.</p>
        </div>
    </div>
</section>

 
<!-- Notícias e Eventos -->

<section class="news-events" id='artigos-noticias'>
    <div class="container">
        <div class="news-section">
            <h2 class="section-title">Notícias</h2>
            <div class="news-list">
                <?php
                // Função para formatar datas
                function formatDate($dateStr, $source) {
                    $dateStr = trim($dateStr);
                    
                    // Tenta detectar o formato automaticamente
                    if (strpos($dateStr, '/') !== false) {
                        // Formato d/m/Y
                        $date = DateTime::createFromFormat('d/m/Y', $dateStr);
                        if ($date) return $date;
                    } 
                    
                    if (strpos($dateStr, 'h') !== false) {
                        // Se contém hora, assume que é hoje
                        return new DateTime();
                    }
                    
                    // Tenta outros formatos comuns
                    $formats = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'd.m.Y'];
                    foreach ($formats as $format) {
                        $date = DateTime::createFromFormat($format, $dateStr);
                        if ($date) return $date;
                    }
                    
                    // Se tudo falhar, retorna data atual
                    return new DateTime();
                }

                // Função para fazer requisição cURL
                function fetchUrl($url) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $html = curl_exec($ch);
                    curl_close($ch);
                    return $html;
                }

                // Função para buscar notícias
                function fetchNews($limit = 3) {
                    $news = [];
                    
                    // CNN Brasil (antigo Porvir no seu código)
                    $cnnUrl = "https://www.cnnbrasil.com.br/educacao/";
                    $cnnHtml = fetchUrl($cnnUrl);
                    
                    if ($cnnHtml !== false) {
                        $dom = new DOMDocument();
                        @$dom->loadHTML($cnnHtml);
                        $xpath = new DOMXPath($dom);
                        
                        $titles = $xpath->query("//h3[contains(@class, 'post-title')]/a");
                        $dates = $xpath->query("//div[contains(@class, 'post-date')]");
                        $links = $xpath->query("//h3[contains(@class, 'post-title')]/a/@href");
                        
                        for ($i = 0; $i < min($limit, $titles->length); $i++) {
                            $date = formatDate($dates->item($i)->nodeValue, 'CNN Brasil');
                            $news[] = [
                                'day' => $date->format('d'),
                                'month' => $date->format('M'),
                                'title' => trim($titles->item($i)->nodeValue),
                                'link' => $links->item($i)->nodeValue,
                                'source' => 'CNN Brasil',
                                'full_date' => $date->format('Y-m-d')
                            ];
                        }
                    }
                    
                    // G1 Educação
                    $g1Url = "https://g1.globo.com/educacao/";
                    $g1Html = fetchUrl($g1Url);
                    
                    if ($g1Html !== false) {
                        $dom = new DOMDocument();
                        @$dom->loadHTML($g1Html);
                        $xpath = new DOMXPath($dom);
                        
                        $titles = $xpath->query("//a[contains(@class, 'feed-post-link')]");
                        $dates = $xpath->query("//span[contains(@class, 'feed-post-datetime')]");
                        
                        for ($i = 0; $i < min($limit, $titles->length); $i++) {
                            $dateStr = $dates->item($i) ? $dates->item($i)->nodeValue : 'hoje';
                            $date = formatDate($dateStr, 'G1');
                            $news[] = [
                                'day' => $date->format('d'),
                                'month' => $date->format('M'),
                                'title' => trim($titles->item($i)->nodeValue),
                                'link' => $titles->item($i)->getAttribute('href'),
                                'source' => 'G1 Educação',
                                'full_date' => $date->format('Y-m-d')
                            ];
                        }
                    }
                    
                    // Gazeta do Povo Educação
                    $gazetaUrl = "https://www.gazetadopovo.com.br/educacao/";
                    $gazetaHtml = fetchUrl($gazetaUrl);
                    
                    if ($gazetaHtml !== false) {
                        $dom = new DOMDocument();
                        @$dom->loadHTML($gazetaHtml);
                        $xpath = new DOMXPath($dom);
                        
                        $titles = $xpath->query("//h3[contains(@class, 'c-title')]/a");
                        $dates = $xpath->query("//time[contains(@class, 'c-time')]");
                        $links = $xpath->query("//h3[contains(@class, 'c-title')]/a/@href");
                        
                        for ($i = 0; $i < min($limit, $titles->length); $i++) {
                            $dateStr = $dates->item($i) ? $dates->item($i)->getAttribute('datetime') : '';
                            $date = formatDate($dateStr, 'Gazeta do Povo');
                            $news[] = [
                                'day' => $date->format('d'),
                                'month' => $date->format('M'),
                                'title' => trim($titles->item($i)->nodeValue),
                                'link' => 'https://www.gazetadopovo.com.br' . $links->item($i)->nodeValue,
                                'source' => 'Gazeta do Povo',
                                'full_date' => $date->format('Y-m-d')
                            ];
                        }
                    }
                    
                    // Ordenar por data mais recente
                    usort($news, function($a, $b) {
                        return strtotime($b['full_date']) - strtotime($a['full_date']);
                    });
                    
                    return array_slice($news, 0, $limit);
                }
                
                // Buscar notícias
                $displayNews = fetchNews(3);
                
                // Exibir as notícias
                foreach ($displayNews as $newsItem) {
                    echo '<div class="news-item">';
                    echo '    <div class="news-date">';
                    echo '        <span class="day">' . htmlspecialchars($newsItem['day']) . '</span>';
                    echo '        <span class="month">' . htmlspecialchars($newsItem['month']) . '</span>';
                    echo '    </div>';
                    echo '    <div class="news-content">';
                    echo '        <h3><a href="' . htmlspecialchars($newsItem['link']) . '" target="_blank">' . htmlspecialchars($newsItem['title']) . '</a></h3>';
                    echo '        <p>Fonte: ' . htmlspecialchars($newsItem['source']) . '</p>';
                    echo '    </div>';
                    echo '</div>';
                }
                ?>
            </div>

            <div class="btn-container">
                <a href="noticias-detalhes.php" class="btn-secondary">Ver Mais Notícias</a>
            </div>
        </div>

        <div class="events-section">
            <!-- Seção de Artigos -->
            <h2 class="section-title">Artigos</h2>
            <div class="events-list">
                <?php
                try {
                    // Consulta os artigos no banco de dados usando PDO
                    $query = "SELECT id, nome, texto, imagem, fonte, data FROM artigo ORDER BY data DESC LIMIT 3";
                    $stmt = $conexao->prepare($query);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        while ($artigo = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            // Formata a data
                            $data = date('d M', strtotime($artigo['data']));
                            $partesData = explode(' ', $data);
                            
                            echo '<div class="event-item">';
                            echo '    <div class="event-date">';
                            echo '        <span class="day">' . htmlspecialchars($partesData[0]) . '</span>';
                            echo '        <span class="month">' . htmlspecialchars($partesData[1]) . '</span>';
                            echo '    </div>';
                            echo '    <div class="event-content">';
                            echo '        <h3><a href="artigo-detalhes.php?id=' . $artigo['id'] . '">' . htmlspecialchars($artigo['nome']) . '</a></h3>';
                            
                            // Remove tags HTML e limita o texto
                            $textoResumo = strip_tags($artigo['texto']);
                            $textoResumo = substr($textoResumo, 0, 100);
                            if (strlen($artigo['texto']) > 100) {
                                $textoResumo .= '...';
                            }
                            
                            echo '        <p>' . htmlspecialchars($textoResumo) . '</p>';
                            if (!empty($artigo['fonte'])) {
                                // Limita o texto da fonte
                                $fonteResumo = substr($artigo['fonte'], 0, 30);
                                if (strlen($artigo['fonte']) > 30) {
                                    $fonteResumo .= '...';
                                }
                                echo '        <span class="event-time"><i class="fas fa-book"></i> ' . htmlspecialchars($fonteResumo) . '</span>';
                            }
                            echo '    </div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="event-item">';
                        echo '    <div class="event-content">';
                        echo '        <p>Nenhum artigo encontrado.</p>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="event-item">';
                    echo '    <div class="event-content">';
                    echo '        <p>Erro ao carregar artigos.</p>';
                    echo '    </div>';
                    echo '</div>';
                    // Em ambiente de desenvolvimento, você pode descomentar a linha abaixo para ver o erro
                    // echo '<p>Erro: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="artigos.php" class="btn-secondary">Ver Todos os Artigos</a>
            </div>
        </div>
    </div>
</section>

    <!-- Depoimentos -->
    <section class="testimonials" id='depoimentos'>
        <div class="container">
            <h2 class="section-title">O que dizem sobre nós</h2>
            <div class="testimonials-slider">
                <div class="testimonial-item active">
                    <div class="testimonial-content">
                        <p>Ferramenta intuitiva, rápida e que atende às necessidades dos educadores e educandos.</p>
                        <div class="testimonial-author">
                            <div>
                                <h4>Hélio Sander</h4>
                                <span>Programador - abr-2025</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-item">
                    <div class="testimonial-content">
                        <p>Estava faltando uma ferramenta desta no mercado. Ajudará muitos os professores e alunos.</p>
                        <div class="testimonial-author">
                            <div>
                                <h4>Wilma Maria</h4>
                                <span>Aposentada - abr-2025</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-item">
                    <div class="testimonial-content">
                        <p>O apoio e cuidado do Everton é sensacional. Parabéns pelo profissionalismo e comprometimento</p>
                        <div class="testimonial-author">
                            <div>
                                <h4>Fabiana Alves</h4>
                                <span>Terapeuta - abr-2025</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonials-controls">
                <button class="prev-testimonial"><i class="fas fa-chevron-left"></i></button>
                <div class="testimonials-dots"></div>
                <button class="next-testimonial"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <?php
	
	/*
// newsletter.php (crie este arquivo para processar o formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        // Configurações do e-mail
        $to = 'contato@portaluniversodosaber.com.br';
        $subject = 'Confirmação de inscrição na newsletter';
        $message = "
        <html>
        <head>
            <title>Confirmação de inscrição</title>
            <meta charset='UTF-8'>
        </head>
        <body>
            <h2 style='color: #0057B7;'>E-mail cadastrado!</h2>
            <p>Você receberá nossas novidades, cursos e oportunidades em primeira mão.</p>
            <p>E-mail cadastrado: $email</p><br><br>
            <p><strong>Equipe Portal Universo do Saber</strong></p>
        </body>
        </html>
        ";
        
        // Headers para evitar spam e suportar UTF-8
        $headers = "From: Portal Universo do Saber <contato@portaluniversodosaber.com.br>\r\n";
        $headers .= "Reply-To: contato@portaluniversodosaber.com.br\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Envia o e-mail
        $mailSent = mail($to, $subject, $message, $headers);
        
        // Redireciona com mensagem de sucesso/erro
        header('Location: index.php?newsletter='.($mailSent ? 'success' : 'error'));
        exit();
    } else {
        header('Location: index.php?newsletter=invalid');
        exit();
    }
}*/
?>

<!-- No seu index.php, substitua a seção de newsletter por: -->
<!--
<section class="newsletter">
    <div class="container">
        <div class="newsletter-content">
            <h2>Receba nossas novidades</h2>
            <p>Cadastre-se e receba informações sobre cursos, eventos e oportunidades.</p>
            
            </*?php if (isset($_GET['newsletter'])): ?>
                <div class="newsletter-message">
                    ?php /**<switch($_GET['newsletter']) {
                        case 'success':
                            echo '<p class="success">Inscrição realizada com sucesso! Verifique seu e-mail.</p>';
                            break;
                        case 'error':
                            echo '<p class="error">Ocorreu um erro. Tente novamente mais tarde.</p>';
                            break;
                        case 'invalid':
                            echo '<p class="error">Por favor, insira um e-mail válido.</p>';
                            break;
                    }?**/>
                </div>
           <//?php endif; ?>
        </div>
        <form class="newsletter-form" method="POST" action="newsletter.php">
            <input type="email" name="email" placeholder="Seu melhor e-mail" required>
            <button type="submit" class="btn-primary">Assinar</button>
        </form>
    </div>
</section>
    -->
    
<!-- Seção de Suporte -->
<section class="support-section" id='contato'>
    <div class="container">
        <h2 class="section-title">Precisa de Ajuda?</h2>
        <p class="section-subtitle">Entre em contato com nossa equipe de suporte</p>
        
        <?php if (isset($_GET['contato'])): ?>
            <div class="contato-message">
                <?php switch($_GET['contato']) {
                    case 'success':
                        echo '<p class="success">Mensagem enviada com sucesso! Entraremos em contato em breve.</p>';
                        break;
                    case 'error':
                        echo '<p class="error">Ocorreu um erro ao enviar sua mensagem. Tente novamente mais tarde.</p>';
                        break;
                    case 'invalid':
                        echo '<p class="error">Por favor, preencha todos os campos corretamente.</p>';
                        break;
                } ?>
            </div>
        <?php endif; ?>
        
        <form class="support-form" method="POST" action="processar-contato.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome Completo</label>
                    <input type="text" id="name" name="name" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Você é:</label>
                    <select id="type" name="type" required>
                        <option value="" disabled selected>Selecione uma opção</option>
                        <option value="aluno">Aluno</option>
                        <option value="professor">Professor</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Telefone</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="message">Mensagem</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
            </div>
			
			<?php if (isset($_GET['contato']) && $_GET['contato'] === 'recaptcha'): ?>
			  <div class="contato-message">
				<p class="error">Por favor, marque o reCAPTCHA antes de enviar.</p>
			  </div>
			<?php endif; ?>
            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaCheckboxKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
				<script src="https://www.google.com/recaptcha/api.js" async defer></script>

			  <div class="form-submit">
                <button type="submit" class="btn-accent">Enviar Mensagem</button>
            </div>
			
			

        </form>
    </div>
</section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3>Institucional</h3>
                    <ul>
                        <li><a href="#institucional">Sobre Nós</a></li>
                        <li><a href="#ferramentas">Ferramentas</a></li>
                        <li><a href="#planos">Planos</a></li>
                        <li><a href="#contato">Contato</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Acesse grátis</h3>
                    <ul>
                        <li><a href="videos.php" target='_blank'>Vídeos</a></li>
                        <li><a href="#">Artigos</a></li>
                        <li><a href="#">Notícias</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Links Úteis</h3>
                    <ul>
                        <li><a href="https://www.agendedu.com.br/site/" target='_blank'>agendEDU</a></li>
                        <li><a href="http://download.basenacionalcomum.mec.gov.br/" target='_blank'>Download BNCC</a></li>
                        <li><a href="https://www.gov.br/mec/pt-br" target='_blank'>MEC</a></li>
                        <!--<li><a href="#">Perguntas Frequentes</a></li>-->
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contato</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Blumenau - SC</li>
                        <li><i class="fas fa-phone"></i> (47) 9-8886-8374</li>
                        <li><i class="fas fa-envelope"></i> contato@<br>portaluniversodosaber.com.br</li>
                    </ul>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/profile.php?id=61575735779692&locale=pt_BR" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/portaluniversodosaberoficial/" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/everton-leite-54b997335?originalSubdomain=br" target='_blank'><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="copyright">
                    &copy; 2025 Portal Universo do Saber. Todos os direitos reservados.
                </div>
                <div class="footer-links">
                    <a href="/politica-privacidade.html">Política de Privacidade</a>
                    <a href="/termos-uso.html">Termos de Uso</a>
                </div>
            </div>
        </div>
    </footer>
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($recaptchaV3Key, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
grecaptcha.ready(function() {
    grecaptcha.execute('<?php echo addslashes($recaptchaV3Key); ?>', {action: 'contato'}).then(function(token) {
        var recaptchaResponse = document.createElement('input');
        recaptchaResponse.type = 'hidden';
        recaptchaResponse.name = 'g-recaptcha-response';
        recaptchaResponse.value = token;
        document.forms[0].appendChild(recaptchaResponse); // ou selecione seu form por ID
    });
});
</script>
    <script src="js/script.js"></script>
</body>
</html>