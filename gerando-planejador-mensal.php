<?php
require('conexao.php');

// Verifica se há um ID enviado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: planejador.php');
    exit();
}

$id = (int)$_GET['id'];

// Consulta para obter o planejamento selecionado
$sql = "SELECT * FROM planejadormensal WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->execute([$id]);
$planejamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$planejamento) {
    header('Location: planejador.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planejador Mensal - Universo do Saber</title>
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
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: var(--primary-blue);
            margin-bottom: 10px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h2 {
            color: var(--primary-blue);
            border-bottom: 1px solid var(--medium-gray);
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-item strong {
            color: var(--dark-blue);
            display: block;
            margin-bottom: 5px;
        }
        
        .content-section {
            margin-bottom: 30px;
        }
        
        .content-section h2 {
            color: var(--primary-blue);
            border-bottom: 1px solid var(--medium-gray);
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .periodo-info {
            background-color: var(--light-gray);
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: inline-block;
            font-weight: 500;
        }
        
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
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
        
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }
        
        .btn-secondary:hover {
            background-color: #d1d5db;
        }
        
        @media print {
            .btn-group {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="img/logo.png" alt="Universo do Saber" style="max-height: 80px;">
            </div>
            <h1>Planejador Mensal - <?php echo htmlspecialchars($planejamento['tipo']); ?> <?php echo htmlspecialchars($planejamento['sequencial']); ?></h1>
            <p>Universo do Saber - <a href="https://www.portaluniversodosaber.com.br" target="_blank">www.portaluniversodosaber.com.br</a></p>
        </div>
        
        <!-- Informações básicas -->
        <div class="info-section">
            <h2>Informações Gerais</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Escola:</strong>
                    <span><?php echo htmlspecialchars($planejamento['escola']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Professor:</strong>
                    <span><?php echo htmlspecialchars($planejamento['professor']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Curso:</strong>
                    <span><?php echo htmlspecialchars($planejamento['curso']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Ano:</strong>
                    <span><?php echo htmlspecialchars($planejamento['ano']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Componente Curricular:</strong>
                    <span><?php echo htmlspecialchars($planejamento['componente_curricular']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Aulas Semanais:</strong>
                    <span><?php echo htmlspecialchars($planejamento['numero_aulas_semanais']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Período de Realização:</strong>
                    <span><?php echo htmlspecialchars($planejamento['periodo_realizacao']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Objetivos -->
        <div class="content-section">
            <h2>Objetivos</h2>
            <div class="info-item">
                <strong>Objetivo Geral:</strong>
                <p><?php echo nl2br(htmlspecialchars($planejamento['objetivo_geral'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Objetivos Específicos:</strong>
                <p><?php echo nl2br(htmlspecialchars($planejamento['objetivo_especifico'])); ?></p>
            </div>
        </div>
        
        <!-- Conteúdos e Habilidades -->
        <div class="content-section">
            <h2>Conteúdos e Habilidades</h2>
            <div class="periodo-info">
                <?php echo htmlspecialchars($planejamento['tipo']); ?> - 
                <?php echo htmlspecialchars($planejamento['sequencial']); ?>
            </div>
            <div class="info-item">
                <strong>Unidade Temática:</strong>
                <p><?php echo htmlspecialchars($planejamento['unidade_tematica']); ?></p>
            </div>
            <div class="info-item">
                <strong>Objeto do Conhecimento:</strong>
                <p><?php echo nl2br(htmlspecialchars($planejamento['objeto_do_conhecimento'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Conteúdos:</strong>
                <p><?php echo nl2br(htmlspecialchars($planejamento['conteudos'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Habilidades:</strong>
                <p><?php echo nl2br(htmlspecialchars($planejamento['habilidades'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Metodologias:</strong>
                <p><?php echo nl2br(htmlspecialchars($planejamento['metodologias'])); ?></p>
            </div>
        </div>
        
        <div class="btn-group">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="planejador-mensal" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</body>
</html>