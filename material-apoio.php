<?php
require('sessao-aluno.php');
require('conexao.php');
$matricula = $_SESSION['matricula'] ?? '';
$nome_aluno = $_SESSION['nome_login'] ?? '';

try {
    $sql = "SELECT p.turma, p.materia, p.nota, po.data as data_prova
            FROM provas p
            LEFT JOIN provas_online po ON po.turma = p.turma AND po.materia = p.materia
            WHERE p.matricula = ?
            ORDER BY po.data DESC, p.turma, p.materia";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$matricula]);
    $provas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar turma(s) do aluno
    $sql_turma = "SELECT turma FROM login WHERE matricula = ?";
    $stmt_t = $conexao->prepare($sql_turma);
    $stmt_t->execute([$matricula]);
    $row_turma = $stmt_t->fetch(PDO::FETCH_ASSOC);
    $turma_aluno = $row_turma ? $row_turma['turma'] : '';

    // Buscar materiais para a turma do aluno
    $materiais = [];
    if($turma_aluno) {
        $stmt_mat = $conexao->prepare("SELECT turma, materia, descricao, caminho_arquivo, data_envio FROM materiais_pedagogicos WHERE turma = ? ORDER BY data_envio DESC");
        $stmt_mat->execute([$turma_aluno]);
        $materiais = $stmt_mat->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(Exception $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Minhas Notas - Universo do Saber</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
:root {
    --primary-blue: #0057b7;
    --primary-orange: #ffa500;
    --dark-blue: #003d7a;
    --light-gray: #f5f7fa;
    --medium-gray: #e1e5eb;
    --dark-gray: #6c757d;
}
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
 body {
            background-color: var(--light-gray);
            display: flex;
            min-height: 100vh;
        }
.container {
    margin: 0 auto 30px auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    padding: 25px;
}

.main-content {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }
h1 {
    color: var(--dark-blue);
    margin-bottom: 18px;
    font-size: 1.65em;
}
.table-notas {
    width: 100%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 8px #ececec;
    overflow-x:auto;
}
.table-notas th, .table-notas td {
    padding: 8px 5px;
    border-bottom:1px solid #eee;
    text-align:left;
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

.table-notas th { color:var(--dark-blue); font-weight:600;}
.table-notas tr:last-child td { border-bottom:0;}
.nota { font-weight:bold; color:var(--primary-orange);}
@media(max-width:700px) {
    .container{padding:5px;}
    .table-notas th,.table-notas td{ font-size:0.98em;}
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
        <li><a href="dashboard-aluno.php"><i class="fas fa-home"></i> <span>Início</span></a></li>
		<li><a href="material-apoio.php" class="active"><i class="fas fa-book"></i> <span>Material de apoio</span></a></li>
        <li><a href="nota-provas.php"><i class="fas fa-clipboard-list"></i> <span>Ver Notas</span></a></li>
        <li><a href="sair"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>
<div class="main-content">
<div class="top-nav">
    <div class="user-area" style='display:none'>
        <div class="user-img"><?php echo substr($_SESSION['nome_login'], 0, 2); ?></div>
        <div class="user-name"><?php echo $_SESSION['nome_login']; ?></div>
    </div>
</div>
<div class="container">
        <h1 style="margin-top:36px;"><i class="fas fa-book"></i> Materiais de Apoio</h1>
    <div style="margin-bottom:17px; color:var(--dark-gray);font-size:1.11em;">
        Veja abaixo os materiais de apoio destinados à sua turma.</b>
    </div>
    <table class="table-notas">
        <thead>
            <tr>
                <th>Turma</th>
                <th>Matéria</th>
                <th>Descrição</th>
                <th>Material</th>
                <th>Enviado em</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($materiais)): ?>
                <?php foreach($materiais as $mat): ?>
                    <tr>
                        <td><?= htmlspecialchars($mat['turma']) ?></td>
                        <td><?= htmlspecialchars($mat['materia']) ?></td>
                        <td><?= nl2br(htmlspecialchars($mat['descricao'])) ?></td>
                        <td>
                            <?php if($mat['caminho_arquivo']): ?>
                                <a href="<?= htmlspecialchars($mat['caminho_arquivo']) ?>" target="_blank" style="color:var(--primary-blue);font-weight:bold;text-decoration:underline">
                                    <i class="fas fa-download"></i> Baixar/Abrir
                                </a>
                            <?php else: ?>
                                <span style="color:#888">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mat['data_envio'] ? date('d/m/Y', strtotime($mat['data_envio'])) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="color:#777;text-align:center">Nenhum material disponível para sua turma.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
</body>
</html>