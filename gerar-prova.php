<?php
require('sessao-professor.php');
require('conexao.php');

// Verifica se há IDs de questões selecionadas
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    die("Nenhuma questão selecionada para gerar a prova.");
}

$ids = explode(',', $_GET['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// Consulta as questões selecionadas
$sql = "SELECT id, data, materia, assunto, questao, alternativa_A, alternativa_B, alternativa_C, alternativa_D, alternativa_E, imagem 
        FROM questoes 
        WHERE id IN ($placeholders) 
        ORDER BY FIELD(id, " . implode(',', $ids) . ")";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($ids);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao consultar questões: " . $e->getMessage());
}

// Verifica se encontrou as questões
if (count($questoes) === 0) {
    die("Nenhuma questão encontrada com os IDs fornecidos.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prova Gerada - Universo do Saber</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0057b7;
            padding-bottom: 10px;
        }
        
        .header h1 {
            color: #0057b7;
            margin-bottom: 5px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .questao {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .questao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .questao-id {
            font-weight: bold;
            color: #0057b7;
        }
        
        .questao-info {
            font-size: 0.9em;
            color: #666;
        }
        
        .enunciado {
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        
        .imagem-questao {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
            display: block;
        }
        
        .alternativas {
            margin-left: 20px;
        }
        
        .alternativa {
            margin-bottom: 8px;
        }
        
        .letra-alternativa {
            font-weight: bold;
            margin-right: 5px;
        }
        
        @media print {
            body {
                padding: 10px;
                font-size: 12pt;
            }
            
            .no-print {
                display: none;
            }
            
            .questao {
                margin-bottom: 20pt;
            }
        }
        
        .actions {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            padding: 10px 20px;
            background-color: #0057b7;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }
        
        .btn:hover {
            background-color: #003d7a;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Prova Gerada</h1>
        <p>Universo do Saber - <?php echo date('d/m/Y'); ?></p>
        <p>Total de questões: <?php echo count($questoes); ?></p>
    </div>
    
    <div class="actions no-print">
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Prova
        </button>
        <a href="javascript:history.back()" class="btn">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
    
    <?php foreach ($questoes as $index => $questao): ?>
        <div class="questao">
            <div class="questao-header">
                <span class="questao-id">Questão <?php echo $index + 1; ?></span>
                <span class="questao-info">
                    <?php echo htmlspecialchars($questao['materia']); ?> - 
                    <?php echo htmlspecialchars($questao['assunto']); ?> - 
                    ID: <?php echo $questao['id']; ?>
                </span>
            </div>
            
            <div class="enunciado">
                <?php echo nl2br(htmlspecialchars($questao['questao'])); ?>
            </div>
            
            <?php if (!empty($questao['imagem'])): ?>
                <img src="<?php echo htmlspecialchars($questao['imagem']); ?>" alt="Imagem da questão" class="imagem-questao">
            <?php endif; ?>
            
            <div class="alternativas">
                <div class="alternativa">
                    <span class="letra-alternativa">A)</span>
                    <?php echo htmlspecialchars($questao['alternativa_A']); ?>
                </div>
                
                <div class="alternativa">
                    <span class="letra-alternativa">B)</span>
                    <?php echo htmlspecialchars($questao['alternativa_B']); ?>
                </div>
                
                <div class="alternativa">
                    <span class="letra-alternativa">C)</span>
                    <?php echo htmlspecialchars($questao['alternativa_C']); ?>
                </div>
                
                <div class="alternativa">
                    <span class="letra-alternativa">D)</span>
                    <?php echo htmlspecialchars($questao['alternativa_D']); ?>
                </div>
                
                <?php if (!empty($questao['alternativa_E'])): ?>
                    <div class="alternativa">
                        <span class="letra-alternativa">E)</span>
                        <?php echo htmlspecialchars($questao['alternativa_E']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="actions no-print">
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Prova
        </button>
        <a href="javascript:history.back()" class="btn">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</body>
</html>