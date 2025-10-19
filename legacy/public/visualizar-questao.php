<?php
require('sessao-adm-professor.php');
require('conexao.php');

$id_questao = $_GET['id'];

// Busca a questão no banco de dados
try {
    $sql = "SELECT * FROM questoes WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_questao, PDO::PARAM_INT);
    $stmt->execute();
    $questao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$questao) {
        header("Location: questoes");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao buscar questão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Questão - Universo do Saber</title>
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
        
        .imagem-questao {
            max-width: 100%;
            max-height: 400px;
            margin-top: 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
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
        .info-group {
            margin-bottom: 20px;
        }
        .info-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-gray);
        }
        .info-group .info-value {
            padding: 10px;
            background-color: var(--light-gray);
            border-radius: 4px;
            border: 1px solid var(--medium-gray);
            min-height: 40px;
        }
        .info-group textarea.info-value {
            min-height: 100px;
            width: 100%;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-col {
            flex: 1;
        }
        .alternativa {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: var(--light-gray);
            border-radius: 4px;
            border: 1px solid var(--medium-gray);
        }
        .alternativa.correta {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 4px solid #28a745;
        }
        .alternativa-numero {
            font-weight: bold;
            color: var(--dark-blue);
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Visualizar Questão</h1>
                <p>Detalhes completos da questão</p>
            </div>
            <div>
                <a href="rota-visualizar-questoes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="criar-editar-questao.php?id=<?php echo urlencode($questao['id']); ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="info-group">
                    <label>Id</label>
                    <div class="info-value"><?php echo htmlspecialchars($questao['id']); ?></div>
                </div>
            </div>
            
            <div class="form-col">
                <div class="info-group">
                    <label>Matéria</label>
                    <div class="info-value"><?php echo htmlspecialchars($questao['materia']); ?></div>
                </div>
            </div>
            <div class="form-col">
                <div class="info-group">
                    <label>Assunto</label>
                    <div class="info-value"><?php echo htmlspecialchars($questao['assunto']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="info-group">
                    <label>Nível de Ensino</label>
                    <div class="info-value"><?php echo htmlspecialchars($questao['grau_escolar']); ?></div>
                </div>
            </div>
            <div class="form-col">
                <div class="info-group">
                    <label>Tipo</label>
                    <div class="info-value"><?php echo htmlspecialchars($questao['tipo']); ?></div>
                </div>
            </div>
            <div class="form-col">
                <div class="info-group">
                    <label>Status</label>
                    <div class="info-value">
                        <span style="color: <?php echo $questao['status'] == 'ativo' ? '#28a745' : '#dc3545'; ?>;">
                            <?php echo ucfirst(htmlspecialchars($questao['status'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
       <div class="info-group">
    <label>Questão</label>
    <textarea class="info-value" readonly><?php echo htmlspecialchars($questao['questao']); ?></textarea>
</div>

<div class="info-group">
    <label>Resposta</label>
    <textarea class="info-value" readonly><?php echo htmlspecialchars($questao['resposta']); ?></textarea>
</div>

<?php if (!empty($questao['imagem'])): ?>
    <div class="info-group">
        <label>Imagem da Questão</label>
        <div class="info-value">
            <img src="<?php echo htmlspecialchars($questao['imagem']); ?>" alt="Imagem da questão" class="imagem-questao">
        </div>
    </div>
<?php endif; ?>
        
        <div class="info-group">
            <label>Alternativas</label>
            <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                <?php
                $letras = ['A', 'B', 'C', 'D', 'E'];
                foreach ($letras as $letra): 
                    if (!empty($questao['alternativa_' . $letra])): 
                        $is_correta = ($questao['resposta'] == $letra);
                ?>
                        <div class="alternativa <?php echo $is_correta ? 'correta' : ''; ?>">
                            <span class="alternativa-numero"><?php echo $letra; ?>.</span>
                            <span><?php echo htmlspecialchars($questao['alternativa_' . $letra]); ?></span>
                            <?php if ($is_correta): ?>
                                <span style="margin-left: auto; color: #28a745;">
                                    <i class="fas fa-check-circle"></i> Resposta Correta
                                </span>
                            <?php endif; ?>
                        </div>
                <?php 
                    endif;
                endforeach; ?>
            </div>
        </div>
        

        <div class="info-group">
            <label>Data de Cadastro / Atualização</label>
            <div class="info-value"><?php echo date('d/m/Y H:i', ($questao['data'])); ?></div>
        </div>
        

    </div>

    <script>
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.')) {
                window.location.href = 'excluir-questao?id=' + id;
            }
        }
    </script>
</body>
</html>
