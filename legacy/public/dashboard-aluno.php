<?php
require 'conexao.php';
require 'sessao-aluno.php';

$query_questoes = "SELECT COUNT(*) as total FROM questoes";
$stmt_questoes = $conexao->prepare($query_questoes);
$stmt_questoes->execute();
$total_questoes = $stmt_questoes->fetch(PDO::FETCH_ASSOC)['total'];

$query_alunos = "SELECT COUNT(*) as total FROM login
                 WHERE escola = :escola AND login = :login AND perfil = :perfil";
$stmt_alunos = $conexao->prepare($query_alunos);
$stmt_alunos->bindValue(':escola', $_SESSION['escola']);
$stmt_alunos->bindValue(':login', $_SESSION['login']);
$stmt_alunos->bindValue(':perfil', 'Aluno');
$stmt_alunos->execute();
$total_alunos = $stmt_alunos->fetch(PDO::FETCH_ASSOC)['total'];

$query_turmas = "SELECT COUNT(*) as total FROM turmas
                 WHERE escola = :escola AND login = :login";
$stmt_turmas = $conexao->prepare($query_turmas);
$stmt_turmas->bindValue(':escola', $_SESSION['escola']);
$stmt_turmas->bindValue(':login', $_SESSION['login']);
$stmt_turmas->execute();
$total_turmas = $stmt_turmas->fetch(PDO::FETCH_ASSOC)['total'];

$query_provas = "
SELECT
    po.id AS id_prova_online,
    po.data,
    po.materia,
    p.tentativa_max,
    p.tentativa_feita
FROM provas_online po
INNER JOIN provas p
      ON po.turma = p.turma
      AND po.materia = p.materia
      AND p.matricula = :matricula
GROUP BY po.id, po.data, po.materia, p.tentativa_max, p.tentativa_feita
ORDER BY po.data DESC
";
$stmt_provas = $conexao->prepare($query_provas);
$stmt_provas->bindValue(':matricula', $_SESSION['matricula']);
$stmt_provas->execute();
$provasAluno = $stmt_provas->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = 'Dashboard do Aluno - Universo do Saber';
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
                    <h1>Bem-vindo ao seu painel</h1>
                    <p>Acompanhe suas atividades, tentativas e desempenho no Universo do Saber.</p>
                </div>

                <div class="card-container">
                    <div class="card">
                        <div class="card-icon blue">
                            <i class="fa fa-layer-group" aria-hidden="true"></i>
                        </div>
                        <div class="card-content">
                            <h3><?= number_format($total_turmas, 0, ',', '.'); ?></h3>
                            <p>Turmas relacionadas</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon orange">
                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                        </div>
                        <div class="card-content">
                            <h3><?= number_format($total_questoes, 0, ',', '.'); ?></h3>
                            <p>Questões disponíveis</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon green">
                            <i class="fa fa-users" aria-hidden="true"></i>
                        </div>
                        <div class="card-content">
                            <h3><?= number_format($total_alunos, 0, ',', '.'); ?></h3>
                            <p>Colegas na sua escola</p>
                        </div>
                    </div>
                </div>

                <section class="content-container">
                    <div class="page-header">
                        <div>
                            <h2>Suas avaliações</h2>
                            <p>Confira as próximas avaliações e o status das tentativas.</p>
                        </div>
                    </div>

                    <?php if (!empty($provasAluno)): ?>
                        <div class="sub-container table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Data</th>
                                        <th scope="col">Matéria</th>
                                        <th scope="col">Tentativas</th>
                                        <th scope="col" class="text-end">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($provasAluno as $prova): ?>
                                        <?php
                                            $tentativasFeitas = (int) ($prova['tentativa_feita'] ?? 0);
                                            $tentativasMax = (int) ($prova['tentativa_max'] ?? 0);
                                            $restantes = max($tentativasMax - $tentativasFeitas, 0);
                                        ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($prova['data'])); ?></td>
                                            <td><?= htmlspecialchars($prova['materia'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $tentativasFeitas; ?> / <?= $tentativasMax; ?></td>
                                            <td class="text-end">
                                                <?php if ($restantes > 0): ?>
                                                    <a class="btn btn-primary btn-sm"
                                                       href="prova-online.php?id=<?= (int) $prova['id_prova_online']; ?>">
                                                        Continuar
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Concluída</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhuma avaliação disponível no momento.</div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/foot.php'; ?>
