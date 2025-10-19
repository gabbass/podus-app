<?php
require('sessao-adm.php');
require('conexao.php');

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: pesquisar-artigos.php");
    exit();
}

$id_artigo = (int)$_GET['id'];
$mensagem = '';

try {
    // Buscar dados do artigo
    $stmt_busca = $conexao->prepare("SELECT id, nome, imagem FROM artigo WHERE id = ?");
    $stmt_busca->execute([$id_artigo]);
    
    if ($stmt_busca->rowCount() === 0) {
        $mensagem = "<div class='alert alert-danger'>Artigo não encontrado.</div>";
    } else {
        $artigo = $stmt_busca->fetch(PDO::FETCH_ASSOC);

        // Excluir registro do banco
        $stmt_delete = $conexao->prepare("DELETE FROM artigo WHERE id = ?");
        $stmt_delete->execute([$id_artigo]);

        // Excluir pasta do artigo e suas imagens
        $pasta_artigo = "artigos/" . $id_artigo;
        if (is_dir($pasta_artigo)) {
            array_map('unlink', glob("$pasta_artigo/*"));
            rmdir($pasta_artigo);
        }

        $mensagem = "<div class='alert alert-success'>Artigo '{$artigo['nome']}' excluído com sucesso.</div>";
    }
} catch (PDOException $e) {
    $mensagem = "<div class='alert alert-danger'>Erro ao excluir artigo: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Excluir Artigo - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css ">
    <style>
        :root {
            --primary-blue: #0057b7;
            --dark-blue: #003d7a;
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
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: rgba(0, 87, 183, 0.1);
            color: var(--primary-blue);
            border-left: 4px solid var(--primary-blue);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: var(--dark-blue);
            margin-bottom: 20px;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 10px 20px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn:hover {
            background: var(--dark-blue);
        }

        .center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .icon-delete {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 15px;
        }

    </style>
</head>
<body>

    <!-- Sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <div class="card center">
            <i class="fas fa-trash-alt icon-delete"></i>
            <h2>Excluir Artigo</h2>

            <?= $mensagem ?>

            <button class="btn" onclick="location.href='pesquisar-artigos.php'">Voltar</button>
        </div>
    </div>

</body>
</html>