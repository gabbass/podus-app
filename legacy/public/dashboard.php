<?php
require 'sessao-adm.php';
require 'conexao.php';

try {
    $stmt_professores = $conexao->prepare("SELECT COUNT(*) AS total FROM login WHERE perfil = 'Professor'");
    $stmt_professores->execute();
    $professores = $stmt_professores->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt_questoes = $conexao->prepare("SELECT COUNT(*) AS total FROM questoes");
    $stmt_questoes->execute();
    $questoes = $stmt_questoes->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt_artigos = $conexao->prepare("SELECT COUNT(*) AS total FROM artigo");
    $stmt_artigos->execute();
    $artigos = $stmt_artigos->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo "Erro ao buscar dados: " . $e->getMessage();
    exit;
}

$tituloPagina = 'Gerador de Avaliações - Universo do Saber';
include __DIR__ . '/../includes/head.php';
?>
<body class="legacy-app" data-menu-breakpoints='[{"name":"mobile","width":0,"mode":"overlay"},{"name":"desktop","width":1024,"mode":"inline"}]'>
    <?php include __DIR__ . '/../includes/menu.php'; ?>
    <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>
    <main id="main-content" class="layout-main">
        <?php include __DIR__ . '/../includes/cabecalho.php'; ?>
        <div class="layout-main__content">
            <div class="container">
                <div class="page-title">
                    <h1>Gerador de Avaliações</h1>
                    <p>Visão geral das principais métricas do portal.</p>
                </div>

                <div class="card-container">
                    <div class="card">
                        <div class="card-icon blue">
                            <i class="fa fa-chalkboard-teacher" aria-hidden="true"></i>
                        </div>
                        <div class="card-content">
                            <h3><?= number_format($professores, 0, ',', '.'); ?></h3>
                            <p>Professores cadastrados</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon orange">
                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                        </div>
                        <div class="card-content">
                            <h3><?= number_format($questoes, 0, ',', '.'); ?></h3>
                            <p>Questões disponíveis</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon green">
                            <i class="fa fa-newspaper" aria-hidden="true"></i>
                        </div>
                        <div class="card-content">
                            <h3><?= number_format($artigos, 0, ',', '.'); ?></h3>
                            <p>Artigos publicados</p>
                        </div>
                    </div>
                </div>

                <section class="content-container">
                    <h2>Bem-vindo ao painel administrativo</h2>
                    <p>Utilize o menu lateral para navegar entre os módulos disponíveis e gerenciar os recursos do Universo do Saber.</p>
                </section>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/foot.php'; ?>
