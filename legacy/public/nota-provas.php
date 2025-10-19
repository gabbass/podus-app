<?php
require('sessao-aluno.php');
require('conexao.php');
$matricula = $_SESSION['matricula'] ?? '';
$nome_aluno = $_SESSION['nome_login'] ?? '';

try {
    // Busca as provas e notas deste aluno
    $sql = "SELECT p.turma, p.materia, p.nota, po.data as data_prova
            FROM provas p
            LEFT JOIN provas_online po ON po.turma = p.turma AND po.materia = p.materia
            WHERE p.matricula = ?
            ORDER BY po.data DESC, p.turma, p.materia";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$matricula]);
    $provas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    die("Erro ao buscar notas: " . $e->getMessage());
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
            <!-- <li><a href="#"><i class="fas fa-edit"></i> <span>Fazer Prova</span></a></li> -->
			<li><a href="material-apoio.php"><i class="fas fa-book"></i> <span>Material de apoio</span></a></li>
            <li><a href="nota-provas.php" class="active"><i class="fas fa-clipboard-list"></i> <span>Ver Notas</span></a></li>
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
 
    <h1><i class="fas fa-star"></i> Minhas Notas</h1>
    <div style="margin-bottom:17px; color:var(--dark-gray);font-size:1.11em;">
        Olá, <b><?= htmlspecialchars($nome_aluno) ?></b>! Aqui estão suas notas das provas realizadas:
    </div>
    <table class="table-notas">
        <thead>
            <tr>
                <th>Turma</th>
                <th>Matéria</th>
                <th>Nota</th>
                <th>Data Prova</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($provas)): ?>
                <?php foreach($provas as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['turma']) ?></td>
                        <td><?= htmlspecialchars($p['materia']) ?></td>
                        <td>
                            <?php if($p['nota']!==null): ?>
                                <span class="nota"><?= htmlspecialchars($p['nota']) ?></span>
                            <?php else: ?>
                                <span style="color:#888">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $p['data_prova'] ? date('d/m/Y', strtotime($p['data_prova'])) : "" ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="color:#777;text-align:center">Nenhuma nota registrada até agora.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
<script>
        // Menu toggle for mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth <= 1200 && 
                !sidebar.contains(event.target) && 
                event.target !== menuToggle && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
</script>
</body>
</html>
