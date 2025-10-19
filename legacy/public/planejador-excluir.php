<?php
require('conexao.php');

if (!isset($_GET['id'])) {
    header('Location: planejador-pesquisar.php');
    exit();
}

$id = $_GET['id'];

// Verificar se o planejamento existe
$query = "SELECT id FROM planejador WHERE id = ?";
$stmt = $conexao->prepare($query);
$stmt->execute([$id]);
$planejamento = $stmt->fetch();

if (!$planejamento) {
    header('Location: planejador-pesquisar.php');
    exit();
}

// Processar a exclusão se confirmado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "DELETE FROM planejador WHERE id = ?";
        $stmt = $conexao->prepare($query);
        $stmt->execute([$id]);
        
        header('Location: planejador-pesquisar.php?excluido=1');
        exit();
    } catch (PDOException $e) {
        $erro = "Erro ao excluir planejamento: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Planejamento</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            line-height: 1.6;
        }

        .header {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .confirmation-box {
            background-color: white;
            padding: 20px;
            margin: 0 auto;
            max-width: 600px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .confirmation-box p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        button, .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
        }

        .btn-confirm {
            background-color: #f44336;
        }

        .btn-confirm:hover {
            background-color: #d32f2f;
        }

        .btn-cancel {
            background-color: #2196F3;
        }

        .btn-cancel:hover {
            background-color: #0b7dda;
        }

        .mensagem {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .erro {
            background-color: #f2dede;
            color: #a94442;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            
            .confirmation-box {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Excluir Planejamento</h1>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="mensagem erro"><?= $erro ?></div>
    <?php endif; ?>

    <div class="confirmation-box">
        <p>Você tem certeza que deseja excluir este planejamento? Esta ação não pode ser desfeita.</p>
        
        <form method="POST" action="">
            <div class="button-container">
                <button type="submit" class="btn-confirm">Confirmar Exclusão</button>
                <a href="planejador-pesquisar.php" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>