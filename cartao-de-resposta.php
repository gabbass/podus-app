<?php
require('conexao.php');
require('sessao-professor.php');

// Verifica se existem IDs na URL
if (isset($_GET['ids'])) {
    // Converte a string de IDs separados por vírgula em um array
    $ids = explode(',', $_GET['ids']);
    
    // Sanitiza os IDs (importante para segurança)
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids); // Remove valores vazios
    
    // Busca todas as provas selecionadas agrupadas por matrícula
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Primeiro obtemos todas as matrículas distintas
        $sql_matriculas = "SELECT DISTINCT p.matricula 
                          FROM provas p 
                          WHERE p.id IN ($placeholders)";
        
        $stmt = $conexao->prepare($sql_matriculas);
        foreach ($ids as $k => $id) {
            $stmt->bindValue(($k+1), $id);
        }
        $stmt->execute();
        $matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada matrícula, buscamos as provas correspondentes
        $alunos_provas = [];
        foreach ($matriculas as $matricula_item) {
            $matricula = $matricula_item['matricula'];
            
            // Busca dados completos do aluno
            $stmt = $conexao->prepare("SELECT * FROM login WHERE matricula = ?");
            $stmt->execute([$matricula]);
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($aluno) {
                // Busca as provas deste aluno com informações completas das questões
                $sql_provas = "SELECT p.*, q.* 
                              FROM provas p
                              JOIN questoes q ON p.id_questao = q.id
                              WHERE p.id IN ($placeholders) AND p.matricula = ?";
                
                $stmt = $conexao->prepare($sql_provas);
                foreach ($ids as $k => $id) {
                    $stmt->bindValue(($k+1), $id);
                }
                $stmt->bindValue(count($ids)+1, $matricula);
                $stmt->execute();
                $provas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($provas)) {
                    // Conta o número de questões distintas
                    $questoes_ids = array_unique(array_column($provas, 'id_questao'));
                    $numero_questoes = count($questoes_ids);
                    
                    $alunos_provas[] = [
                        'matricula' => $matricula,
                        'nome' => $aluno['nome'],
                        'turma' => $aluno['turma'],
                        'escola' => $aluno['escola'],
                        'materia' => $provas[0]['materia'],
                        'numero_questoes' => $numero_questoes,
                        'provas_ids' => array_column($provas, 'id')
                    ];
                }
            }
        }
    }
    
    if (empty($alunos_provas)) {
        header('Location: provas-e-notas.php');
        exit;
    }
    
    // URL base para o QR Code
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
} else {
    header('Location: provas-e-notas.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartões Resposta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .page {
            border: 1px solid #000;
            padding: 20px;
            margin-bottom: 30px;
            page-break-after: always;
        }
        
        .instructions {
            margin-bottom: 20px;
        }
        
        .card {
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .student-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .student-info div {
            border-bottom: none;
            padding: 5px 0;
        }
        
        .student-info label {
            font-weight: bold;
            display: block;
            font-size: 12px;
        }
        
        .answer-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .column {
            width: 48%;
        }
        
        .question {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .question-number {
            width: 30px;
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
        }
        
        .options {
            display: flex;
            gap: 10px;
        }
        
        .option {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .circle {
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .option-label {
            font-size: 12px;
            margin-top: 2px;
        }
        
        .example {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        
        .example-circle {
            min-width: 20px;
            min-height: 20px;
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            border-radius: 50%;
            background-color: #000;
            flex-shrink: 0;
        }
        
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        
        .subtitle {
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .signature {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 20px;
            width: 100%;
            text-align: center;
            font-size: 12px;
        }
        
        .qr-code {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .card-container {
            position: relative;
        }
        
        .print-button {
            background-color: #0057b7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 20px auto;
            display: block;
        }
        
        @media print {
            .print-button {
                display: none !important;
            }
            .page {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php foreach ($alunos_provas as $aluno): ?>
        <!-- Instructions Page (repetido para cada aluno) -->
        <div class="page instructions">
            <h1>Instruções para a Avaliação Impressa</h1>
            <h2>Recomendações para impressão, preenchimento e digitalização das folhas de gabarito</h2>
            
            <h3>Impressão</h3>
            <ul>
                <li>Imprimir em folha de tamanho A4, papel branco, na orientação vertical;</li>
                <li>Utilizar impressão de boa qualidade, com no mínimo 300 dpi de resolução;</li>
                <li>Após gerar o arquivo em Word com os enunciados para impressão, não alterar a ordem das questões, pois isso resultará na correção incorreta da prova;</li>
            </ul>
            
            <h3>Preenchimento</h3>
            <ul>
                <li>Orientar os estudantes a preencher corretamente os círculos de marcação de resposta, conforme o exemplo de preenchimento fornecido, utilizando caneta azul ou preta;</li>
                <li>Instruir os alunos a não rasurar partes da folha de gabarito e a não escrever em espaços não destinados para isso;</li>
            </ul>
            
            <h3>Digitalização</h3>
            <ul>
                <li>Digitalizar as folhas de gabarito utilizando scanner, na configuração tons de cinza;</li>
                <li>A qualidade do escaneamento deve ser de pelo menos 300 dpi;</li>
                <li>Digitalizar as folhas de gabarito na orientação correta: vertical e de cabeça para cima;</li>
                <li>Não importar folhas de gabarito de alunos ausentes, pois o sistema de correção irá considerar como aluno presente, com todas as respostas em branco.</li>
            </ul>
        </div>
        
        <!-- Answer Card Page (um para cada aluno) -->
        <div class="page card-container">
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode($base_url . '/notas-prova.php?ids=' . implode(',', $aluno['provas_ids'])) ?>" alt="QR Code">
            </div>
            
            <div class="card">
                <div class="header">
                    <h1 class="title">Cartão Resposta</h1>
                    <p class="subtitle">Prova de <?= htmlspecialchars($aluno['materia']) ?> - Turma <?= htmlspecialchars($aluno['turma']) ?></p>
                </div>
                
                <div class="example">
                    <div class="example-circle"></div>
                    <p>Para todas as marcações neste CARTÃO RESPOSTA, preencha os círculos completamente e com nitidez utilizando caneta preta ou azul, conforme exemplo ao lado. NÃO RASURE O CARTÃO RESPOSTA, sob pena de ANULAÇÃO DA AVALIAÇÃO.</p>
                </div>
                
                <div class="student-info">
                    <div>
                        <label>Escola:</label>
                        <div><?= htmlspecialchars($aluno['escola']) ?></div>
                    </div>
                    <div>
                        <label>Matrícula:</label>
                        <div><?= htmlspecialchars($aluno['matricula']) ?></div>
                    </div>
                    <div>
                        <label>Nome:</label>
                        <div><?= htmlspecialchars($aluno['nome']) ?></div>
                    </div>
                    <div>
                        <label>Turma:</label>
                        <div><?= htmlspecialchars($aluno['turma']) ?></div>
                    </div>
                </div>
                
                <div class="answer-grid">
                    <?php
                    // Divide as questões em duas colunas
                    $metade = ceil($aluno['numero_questoes'] / 2);
                    $colunas = [
                        range(1, $metade),
                        range($metade + 1, $aluno['numero_questoes'])
                    ];
                    
                    foreach ($colunas as $index => $questoes_coluna):
                        if (!empty($questoes_coluna)):
                    ?>
                    <div class="column">
                        <h3>Respostas <?= $questoes_coluna[0] ?> - <?= end($questoes_coluna) ?></h3>
                        
                        <?php foreach ($questoes_coluna as $numero): ?>
                        <div class="question">
                            <div class="question-number"><?= str_pad($numero, 2, '0', STR_PAD_LEFT) ?></div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
                
                <div class="signature">
                    Assinatura do Aluno:
                    
                    _________________________________________
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="print-button" onclick="window.print()">Imprimir Cartões Resposta</button>

    <script>
        // Adiciona funcionalidade para marcar os círculos quando clicados
        document.querySelectorAll('.circle').forEach(circle => {
            circle.addEventListener('click', function() {
                this.style.backgroundColor = this.style.backgroundColor === 'black' ? '' : 'black';
            });
        });
    </script>
</body>
</html>