<?php
require('conexao.php');

// Verifica se há IDs enviados
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    header('Location: planejador.php');
    exit();
}

$ids = explode(',', $_GET['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// Consulta para obter todos os planejamentos selecionados ordenados por data
$sql = "SELECT * FROM planejador WHERE id IN ($placeholders) ORDER BY data ASC";
$stmt = $conexao->prepare($sql);
$stmt->execute($ids);
$planejamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($planejamentos) === 0) {
    header('Location: planejador.php');
    exit();
}

// Pegar o primeiro registro para os campos únicos
$primeiroRegistro = $planejamentos[0];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planejador Anual - Universo do Saber</title>
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
        
        .content-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--medium-gray);
        }
        
        .content-item:last-child {
            border-bottom: none;
        }
        
        .content-item h3 {
            color: var(--dark-blue);
            margin-bottom: 10px;
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
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        @media print {
            .btn-group {
                display: none;
            }
            
            .no-print {
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
    <h1>Planejamento Anual - <?php echo htmlspecialchars($primeiroRegistro['ano']); ?></h1>
    <p>Universo do Saber - <a href="https://www.portaluniversodosaber.com.br" target="_blank">www.portaluniversodosaber.com.br</a></p>
</div>
        
        <!-- Informações básicas (apenas do primeiro registro) -->
        <div class="info-section">
            <h2>Informações Gerais</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Escola:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['escola']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Professor:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['professor']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Curso:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['curso']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Ano:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['ano']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Componente Curricular:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['componente_curricular']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Aulas Semanais:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['numero_aulas_semanais']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Tipo de Planejamento:</strong>
                    <span><?php echo htmlspecialchars($primeiroRegistro['tipo']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Objetivos (apenas do primeiro registro) -->
        <div class="content-section">
            <h2>Objetivos</h2>
            <div class="info-item">
                <strong>Objetivo Geral:</strong>
                <p><?php echo nl2br(htmlspecialchars($primeiroRegistro['objetivo_geral'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Objetivos Específicos:</strong>
                <p><?php echo nl2br(htmlspecialchars($primeiroRegistro['objetivo_especifico'])); ?></p>
            </div>
        </div>
        
        <!-- Projetos Integradores (apenas do primeiro registro) -->
        <div class="content-section">
            <h2>Projetos Integradores</h2>
            <div class="info-item">
                <p><?php echo nl2br(htmlspecialchars($primeiroRegistro['projetos_integrador'])); ?></p>
            </div>
        </div>
        
        <!-- Conteúdos (todos os registros) -->
        <div class="content-section">
            <h2>Conteúdos e Habilidades</h2>
            <?php foreach ($planejamentos as $planejamento): ?>
                <div class="content-item">
                    <div class="periodo-info">
                        <?php echo htmlspecialchars($planejamento['tipo']); ?> - 
                        <?php echo htmlspecialchars($planejamento['sequencial']); ?>
                    </div>
                    <h3>Unidade Temática: <?php echo htmlspecialchars($planejamento['unidade_tematica']); ?></h3>
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
            <?php endforeach; ?>
        </div>
        
        <!-- Diagnóstico e Referências (apenas do primeiro registro) -->
        <div class="content-section">
            <h2>Diagnóstico e Referências</h2>
            <div class="info-item">
                <strong>Diagnóstico:</strong>
                <p><?php echo nl2br(htmlspecialchars($primeiroRegistro['diagnostico'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Referências:</strong>
                <p><?php echo nl2br(htmlspecialchars($primeiroRegistro['referencias'])); ?></p>
            </div>
        </div>
        
        <div class="btn-group no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
         
            <a href="planejador.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <script>
        function gerarPDF() {
            // Aqui você pode implementar a geração de PDF usando uma biblioteca como jsPDF
            // ou chamar um endpoint no servidor que gere o PDF
            alert('Funcionalidade de gerar PDF será implementada aqui.');
            // Exemplo básico:
            // window.location.href = 'gerar-pdf.php?ids=<?php echo $_GET['ids']; ?>';
        }
    </script>
</body>
</html>