<?php
require('sessao-professor.php');
require('conexao.php');

$login = $_SESSION['login'] ?? '';
$msg = "";
$erro = "";

if (isset($_GET['msg'])) $msg = $_GET['msg'];

try {
    $stmt_turmas = $conexao->prepare("SELECT * FROM turmas WHERE login=?");
    $stmt_turmas->execute([$login]);
    $turmas = $stmt_turmas->fetchAll(PDO::FETCH_ASSOC);

    $stmt_questoes = $conexao->query("SELECT * FROM questoes ORDER BY id DESC");
    $questoes = $stmt_questoes->fetchAll(PDO::FETCH_ASSOC);

    $stmt_provas = $conexao->prepare(
        "SELECT po.*, t.nome AS turma_nome
        FROM provas_online po
        LEFT JOIN turmas t ON t.nome = po.turma
        WHERE po.login = ?
        ORDER BY po.id DESC"
    );
    $stmt_provas->execute([$login]);
    $provas = $stmt_provas->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}

// Salvar nova prova
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['salvar_prova'])) {
    try {
        $turma_id = $_POST['turma'];
        $escola   = isset($_POST['escola']) && trim($_POST['escola']) !== '' ? $_POST['escola'] : '';
        $materia  = $_POST['materia'];
        $lista_quest = $_POST['lista_quest'] ?? [];
        $data = date('Y-m-d');

        $nome_turma = '';
        foreach($turmas as $t){
            if($t['id'] == $turma_id){
                $nome_turma = $t['nome'];
                break;
            }
        }
        if ($nome_turma == "") throw new Exception("Turma selecionada não encontrada.");

        if (empty($_POST['edit_id'])) {
            // Salva a definição da prova
            $ins = $conexao->prepare(
                "INSERT INTO provas_online (data, turma, materia, login, escola, lista_quest)
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            $ins->execute([
                $data,
                $nome_turma,
                $materia,
                $login,
                $escola,
                implode(',', $lista_quest)
            ]);
            // Cria uma linha em provas para cada aluno
            $stmt_alunos = $conexao->prepare("SELECT matricula FROM login WHERE turma=? AND perfil='Aluno'");
            $stmt_alunos->execute([$nome_turma]);
            $alunos = $stmt_alunos->fetchAll(PDO::FETCH_COLUMN);

            foreach($alunos as $matricula) {
                $checa = $conexao->prepare(
                    "SELECT COUNT(*) FROM provas WHERE turma=? AND matricula=? AND materia=?"
                );
                $checa->execute([$nome_turma, $matricula, $materia]);
                if ($checa->fetchColumn() == 0) {
                    $ins_prova = $conexao->prepare(
                        "INSERT INTO provas (data, turma, id_questao, login, matricula, materia, nota)
                        VALUES (?, ?, NULL, ?, ?, ?, NULL)"
                    );
                    $ins_prova->execute([
                        $data,
                        $nome_turma,
                        $login,
                        $matricula,
                        $materia
                    ]);
                }
            }
            header("Location: cadastrar-provas.php?msg=" . urlencode("Prova criada com sucesso!"));
            exit;
        } else {
            // Edição
            $id = intval($_POST['edit_id']);
            $upd = $conexao->prepare(
                "UPDATE provas_online
                SET turma=?, materia=?, escola=?, lista_quest=?
                WHERE id=? AND login=?"
            );
            $upd->execute([
                $nome_turma,
                $materia,
                $escola,
                implode(',', $lista_quest),
                $id,
                $login
            ]);
            header("Location: cadastrar-provas.php?msg=" . urlencode("Prova atualizada!"));
            exit;
        }
    } catch(Exception $e) {
        $erro = "Erro ao salvar: " . $e->getMessage();
    }
}

// Edição/pré-preenchimento
$prova_edit = false;
if (isset($_GET['edit']) && $_GET['edit']!='novo') {
    try {
        $stmt = $conexao->prepare("SELECT * FROM provas_online WHERE id=? AND login=?");
        $stmt->execute([$_GET['edit'], $login]);
        $prova_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        $erro = "Erro ao buscar prova: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastrar Prova - Universo do Saber</title>
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
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body {
    background-color: var(--light-gray);
    min-height: 100vh;
   
}
.container {
    max-width: 1000px;
    margin: 0 auto 30px auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    padding: 25px;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.page-title h1 {
    font-size: 1.8rem;
    color: var(--dark-blue);
    font-weight: 600;
    margin-bottom: 5px;
}
.btn {
    padding: 10px 20px;
    border-radius: 4px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}
.btn i { margin-right: 8px; }
.btn-primary {
    background-color: var(--primary-blue);
    color: white;
}
.btn-primary:hover { background-color: var(--dark-blue);}
.btn-secondary {
    background-color: var(--medium-gray);
    color: var(--dark-gray);
}
.btn-secondary:hover { background-color: #d1d5db;}
.form-group { margin-bottom: 20px; }
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--dark-gray);
}
.form-group input[type="text"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--medium-gray);
    border-radius: 4px;
    font-size: 1rem;
}
.form-group textarea {
    min-height: 50px;
    resize: vertical;
}
.form-row { display: flex; gap: 15px; margin-bottom: 20px;}
.form-col { flex: 1; }
.error-message {
    color: #dc3545;
    margin-top: 5px;
    font-size: 0.96rem;
    margin-bottom:14px;
}
.success-message {
    background-color: #08FF08;
    color: #000;
    padding: 10px;
    border-radius: 5px;
    font-weight: bold;
    margin-bottom:18px;
    text-align:center;
}
.table-miniprovas {
    margin-top:32px;
    width: 100%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 8px #ececec;
    overflow-x:auto;
}
.table-miniprovas th, .table-miniprovas td {
    padding: 8px 5px;
    border-bottom:1px solid #eee;
    text-align:left;
}
.table-miniprovas th { color:var(--dark-blue); font-weight:600;}
.table-miniprovas tr:last-child td { border-bottom:0;}

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
        
        /* Content */
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
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            margin-top: 10px;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
        }
        
        /* Cards */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            transition: all 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            cursor:pointer;
        }
        
        .card-title {
            font-size: 1rem;
            color: var(--dark-gray);
            font-weight: 500;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .card-icon.blue {
            background: linear-gradient(to right, var(--primary-blue), var(--dark-blue));
        }
        
        .card-icon.orange {
            background: linear-gradient(to right, var(--primary-orange), var(--dark-orange));
        }
        
        .card-icon.green {
            background: linear-gradient(to right, #28a745, #1e7e34);
        }
        
        .card-body h2 {
            font-size: 2.2rem;
            color: var(--dark-blue);
            margin-bottom: 5px;
        }
        
        .card-footer {
            margin-top: 15px;
            font-size: 0.8rem;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
        }
        
        .card-footer i {
            margin-right: 5px;
        }
        
        .positive {
            color: #28a745;
        }
        
        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 1.2rem;
            color: var(--dark-blue);
            font-weight: 600;
        }
        
        .chart-wrapper {
            position: relative;
            height: 400px;
        }
        
        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
        }
        
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .activity-title {
            font-size: 1.2rem;
            color: var(--dark-blue);
            font-weight: 600;
        }
        
        .activity-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .activity-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(0, 87, 183, 0.1);
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1rem;
        }
        
        .activity-content h4 {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 5px;
        }
        
        .activity-content p {
            font-size: 0.8rem;
            color: var(--dark-gray);
        }
        
        .activity-time {
            font-size: 0.7rem;
            color: var(--dark-gray);
            margin-left: auto;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--dark-gray);
            font-size: 1.5rem;
            cursor: pointer;
            margin-right: 15px;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 1000;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .top-nav {
                position: sticky;
                top: 0;
                z-index: 100;
            }
        }
        
        @media (max-width: 768px) {
            .card-container {
                grid-template-columns: 1fr;
            }
            
            .top-nav {
                padding: 15px;
            }
            
            .search-bar {
                order: 2;
                width: 100%;
                margin-top: 15px;
            }
            
            .search-bar input {
                width: 100%;
            }
            
            .user-area {
                order: 1;
            }
            
            .content {
                padding: 15px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .chart-wrapper {
                height: 300px;
            }
        }
        
        @media (max-width: 480px) {
            .activity-item {
                flex-direction: column;
            }
            
            .activity-time {
                margin-left: 55px;
                margin-top: 5px;
            }
            
            .card-body h2 {
                font-size: 1.8rem;
            }
            
            .chart-wrapper {
                height: 250px;
            }
        }
/* Responsivo simples */
@media (max-width: 600px) {
    .form-row { flex-direction:column; }
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
            <li><a href="#" ><i class="fas fa-home"></i> <span>Início</span></a></li>
            <li><a href="meu-cadastro-professor"><i class="fas fa-chalkboard-teacher"></i> <span>Meu Cadastro</span></a></li>
            <li><a href="questoes-professor"><i class="fas fa-question-circle"></i> <span>Questões</span></a></li>
            <li><a href="cadastrar-alunos"><i class="fas fa-user-graduate"></i> <span>Alunos</span></a></li>
            <li><a href="cadastrar-turmas"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
            <li><a href="jogos-pedagocicos"><i class="fas fa-gamepad"></i> <span>Jogos Pedagógicos</span></a></li>
            <li><a href="planejador"><i class="fas fa-calendar-alt"></i> <span>Planejador Anual</span></a></li>
            <li><a href="planejador-mensal"><i class="fas fa-calendar-alt"></i> <span>Planejador Mensal</span></a></li>
            <li><a href="material-pedagogico"><i class="fas fa-book"></i> <span>Material Pedagógico</span></a></li>
			 <li><a href="cadastrar-provas.php" class="active"><i class="fas fa-book"></i> <span>Provas</span></a></li>
			  <li><a href="notas-alunos.php"><i class="fas fa-book"></i> <span>Notas</span></a></li>
            <li><a href="sair.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
        </ul>
    </div>
  <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <!--<button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>-->
            
            <div class="user-area" style='display:none'>
                <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="user-img"><?php echo substr($_SESSION['nome_login'], 0, 2); ?></div>
                <div class="user-name"><?php echo $_SESSION['nome_login']; ?></div>
            </div>
            
            
        </div>
<div class="container">
    <div class="page-header">
        <div class="page-title"><h1><?php echo $prova_edit ? "Editar Prova" : "Cadastrar Nova Prova"; ?></h1></div>
        <a href="dashboard-professor.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
    <?php if ($msg): ?>
        <div class="success-message"><?php echo $msg; ?></div>
        <script>
            setTimeout(function() { location.href = 'cadastrar-provas.php'; }, 3500);
        </script>
    <?php endif; ?>
    <?php if ($erro): ?>
        <div class="error-message"><?php echo $erro; ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="edit_id" value="<?= $prova_edit ? $prova_edit['id'] : "" ?>">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label>Turma</label>
                    <select name="turma" required>
                        <option value="">Selecione...</option>
                        <?php foreach($turmas as $t): ?>
                            <option value="<?= $t['id'] ?>"
                                <?= $prova_edit && $prova_edit['turma']==$t['nome'] ? "selected" : "" ?>>
                                <?= htmlspecialchars($t['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label>Escola <small style="color:#888">(opcional)</small></label>
                    <input type="text" name="escola" value="<?= $prova_edit ? htmlspecialchars($prova_edit['escola']) : "" ?>">
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label>Matéria</label>
                    <input type="text" name="materia" required value="<?= $prova_edit ? htmlspecialchars($prova_edit['materia']) : "" ?>" placeholder="Ex: Matemática">
                </div>
            </div>
            <div class="form-col">
                <!-- espaço para possível novo campo futuramente -->
            </div>
        </div>
        <div class="form-group">
            <label>Questões da Prova<br>
            <span style="font-size:0.96em;font-weight:normal;color:#555">Segure Ctrl/Cmd e clique para selecionar mais de uma questão</span>
            </label>
            <select name="lista_quest[]" multiple required size="7" style="font-size:1em;">
                <?php foreach($questoes as $q): ?>
                    <option value="<?= $q['id'] ?>"
                        <?php if($prova_edit && in_array($q['id'], explode(',', $prova_edit['lista_quest']))) echo "selected"; ?>>
                        <?= $q['id'] ?> - <?= htmlspecialchars(mb_substr($q['questao'],0,40)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-top: 30px;">
            <button type="submit" name="salvar_prova" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $prova_edit ? "Salvar Alterações" : "Cadastrar Prova"; ?>
            </button>
        </div>
    </form>
    <!-- Mini listagem das provas existentes -->
    <table class="table-miniprovas">
        <thead>
            <tr>
                <th>#</th>
                <th>Turma</th>
                <th>Matéria</th>
                <th>Questões IDs</th>
                <th>Escola</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($provas as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['turma_nome'] ?? $r['turma']) ?></td>
                <td><?= htmlspecialchars($r['materia']) ?></td>
                <td><?= htmlspecialchars($r['lista_quest']) ?></td>
                <td><?= htmlspecialchars($r['escola']) ?></td>
                <td>
                    <a class="btn btn-primary" style="padding:5px 12px;font-size:0.98em"
                        href="?edit=<?= $r['id'] ?>"><i class="fas fa-edit"></i> Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(!count($provas)): ?>
            <tr><td colspan="6" style="text-align:center;color:#777">Nenhuma prova cadastrada.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
	</div>
</div>


</body>
</html>
