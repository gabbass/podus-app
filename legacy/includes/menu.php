<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$perfil = $_SESSION['perfil'] ?? '';

$menus = [
    'Professor' => [
        ['Início', 'dashboard-professor.php', 'fa-home'],
        ['Minhas turmas', 'pesquisar-turmas.php', 'fa-people-line'],
		['Meus alunos', 'pesquisar-alunos.php', 'fa-users'],
		['Material pedagógico', 'material-pedagogico.php', 'fa-book'],
		['Planejamento de aulas', 'planejador-mensal.php', 'fas fa-calendar-week'],
        //['Provas', 'cadastrar-provas.php', 'fa-file-alt'],
		['Banco de questões', 'questoes.php', 'fa-question-circle'],
		['Provas', 'provas.php', 'fa-file-alt'],
		['Notas', 'notas-ap.php', 'fa-calculator'],
		//['Questões', 'questoes-professor.php', 'fa-question-circle'],
		//['Notas', 'notas-alunos.php', 'fa-calculator'],
		['Jogos pedagógicos', 'jogos-pedagocicos.php', 'fas fa-gamepad']
    ],
    'Administrador' => [
        ['Início', 'dashboard.php', 'fa-home'],
        ['Usuários', 'usuarios.php', 'fa-user-cog'],
        ['Turmas', 'cadastrar-turmas.php', 'fa-people-line'],
		['Planejamento de aulas', 'planejador-mensal.php', 'fas fa-calendar-week'],
		['Banco de questões', 'questoes.php', 'fa-question-circle'],
      //  ['Questões', 'questoes-professor.php', 'fa-question-circle'],
        //['Provas', 'provas-admin.php', 'fa-file-alt'],
		['Provas', 'provas.php', 'fa-file-alt'],
		['Notas', 'notas-ap.php', 'fa-calculator'],
        ['Relatórios', 'relatorios.php', 'fa-chart-bar']
    ],
    'Aluno' => [
        ['Início', 'dashboard-aluno.php', 'fa-home'],
        ['Material de apoio', 'material-apoio.php', 'fa-book'],
		['Minhas Notas', 'nota-provas.php', 'fa-calculator']
    ]
];
?>
<aside class="sidebar active" id="sidebar">
    <div class="sidebar-header">
        <h3>Universo Saber</h3>
        <button class="close-sidebar" id="close-sidebar" onclick="document.querySelector('.sidebar').classList.remove('active')">&times;</button>
    </div>
   <ul class="sidebar-menu">
    <?php
    if (isset($menus[$perfil])) {
        foreach ($menus[$perfil] as [$label, $file, $icon]) {
            $active = $currentPage === $file ? 'active' : '';
            echo <<<HTML
            <li>
                <a href="$file" class="$active"><i class="fa $icon"></i> <span>$label</span></a>
            </li>
            HTML;
        }
    }
    ?>
</ul>

</aside>

<script src="js/pusaber.js"></script>
