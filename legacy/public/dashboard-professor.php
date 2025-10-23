<?php
require 'sessao-professor.php';
require 'dashboard_stats.php';
$tituloPagina = 'Dashboard - Universo do Saber';
include __DIR__ . '/../includes/head.php';
?>
<body class="legacy-app" data-menu-breakpoints='[{"name":"mobile","width":0,"mode":"overlay"},{"name":"desktop","width":1024,"mode":"inline"}]'>
        <?php include __DIR__ . '/../includes/menu.php'; ?>
        <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>
        <main class="layout-main" id="main-content">
                <?php include __DIR__ . '/../includes/cabecalho.php'; ?>
                <div class="layout-main__content" id="content-container">
                        <div class="container">
                                <div class="page-header">
                                        <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Bem-vindo ao Portal Universo do Saber</p>
                                        </div>
                                </div>

                                <div class="card-container">
                                        <div class="card">
                                                <div class="card-header" onclick="location.assign('questoes-professor')" >
                                                        <div class="card-title">Questões</div>
                                                        <div class="card-icon blue"><i class="fas fa-question-circle" aria-hidden="true"></i></div>
                                                </div>
                                                <div class="card-body">
                                                        <h2><?php echo $total_questoes; ?></h2>
                                                        <p>Total de questões cadastradas</p>
                                                </div>

                                        </div>

                                        <div class="card">
                                                <div class="card-header" onclick="location.assign('pesquisar-alunos')">
                                                        <div class="card-title">Alunos</div>
                                                        <div class="card-icon orange"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
                                                </div>
                                                <div class="card-body">
                                                        <h2><?php echo $total_alunos; ?></h2>
                                                        <p>Alunos cadastrados</p>
                                                </div>
                                        </div>

                                        <div class="card">
                                                <div class="card-header" onclick="location.assign('pesquisar-turmas')">
                                                        <div class="card-title">Turmas</div>
                                                        <div class="card-icon green"><i class="fas fa-users" aria-hidden="true"></i></div>
                                                </div>
                                                <div class="card-body">
                                                        <h2><?php echo $total_turmas; ?></h2>
                                                        <p>Turmas ativas</p>
                                                </div>
                                        </div>

                                        <div class="card">
                                                <div class="card-header" onclick="location.assign('questoes')">
                                                        <div class="card-title">Avaliações</div>
                                                        <div class="card-icon blue"><i class="fas fa-file-alt" aria-hidden="true"></i></div>
                                                </div>
                                                <div class="card-body">
                                                        <h2><?php echo $total_provas; ?></h2>
                                                        <p>Avaliações geradas</p>
                                                </div>
                                        </div>
                                </div>

                                <div class="chart-section">
                                        <div class="chart-card">
                                                <h2>Questões cadastradas por dificuldade</h2>
                                                <canvas id="chart_difficulty"></canvas>
                                        </div>
                                        <div class="chart-card">
                                                <h2>Questões cadastradas por disciplina</h2>
                                                <canvas id="chart_discipline"></canvas>
                                        </div>
                                </div>

                        </div>
                </div>
        </main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js"></script>
<script src="js/dashboard.js"></script>
        <?php include __DIR__ . '/../includes/foot.php'; ?>
