<?php
require('sessao-adm-professor.php');
require('conexao.php');

// Busca valores existentes para os datalists
try {
    $sql_materias = "SELECT DISTINCT materia FROM questoes ORDER BY materia";
    $materias = $conexao->query($sql_materias)->fetchAll(PDO::FETCH_COLUMN);
    
    $sql_graus = "SELECT DISTINCT grau_escolar FROM questoes ORDER BY grau_escolar";
    $graus_escolares = $conexao->query($sql_graus)->fetchAll(PDO::FETCH_COLUMN);
    
    $sql_assuntos = "SELECT DISTINCT assunto FROM questoes ORDER BY assunto";
    $assuntos = $conexao->query($sql_assuntos)->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Erro ao buscar opções: " . $e->getMessage());
}



$id_questao = $_GET['id'];

// Busca a questão no banco de dados
try {
    $sql = "SELECT * FROM questoes WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_questao, PDO::PARAM_INT);
    $stmt->execute();
    $questao = $stmt->fetch(PDO::FETCH_ASSOC);

   
} catch (PDOException $e) {
    die("Erro ao buscar questão: " . $e->getMessage());
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = empty($_POST['alternativa_5']) ? '4 Questões' : '5 Questões';
    
    // Configurações para upload de imagem
    $imagem_path = $questao['imagem']; // Mantém a imagem existente por padrão
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/questoes/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid('questao_') . '.' . $extensao;
        $destino = $uploadDir . $nomeArquivo;
        
        // Tipos de arquivo permitidos
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (in_array($_FILES['imagem']['type'], $tiposPermitidos)) {
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                // Remove a imagem antiga se existir
                if (!empty($questao['imagem']) && file_exists($questao['imagem'])) {
                    unlink($questao['imagem']);
                }
                $imagem_path = $destino;
            }
        }
    }
    
    $dados = [
        'questao' => $_POST['questao'],
        'materia' => $_POST['materia'],
        'assunto' => $_POST['assunto'],
        'grau_escolar' => $_POST['grau_escolar'],
        'alternativa_A' => $_POST['alternativa_A'],
        'alternativa_B' => $_POST['alternativa_B'],
        'alternativa_C' => $_POST['alternativa_C'],
        'alternativa_D' => $_POST['alternativa_D'],
        'alternativa_E' => $_POST['alternativa_E'],
        'resposta' => $_POST['resposta'],
        'tipo' => $tipo,
        'status' => $_POST['status'],
        'data' => strtotime($_POST['data']),
        'imagem' => $imagem_path,
        'id' => $id_questao
    ];

    try {
        $sql = "UPDATE questoes SET 
                questao = :questao,
                materia = :materia,
                assunto = :assunto,
                grau_escolar = :grau_escolar,
                alternativa_A = :alternativa_A,
                alternativa_B = :alternativa_B,
                alternativa_C = :alternativa_C,
                alternativa_D = :alternativa_D,
                alternativa_E = :alternativa_E,
                resposta = :resposta,
                tipo = :tipo,
                status = :status,
                data = :data,
                imagem = :imagem
                WHERE id = :id";
        
        $stmt = $conexao->prepare($sql);
        $stmt->execute($dados);
        
        $queryString = $_SERVER['QUERY_STRING'];
parse_str($queryString, $params);
$params['editado'] = 1;
$newQueryString = http_build_query($params);

header("Location: {$_SERVER['PHP_SELF']}?$newQueryString");

        exit();
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar questão: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Questão - Universo do Saber</title>
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
        
        .success-message {
    color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
    padding: 10px 15px;
    border-radius: 4px;
    border-left: 4px solid #28a745;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}


        body {
            background-color: var(--light-gray);
            min-height: 100vh;
            padding: 20px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-gray);
        }
        .form-group input[type="text"],
    .form-group textarea,
    .form-group input[list],
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--medium-gray);
        border-radius: 4px;
        font-size: 1rem;
    }
        .form-group textarea {
            min-height: 100px;
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
        .error-message {
            color: #dc3545;
            margin-top: 5px;
            font-size: 0.9rem;
        }
        
        .imagem-questao {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
        }
        
        .imagem-container {
            margin-bottom: 20px;
        }
        
        .remove-imagem {
            color: #dc3545;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            
            <?php if (isset($_GET['editado']) && $_GET['editado'] == 1): ?>
    <div class="success-message">
        <i class="fas fa-check-circle"></i>
        <span>Informações atualizadas com sucesso!</span>
    </div>
<?php endif; ?>


            <div class="page-title">
                <h1>Editar Questão</h1>
            </div>
            <a href="pesquisar-questoes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="error-message"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="materia">Matéria</label>
                        <input list="materias" id="materia" name="materia" required value="<?php echo htmlspecialchars($questao['materia']); ?>">
                        <datalist id="materias">
                            <?php foreach ($materias as $m): ?>
                                <option value="<?php echo htmlspecialchars($m); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="assunto">Assunto</label>
                        <input list="assuntos" id="assunto" name="assunto" required value="<?php echo htmlspecialchars($questao['assunto']); ?>">
                        <datalist id="assuntos">
                            <?php foreach ($assuntos as $a): ?>
                                <option value="<?php echo htmlspecialchars($a); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
            </div>
            
                        <div class="form-row">
             <div class="form-group">
                <label for="questao">Id</label>
                <input type='text' value="<?php echo htmlspecialchars($questao['id']); ?>">
            </div>
            
            </div>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="grau_escolar">Nível de Ensino</label>
                        <input list="grausEscolares" id="grau_escolar" name="grau_escolar" required value="<?php echo htmlspecialchars($questao['grau_escolar']); ?>">
                        <datalist id="grausEscolares">
                            <?php foreach ($graus_escolares as $g): ?>
                                <option value="<?php echo htmlspecialchars($g); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="resposta">Resposta Correta (Nº da alternativa)</label>
                        <input type="text" id="resposta" name="resposta"  value="<?php echo htmlspecialchars($questao['resposta']); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="ativo" <?php echo $questao['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inativo" <?php echo $questao['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="questao">Questão</label>
                <textarea id="questao" name="questao" required><?php echo htmlspecialchars($questao['questao']); ?></textarea>
            </div>
            
            <!-- Campo para upload de imagem -->
            <div class="form-group">
                <label for="imagem">Imagem da Questão</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">
                
                <?php if (!empty($questao['imagem'])): ?>
                    <div class="imagem-container">
                        <p>Imagem atual:</p>
                        <img src="<?php echo htmlspecialchars($questao['imagem']); ?>" alt="Imagem da questão" class="imagem-questao">
                        <br>

                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Alternativas</label>
                <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                    <?php
                    $letras = ['A', 'B', 'C', 'D', 'E']; // Letras para as alternativas
                    foreach ($letras as $i => $letra): ?>
                        <div>
                            <input 
                                type="text" 
                                name="alternativa_<?php echo $letra; ?>" 
                                placeholder="Alternativa <?php echo $letra; ?>" 
                                value="<?php echo htmlspecialchars($questao['alternativa_' . $letra] ?? ''); ?>" 
                                <?php echo $i < 4 ? 'required' : ''; ?>
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <script>
        // Adiciona lógica para remoção de imagem
        document.querySelector('input[name="remover_imagem"]')?.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('imagem').disabled = true;
            } else {
                document.getElementById('imagem').disabled = false;
            }
        });
        
         // Pré-visualização da imagem
    document.getElementById('imagem').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Remove qualquer pré-visualização existente
                const oldPreview = document.querySelector('.imagem-preview');
                if (oldPreview) {
                    oldPreview.remove();
                }
                
                // Cria novo container de pré-visualização
                const previewContainer = document.createElement('div');
                previewContainer.className = 'imagem-container imagem-preview';
                previewContainer.innerHTML = `
                    <p>Nova imagem selecionada:</p>
                    <img src="${e.target.result}" alt="Pré-visualização" class="imagem-questao">
                `;
                
                // Insere após o campo de upload
                document.querySelector('input[name="imagem"]').parentNode.appendChild(previewContainer);
                
                // Desmarca a opção de remover imagem se estiver marcada
                const removeCheckbox = document.querySelector('input[name="remover_imagem"]');
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            };
            
            reader.readAsDataURL(file);
        }
    });

    // Adiciona lógica para remoção de imagem
    document.querySelector('input[name="remover_imagem"]')?.addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('imagem').disabled = true;
            // Remove a pré-visualização se existir
            const preview = document.querySelector('.imagem-preview');
            if (preview) {
                preview.remove();
            }
        } else {
            document.getElementById('imagem').disabled = false;
        }
    });
    </script>
</body>
</html>