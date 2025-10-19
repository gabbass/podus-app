<?php
/******************************************************************
 *  notas-alunos.php  –  Bloco PHP completo                       *
 *  (carrega filtros, consulta turmas, busca lista de alunos)     *
 ******************************************************************/
require 'sessao-professor.php';
require 'conexao.php';

/* ===============================================================
 *  1. Filtros recebidos
 * =============================================================*/
$login_prof    = $_SESSION['login']      ?? '';
$filtrar_turma = $_GET['turma']          ?? '';
$buscar_nome   = trim($_GET['nome'] ?? '');

/* ===============================================================
 *  2. Carregamento dos dados em bloco try/catch
 * =============================================================*/
try {

    /* -----------------------------------------------------------
     * 2.1  Turmas cadastradas para este professor
     * ----------------------------------------------------------*/
    $stmtTurmas = $conexao->prepare(
        'SELECT * FROM turmas WHERE login = ? ORDER BY nome'
    );
    $stmtTurmas->execute([$login_prof]);
    $turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

    /* -----------------------------------------------------------
     * 2.2  WHERE dinâmico seguro (place-holders e array $params)
     * ----------------------------------------------------------*/
    $whereParts = ["l.perfil = 'Aluno'"];   // condição fixa
    $params     = [];                       // valores a fazer bind

    /* – Filtrar turma (ou todas as turmas do professor) --------*/
    if ($filtrar_turma !== '' && $filtrar_turma !== 'todas') {
        $whereParts[] = 'l.turma = ?';
        $params[]     = $filtrar_turma;
    } else {
        $whereParts[] = 'l.turma IN (SELECT nome FROM turmas WHERE login = ?)';
        $params[]     = $login_prof;
    }

    /* – Filtrar por nome (opcional) ----------------------------*/
    if ($buscar_nome !== '') {
        $whereParts[] = 'l.nome LIKE ?';
        $params[]     = '%' . $buscar_nome . '%';
    }

    /* – Monta string final do WHERE ----------------------------*/
    $whereSQL = implode(' AND ', $whereParts);   // sempre terá ao menos 1 item

    /* -----------------------------------------------------------
     * 2.3  Consulta principal
     *
     *      • LEFT JOIN provas  -> para pegar notas registradas
     *      • LEFT JOIN (subquery) provas_online mais recente
     *        por (turma, matéria)  -> devolve apenas 1 id/data
     * ----------------------------------------------------------*/
    $sql = "
        SELECT
            l.matricula,
            l.nome,
            l.turma,
            p.materia,
            p.nota,
            p.id            AS id_prova,
            po.id           AS id_provas_online,
            po.data         AS data_prova
        FROM login l
        LEFT JOIN provas p
               ON p.matricula = l.matricula
        LEFT JOIN (
            /* sub-consulta: última prova_online de cada (turma, matéria) */
            SELECT po_inner.*
            FROM   provas_online po_inner
            JOIN (
                SELECT turma, materia, MAX(data) AS max_data
                FROM   provas_online
                GROUP BY turma, materia
            ) po_max
              ON  po_inner.turma   = po_max.turma
              AND po_inner.materia = po_max.materia
              AND po_inner.data    = po_max.max_data
        ) po
               ON po.turma   = p.turma
              AND po.materia = p.materia
        WHERE $whereSQL
        ORDER BY l.turma, l.nome
    ";

    $stmtAlunos = $conexao->prepare($sql);
    $stmtAlunos->execute($params);
    $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    /* Falha no acesso ao BD – interrompe carregamento */
    die('Erro ao listar notas: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Notas dos Alunos - Professor</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- ===========================================================
     Estilos (mantidos integralmente)
=========================================================== -->
<style>
:root{
    --primary-blue:#0057b7;
    --primary-orange:#ffa500;
    --dark-blue:#003d7a;
    --light-gray:#f5f7fa;
    --medium-gray:#e1e5eb;
    --dark-gray:#6c757d;
}

/* Reset básico */
*{margin:0;padding:0;box-sizing:border-box}
body,
.container,
table,
input,
select,
button,
.table-notas,
.popup-content{
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}

/* Corpo -----------------------------------------------------*/
body{
    background:var(--light-gray);
    margin:0;
}

/* Container principal --------------------------------------*/
.container{
    background:#ffffff;
    border-radius:8px;
    box-shadow:0 4px 6px rgba(0,0,0,0.06);
    max-width:1050px;
    margin:0 auto;
    padding:20px 28px 28px 28px;
}

/* Cabeçalhos -----------------------------------------------*/
h1{
    color:var(--dark-blue);
    margin-bottom:23px;
}

/* Formulário de filtro -------------------------------------*/
.filtro-form{
    display:flex;
    gap:14px;
    margin-bottom:24px;
    flex-wrap:wrap;
}
.filtro-form input,
.filtro-form select{
    font-size:1em;
    padding:7px 10px;
    border-radius:4px;
    border:1px solid #cccccc;
}
.filtro-form button{
    padding:7px 17px;
    background:var(--primary-blue);
    color:#ffffff;
    border:none;
    border-radius:4px;
    font-weight:bold;
    cursor:pointer;
}

/* Tabela ----------------------------------------------------*/
.table-notas{
    width:100%;
    margin-top:22px;
    background:#ffffff;
    border-radius:7px;
    box-shadow:0 1px 10px #f3f3f3;
    overflow-x:auto;
}
.table-notas th,
.table-notas td{
    padding:7px 4px;
    border-bottom:1px solid #eeeeee;
    text-align:left;
}
.table-notas th{
    font-weight:600;
    color:var(--dark-blue);
}
.table-notas tr:last-child td{
    border-bottom:0;
}
.nome-link{
    color:var(--primary-blue);
    text-decoration:underline;
    font-weight:500;
    cursor:pointer;
}
.nota{
    font-weight:bold;
    color:var(--primary-orange);
}

/* Popup -----------------------------------------------------*/
.popup-bg{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.45);
    align-items:center;
    justify-content:center;
    z-index:900;
}
.popup-content{
    background:#ffffff;
    border-radius:8px;
    max-width:480px;
    width:98%;
    padding:30px 24px 16px 24px;
    position:relative;
    min-height:100px;
    box-shadow:0 15px 64px #1114;
}
.popup-content h2{
    color:var(--primary-blue);
    font-size:1.19em;
    margin-bottom:14px;
}
.popup-content .fechar{
    position:absolute;
    top:13px;
    right:20px;
    font-size:1.45em;
    cursor:pointer;
    color:var(--dark-gray);
}
.resp-correta{color:#07781e}
.resp-errada{color:#e91c44}
.quest-detalhe{margin-bottom:21px}
.quest-enun{font-weight:500}

/* Sidebar (mantido) ----------------------------------------*/
.sidebar{
    width:250px;
    background:#ffffff;
    box-shadow:2px 0 10px rgba(0,0,0,0.1);
    transition:all 0.3s;
    position:fixed;
    height:100vh;
    z-index:100;
}
.sidebar-header{
    padding:20px;
    background:linear-gradient(to right,var(--primary-blue),var(--dark-blue));
    color:#ffffff;
    display:flex;
    align-items:center;
    justify-content:center;
}
.sidebar-header h3{
    font-size:1.3rem;
    font-weight:600;
    margin-left:10px;
}
.sidebar-menu{
    padding:20px 0;
}
.sidebar-menu li{
    list-style:none;
    margin-bottom:5px;
}
.sidebar-menu a{
    display:flex;
    align-items:center;
    padding:12px 20px;
    color:var(--dark-gray);
    text-decoration:none;
    transition:all 0.3s;
}
.sidebar-menu a:hover,
.sidebar-menu a.active{
    background-color:rgba(0,87,183,0.1);
    color:var(--primary-blue);
    border-left:4px solid var(--primary-blue);
}
.sidebar-menu a i{
    margin-right:10px;
    font-size:1.1rem;
    width:20px;
    text-align:center;
}

/* Main-content / top-nav (mantidos) -------------------------*/
.main-content{
    flex:1;
    margin-left:250px;
    transition:all 0.3s;
}
.top-nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
    background:#ffffff;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    position:sticky;
    top:0;
    z-index:10;
}

/* Botões ----------------------------------------------------*/
.btn{
    padding:10px 20px;
    border-radius:4px;
    border:none;
    font-weight:500;
    cursor:pointer;
    transition:all 0.3s;
    display:inline-flex;
    align-items:center;
    text-decoration:none;
}
.btn i{margin-right:8px}
.btn-primary{
    background-color:var(--primary-blue);
    color:#ffffff;
}
.btn-primary:hover{
    background-color:var(--dark-blue);
}
.btn-secondary{
    background-color:var(--medium-gray);
    color:var(--dark-gray);
}
.btn-secondary:hover{
    background-color:#d1d5db;
}

/* Responsividade básica ------------------------------------*/
@media (max-width:700px){
    .container{padding:5px}
    .table-notas th,
    .table-notas td{font-size:0.98em}
}
@media (max-width:1200px){
    .sidebar{
        transform:translateX(-100%);
        position:fixed;
        top:0;
        left:0;
        height:100%;
        z-index:1000;
    }
    .sidebar.active{transform:translateX(0)}
    .main-content{margin-left:0}
}
</style>
</head>
<body>

<!-- ===========================================================
     SIDEBAR
=========================================================== -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-graduation-cap"></i>
        <h3>Universo do Saber</h3>
    </div>

    <ul class="sidebar-menu">
        <li><a href="#"><i class="fas fa-home"></i><span>Início</span></a></li>
        <li><a href="meu-cadastro-professor"><i class="fas fa-chalkboard-teacher"></i><span>Meu Cadastro</span></a></li>
        <li><a href="questoes-professor"><i class="fas fa-question-circle"></i><span>Questões</span></a></li>
        <li><a href="cadastrar-alunos"><i class="fas fa-user-graduate"></i><span>Alunos</span></a></li>
        <li><a href="cadastrar-turmas"><i class="fas fa-users"></i><span>Turmas</span></a></li>
        <li><a href="jogos-pedagocicos"><i class="fas fa-gamepad"></i><span>Jogos Pedagógicos</span></a></li>
        <li><a href="planejador"><i class="fas fa-calendar-alt"></i><span>Planejador Anual</span></a></li>
        <li><a href="planejador-mensal"><i class="fas fa-calendar-alt"></i><span>Planejador Mensal</span></a></li>
        <li><a href="material-pedagogico"><i class="fas fa-book"></i><span>Material Pedagógico</span></a></li>
        <li><a href="cadastrar-provas.php"><i class="fas fa-book"></i><span>Provas</span></a></li>
        <li><a href="notas-alunos.php" class="active"><i class="fas fa-book"></i><span>Notas</span></a></li>
        <li><a href="sair.php"><i class="fas fa-sign-out-alt"></i><span>Sair</span></a></li>
    </ul>
</div>

<!-- ===========================================================
     MAIN CONTENT
=========================================================== -->
<div class="main-content">

    <!-- Top Navigation (apenas placeholder, sem itens visíveis) -->
    <div class="top-nav"></div>

    <!-- Bloco interno -------------------------------------------------->
    <div class="container">

        <!-- Cabeçalho da página -------------------------------------->
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
            <h1>Notas dos alunos</h1>
            <a href="dashboard-professor.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- FORMULÁRIO DE FILTRO ---------------------------------->
        <form class="filtro-form" method="get">
            <label>
                Turma:
                <select name="turma">
                    <option value="todas">Todas</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?= htmlspecialchars($t['nome']) ?>"
                                <?= $filtrar_turma === $t['nome'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Buscar aluno:
                <input type="text"
                       name="nome"
                       value="<?= htmlspecialchars($buscar_nome) ?>"
                       placeholder="Nome do aluno">
            </label>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filtrar
            </button>
        </form>

        <!-- TABELA --------------------------------------------------->
        <table class="table-notas">
            <thead>
                <tr>
                    <th>Turma</th>
                    <th>Nome</th>
                    <th>Matéria</th>
                    <th>Nota</th>
                    <th>Data Prova</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $temLinha = false;
            foreach ($alunos as $aluno):

                /* Caso não exista nome, pula a linha                */
                if (!$aluno['nome']) {
                    continue;
                }
                $temLinha = true;

                /* Apenas id_provas_online realmente numérico -------*/
                $idOnline   = $aluno['id_provas_online'];
                $idOnlineOK = (is_numeric($idOnline) && (int)$idOnline > 0);
            ?>
                <tr>
                    <td><?= htmlspecialchars($aluno['turma']) ?></td>

                    <td>
                        <?php if ($idOnlineOK): ?>
                            <span class="nome-link"
                                  data-matricula="<?= $aluno['matricula'] ?>"
                                  data-idprova="<?= $idOnline ?>"
                                  data-nome="<?= htmlspecialchars($aluno['nome']) ?>"
                                  data-materia="<?= htmlspecialchars($aluno['materia']) ?>">
                                  <?= htmlspecialchars($aluno['nome']) ?>
                            </span>
                        <?php else: ?>
                            <?= htmlspecialchars($aluno['nome']) ?>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($aluno['materia']) ?></td>

                    <td>
                        <?php if ($aluno['nota'] !== null): ?>
                            <span class="nota"><?= htmlspecialchars($aluno['nota']) ?></span>
                        <?php else: ?>
                            <span style="color:#888888">-</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= $aluno['data_prova'] ? date('d/m/Y', strtotime($aluno['data_prova'])) : '' ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$temLinha): ?>
                <tr>
                    <td colspan="5" style="text-align:center;color:#777777">
                        Nenhum aluno encontrado
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div><!-- /.container -->
</div><!-- /.main-content -->

<!-- ===========================================================
     POP-UP (detalhamento das respostas)
=========================================================== -->
<div class="popup-bg" id="popup-bg">
    <div class="popup-content" id="popup-content">
        <span class="fechar" onclick="fecharPopup()">&times;</span>
        <div id="popup-load"  style="text-align:center;margin:50px;">Carregando...</div>
        <div id="popup-dados" style="display:none"></div>
    </div>
</div>

<!-- ===========================================================
     SCRIPT
=========================================================== -->
<script>
/*-------------------------------------------------------------
 | Função para fechar o popup
 +-----------------------------------------------------------*/
function fecharPopup() {
    document.getElementById('popup-bg').style.display   = 'none';
    document.getElementById('popup-dados').style.display = 'none';
    document.getElementById('popup-load').style.display  = 'block';
    document.getElementById('popup-load').innerHTML      = 'Carregando...';
}

/*-------------------------------------------------------------
 | Associa evento de clique aos <span class="nome-link">
 +-----------------------------------------------------------*/
document.querySelectorAll('.nome-link').forEach(function (el) {

    el.addEventListener('click', function () {

        const matricula = el.getAttribute('data-matricula');
        const idProva   = el.getAttribute('data-idprova');  /* string */
        const nome      = el.getAttribute('data-nome');
        const materia   = el.getAttribute('data-materia');

        /* Segurança extra – só prossegue se idProva for numérico */
        if (!/^\d+$/.test(idProva)) {
            return;
        }

        /* Abre modal e mostra loader -------------------------*/
        document.getElementById('popup-bg').style.display   = 'flex';
        document.getElementById('popup-dados').style.display = 'none';
        document.getElementById('popup-load').style.display  = 'block';

        /* Faz a consulta AJAX -------------------------------*/
        fetch(
            'ajax_respostas_aluno.php'
            + '?matricula=' + encodeURIComponent(matricula)
            + '&idprova='   + encodeURIComponent(idProva)
        )
        .then(function (response) { return response.json(); })
        .then(function (ret) {

            const conteudo = document.getElementById('popup-dados');
            let html = '<h2>' + nome + '<br><small>' + materia + '</small></h2>';

            if (!ret.sucesso) {
                html += '<p style="color:#c22">' + (ret.msg || 'Erro desconhecido.') + '</p>';
            } else if (ret.respostas.length === 0) {
                html += '<p style="color:#888">Nenhuma resposta registrada para esta prova.</p>';
            } else {
                /* Percorre questões --------------------------------*/
                ret.respostas.forEach(function (q) {
                    html += '<div class="quest-detalhe">';
                    html += '<div class="quest-enun">' + q.enunciado + '</div>';

                    q.tentativas.forEach(function (t) {
                        const cls = t.correta ? 'resp-correta' : 'resp-errada';
                        html += 'T' + t.tentativa + ': '
                             + '<b class="' + cls + '">' + (t.resposta || '-') + '</b>'
                             + ' | Gab: <b>'   + t.gabarito          + '</b>'
                             + ' | Orig: <b>'  + t.gabarito_original + '</b><br>';
                    });

                    html += '</div>';
                });
            }

            conteudo.innerHTML = html;
            document.getElementById('popup-load').style.display  = 'none';
            conteudo.style.display                               = 'block';
        })
        .catch(function () {
            document.getElementById('popup-load').innerHTML = 'Erro ao carregar.';
        });
    });
});
</script>
</body>
</html>
