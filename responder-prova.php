<?php
require('conexao.php');
require('sessao-aluno.php');

/* --- Parâmetros básicos --- */
$id_provas_online = $_GET['id'] ?? '';
$matricula        = $_SESSION['matricula'] ?? '';
$nome_aluno       = $_SESSION['nome_login'] ?? '';

if (!$matricula)       die('MATRÍCULA NÃO SETADA NA SESSÃO!');
if (!$id_provas_online) die('ID da prova não passado na URL!');

$msg        = '';
$acertos    = 0;
$nota_final = null;
$questao    = null;

/* --- Carrega definição da prova online --- */
$stmt = $conexao->prepare("SELECT * FROM provas_online WHERE id = ?");
$stmt->execute([$id_provas_online]);
$provaOnline = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$provaOnline) $msg = 'Prova não encontrada!';

if (!$msg) {

    /* --- Garante linha na tabela provas --- */
    $stmt_ver = $conexao->prepare(
        "SELECT * FROM provas WHERE matricula=? AND turma=? AND materia=?"
    );
    $stmt_ver->execute([$matricula, $provaOnline['turma'], $provaOnline['materia']]);
    $provaAluno = $stmt_ver->fetch(PDO::FETCH_ASSOC);

    if (!$provaAluno) {
        $conexao->prepare(
            "INSERT INTO provas (matricula,turma,materia,tentativa_feita,data)
             VALUES (?,?,?,0,NOW())"
        )->execute([$matricula,$provaOnline['turma'],$provaOnline['materia']]);

        $stmt_ver->execute([$matricula,$provaOnline['turma'],$provaOnline['materia']]);
        $provaAluno = $stmt_ver->fetch(PDO::FETCH_ASSOC);
    }

    $id_provas = $provaAluno['id'];
    $tentativa = min(3, (int)$provaAluno['tentativa_feita'] + 1);   // 1‥3

    /* --- Lista de questões (agora embaralhada 1× por sessão) --- */
    $lista_raw = [];
    if ($provaOnline['lista_quest'])
        $lista_raw = array_filter(array_map('trim', explode(',', $provaOnline['lista_quest'])));
    elseif ($provaOnline['id_questao'])
        $lista_raw[] = $provaOnline['id_questao'];

    $sessKey = "ordem_questoes_{$id_provas_online}_{$matricula}";
    if (!isset($_SESSION[$sessKey])) {
        $_SESSION[$sessKey] = $lista_raw;
        shuffle($_SESSION[$sessKey]);               // embaralha só na 1ª vez
    }
    $lista_questoes = $_SESSION[$sessKey];
    $total_questoes = count($lista_questoes);

    /* --- Descobre em que questão está --- */
    $questao_atual = isset($_POST['next_q']) ? (int)$_POST['next_q'] : 0;

    /* ========== PROCESSA RESPOSTA (apenas se veio resposta) ========== */
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id_questao'],$_POST['resposta'])) {

        $id_q     = (int)$_POST['id_questao'];
        $resposta = $_POST['resposta'];

        /* Gabarito da questão */
       $stmt_gab = $conexao->prepare("SELECT resposta, alternativa_A, alternativa_B, alternativa_C, alternativa_D, alternativa_E FROM questoes WHERE id=?");
		$stmt_gab->execute([$id_q]);
		$dados_questao = $stmt_gab->fetch(PDO::FETCH_ASSOC);

		$gabarito = strtoupper($dados_questao['resposta']); // ex: 'B'
		$gabarito_original = $dados_questao['alternativa_' . $gabarito] ?? '';

        /* Verifica se já existe registro */
        $stmt_reg = $conexao->prepare(
            "SELECT id FROM respostas_alunos
             WHERE id_provas_online=? AND id_provas=? AND id_questao=? AND id_matricula=?"
        );
        $stmt_reg->execute([$id_provas_online,$id_provas,$id_q,$matricula]);
        $oldId = $stmt_reg->fetchColumn();

        /* Campo dinâmico da tentativa */
        $colResp = "resposta_tenta{$tentativa}";
        $colGab  = "gabarito_tenta{$tentativa}";

        if ($oldId) {
            $conexao->prepare(
                "UPDATE respostas_alunos
                 SET $colResp=?, $colGab=? WHERE id=?"
            )->execute([$resposta,$gabarito,$oldId]);
        } else {
            $conexao->prepare(
                "INSERT INTO respostas_alunos
                 (id_provas_online,id_provas,id_questao,id_matricula,$colResp,$colGab)
                 VALUES (?,?,?,?,?,?)"
            )->execute([$id_provas_online,$id_provas,$id_q,$matricula,$resposta,$gabarito]);
        }
        $questao_atual++;
    }

    /* ========== MONTA PRÓXIMA QUESTÃO (só após POST iniciar / responder) ========== */
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($lista_questoes[$questao_atual])) {
        $qid  = (int)$lista_questoes[$questao_atual];
        $stmt = $conexao->prepare("SELECT * FROM questoes WHERE id=?");
        $stmt->execute([$qid]);
        $questao = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ========== FINALIZAÇÃO ========== */
    if ($_SERVER['REQUEST_METHOD']==='POST' && $questao_atual >= $total_questoes) {

        $resps = $conexao->prepare(
            "SELECT * FROM respostas_alunos
             WHERE id_provas_online=? AND id_provas=? AND id_matricula=?"
        );
        $resps->execute([$id_provas_online,$id_provas,$matricula]);
        $acertos = 0;

        foreach ($resps->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $resp = strtoupper($r["resposta_tenta{$tentativa}"] ?? '');
            $gab  = strtoupper($r["gabarito_tenta{$tentativa}"] ?? '');
            if ($resp && $gab && $resp === $gab) $acertos++;
        }

        $nota_final = $total_questoes ? round($acertos / $total_questoes * 10, 2) : 0;
        $colNota    = "nota_tenta{$tentativa}";

        $conexao->prepare(
            "UPDATE provas SET tentativa_feita=tentativa_feita+1, $colNota=? WHERE id=? AND matricula=?"
        )->execute([$nota_final,$id_provas,$matricula]);
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Prova - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
          :root {
            --primary-blue: #0057b7;
            --primary-orange: #ffa500;
            --dark-blue: #003d7a;
            --dark-orange: #cc8400;
            --light-gray: #f5f7fa;
            --medium-gray: #e1e5eb;
            --dark-gray: #6c757d;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: var(--light-gray);
            display: flex;
            min-height: 100vh;
        }
		/* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }
        .sidebar-header {
            padding: 20px;
            background: linear-gradient(to right, var(--primary-blue), var(--dark-blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu li {
            list-style: none;
            margin-bottom: 5px;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--dark-gray);
            text-decoration: none;
            transition: all 0.3s;
        }
			.sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(0, 87, 183, 0.1);
            color: var(--primary-blue);
            border-left: 4px solid var(--primary-blue);
        }
        .sidebar-menu a i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .container {
			
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.06);
            padding: 38px 29px 42px 29px;
            width: 100%;
			
        }
        h1 {
            color: var(--dark-blue);
            text-align: center;
        }
        .info {
            text-align: center;
            font-size: 1.1em;
            margin: 13px 0 28px 0;
        }
        .questao-box {
            margin-bottom: 25px;
            padding: 24px 16px;
            background: var(--medium-gray);
            border-radius: 7px;
        }
        .questao-enunciado {
            font-weight: 500;
            color: var(--primary-blue);
            margin-bottom: 9px;
        }
        label {
            display: block;
            background: #f8fafc;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 7px;
            cursor: pointer;
            border: 1px solid transparent;
        }
        input[type="radio"]:checked + label {
            background: var(--primary-orange);
            color: #1a1a1a;
            border-color: var(--primary-blue);
        }
        .btn {
            padding: 10px 28px;
            border-radius: 4px;
            border: none;
            background: var(--primary-blue);
            color: white;
            font-weight: bold;
            font-size: 1.08em;
            margin: 0 auto;
            display: block;
            cursor: pointer;
            transition: background 0.22s;
        }
        .btn:disabled { opacity: 0.5; }
        .finalizado {
            text-align: center;
            font-size: 1.15em;
            margin-top: 12px;
            background: #eef8e4;
            border-radius: 8px;
            padding: 24px;
        }
		
		/* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
			
        }
        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .user-area {
            display: flex;
            align-items: center;
        }
        .user-area .notification {
            position: relative;
            margin-right: 20px;
            color: var(--dark-gray);
            cursor: pointer;
        }
        .user-area .notification .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--primary-orange);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .user-area .user-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        .user-area .user-name {
            font-weight: 500;
            color: var(--dark-gray);
        }
        .search-bar {
            display: flex;
            align-items: center;
        }
        .search-bar input {
            padding: 8px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            outline: none;
            width: 250px;
            transition: all 0.3s;
        }
        .search-bar input:focus {
            border-color: var(--primary-blue);
        }
        .search-bar button {
            background: none;
            border: none;
            margin-left: -30px;
            color: var(--dark-gray);
            cursor: pointer;
        }
        .content {
            padding: 30px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }
        .page-title p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
		
        .finalizado strong { color: #0e974b; }
        .voltar {
            background: var(--primary-orange) !important;
            margin-top: 32px;
            color: #222 !important;
        }
        @media(max-width:800px){
            .container{padding:10px;}
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-graduation-cap"></i>
            <h3>Universo do Saber</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard-aluno.php"><i class="fas fa-home"></i><span>Início</span></a></li>
            <!-- <li><a href="#"><i class="fas fa-edit"></i> <span>Fazer Prova</span></a></li> -->
            <li><a href="#" class="active"><i class="fas fa-clipboard-list"></i> <span>Prova atual</span></a></li>
            <!-- <li><a href="material-pedagogico-aluno"><i class="fas fa-book"></i> <span>Material Pedagógico</span></a></li> -->
            <li><a href="sair"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
        </ul>
    </div>
  <div class="main-content">
  
   <div class="top-nav">
            <!--<button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>-->
            
            <div class="user-area" style='display:none'>
               <!-- <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>-->
                <div class="user-img"><?php echo substr($_SESSION['nome_login'], 0, 2); ?></div>
                <div class="user-name"><?php echo $_SESSION['nome_login']; ?></div>
            </div>
            
            
        </div>
<div class="container">
    <h1>Prova Online</h1>

    <?php if ($msg): ?>
        <div class="info" style="color:#d8002f"><?= htmlspecialchars($msg) ?></div>
        <a href="index.php" class="btn voltar">Voltar para Início</a>

    <?php elseif ($nota_final !== null): ?>
        <div class="finalizado">
            <p><strong>Prova Finalizada!</strong></p>
            <p>Olá <?= htmlspecialchars($nome_aluno) ?><br>
            Você acertou <strong><?= $acertos ?></strong> de <strong><?= $total_questoes ?></strong> questões.<br>
            <b>Sua nota: <?= $nota_final ?></b></p>
            <a href="dashboard-aluno.php" class="btn voltar"><i class="fas fa-arrow-left"></i> Voltar para tela inicial</a>
        </div>

    <?php elseif ($questao): ?>
        <div class="info">
            Olá <b><?= htmlspecialchars($nome_aluno) ?></b>,
            Questão <?= $questao_atual + 1 ?> de <?= $total_questoes ?>
        </div>
        <form method="post" autocomplete="off">
            <input type="hidden" name="id_questao" value="<?= $questao['id'] ?>">
            <input type="hidden" name="next_q" value="<?= $questao_atual ?>">

            <div class="questao-box">
                <div class="questao-enunciado">
                    <?= nl2br(htmlspecialchars($questao['pergunta'] ?? $questao['questao'])) ?>
                </div>
						<?php
						/* 1. Gera array [ ['orig' => 'A', 'texto' => 'Mercúrio'], … ]  */
						$alternativas = [];
						foreach (['A','B','C','D','E'] as $letra) {
							$campo = 'alternativa_'.$letra;
							if (!empty($questao[$campo])) {
								$alternativas[] = ['orig' => $letra, 'texto' => $questao[$campo]];
							}
						}

						shuffle($alternativas);            // embaralha só a posição

						/* 2. Exibe: valor = letra ORIGINAL; rótulo = letra VISUAL sequencial */
						$idx = 0;
						foreach ($alternativas as $alt):
							$letraVisual = chr(ord('A') + $idx);   // A, B, C…
							$idCampo     = 'opt'.$idx;             // id único p/ label-for
						?>
							<input style="display:none"
								   type="radio"
								   name="resposta"
								   id="<?= $idCampo ?>"
								   value="<?= $alt['orig'] ?>"     
								   required>

							<label for="<?= $idCampo ?>">
								<strong><?= $letraVisual ?>)</strong>
								<?= htmlspecialchars($alt['texto']) ?>
							</label>
						<?php
							$idx++;
						endforeach;
						?>

            </div>

            <button class="btn" type="submit">
                <?= ($questao_atual + 1 === $total_questoes) ? 'Finalizar' : 'Próxima' ?>
            </button>
        </form>

    <?php else: ?>
        <div class="info">
            <b>Bem-vindo, <?= htmlspecialchars($nome_aluno) ?></b>!<br>
            Você tem até 3 tentativas.<br>
            Clique para começar sua prova:
        </div>
        <form method="post">
            <input type="hidden" name="id_questao" value="<?= $lista_questoes[0] ?? '' ?>">
            <input type="hidden" name="next_q" value="0">
            <button class="btn" type="submit">Iniciar Prova</button>
        </form>
    <?php endif; ?>
</div>

</div>
</body>
</html>
