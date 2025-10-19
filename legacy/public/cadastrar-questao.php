<?php

require('sessao-adm.php');
require('conexao.php');


if ($_GET['cadastrado'] == 1) {
    echo "<div style='background-color:#08FF08; color:#000; padding:10px; border-radius:5px; font-weight:bold;'>Cadastro realizado com sucesso!</div>";
    echo "<script>
        setTimeout(function() {
            location.assign('cadastrar-questao');
        }, 4000); 
    </script>";
}



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

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = empty($_POST['alternativa_E']) ? '4 Questões' : '5 Questões';
    
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
        'data' => strtotime(date('Y-m-d H:i:s')),
        'imagem' => null // Inicializa como null
    ];

    try {
        // Primeiro, insere a questão sem a imagem para obter o ID
        $sql = "INSERT INTO questoes (
                questao, materia, assunto, grau_escolar, 
                alternativa_A, alternativa_B, alternativa_C, 
                alternativa_D, alternativa_E, resposta, 
                tipo, status, data, imagem
                ) VALUES (
                :questao, :materia, :assunto, :grau_escolar, 
                :alternativa_A, :alternativa_B, :alternativa_C, 
                :alternativa_D, :alternativa_E, :resposta, 
                :tipo, :status, :data, :imagem)";
        
        $stmt = $conexao->prepare($sql);
        $stmt->execute($dados);
        
        // Obtém o ID da questão recém-inserida
        $questao_id = $conexao->lastInsertId();
        
        // Processa o upload da imagem se existir
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $pasta_questoes = 'questoes/';
            
            // Cria a pasta se não existir
            if (!file_exists($pasta_questoes)) {
                mkdir($pasta_questoes, 0777, true);
            }
            
            // Obtém informações do arquivo
            $nome_arquivo = $_FILES['imagem']['name'];
            $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
            $novo_nome = "questao_{$questao_id}." . strtolower($extensao);
            $caminho_completo = $pasta_questoes . $novo_nome;
            
            // Move o arquivo para a pasta de destino
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_completo)) {
                // Atualiza o registro com o caminho da imagem
                $sql_update = "UPDATE questoes SET imagem = :imagem WHERE id = :id";
                $stmt_update = $conexao->prepare($sql_update);
                $stmt_update->bindParam(':imagem', $caminho_completo);
                $stmt_update->bindParam(':id', $questao_id);
                $stmt_update->execute();
            } else {
                $erro = "Erro ao fazer upload da imagem.";
            }
        }
        
$queryString = $_SERVER['QUERY_STRING']; // Obtém a query string atual
parse_str($queryString, $params); // Converte a string em um array associativo
$params['cadastrado'] = 1; // Adiciona ou altera o parâmetro 'cadastrado'
$newQueryString = http_build_query($params); // Reconstrói a query string

header("Location: {$_SERVER['PHP_SELF']}?$newQueryString"); // Redireciona para a mesma página com os novos parâmetros
exit(); // Garante que o script pare aqui

    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar questão: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Questão - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  
</head>
<body>
    <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
	
	 <div class="main-content" id="main-content">
			<?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>
    <div class="container">
				<div class="page-header">
					<div class="page-title">
						<h1>Cadastrar Nova Questão</h1>
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
                        <input list="materias" id="materia" name="materia" required>
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
                        <input list="assuntos" id="assunto" name="assunto" required>
                        <datalist id="assuntos">
                            <?php foreach ($assuntos as $a): ?>
                                <option value="<?php echo htmlspecialchars($a); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="grau_escolar">Nível de Ensino</label>
                        <input list="grausEscolares" id="grau_escolar" name="grau_escolar" required>
                        <datalist id="grausEscolares">
                            <?php foreach ($graus_escolares as $g): ?>
                                <option value="<?php echo htmlspecialchars($g); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="resposta">Resposta Correta</label>
                        <input type="text" id="resposta" name="resposta" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="questao">Questão</label>
                <textarea id="questao" name="questao" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagem">Imagem (Opcional)</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">
                <img id="preview" class="preview-imagem" src="#" alt="Pré-visualização da imagem">
            </div>
            
            <div class="form-group">
                <label>Alternativas</label>
                <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                    <?php
                    $letras = ['A', 'B', 'C', 'D', 'E'];
                    for ($i = 0; $i < count($letras); $i++): ?>
                        <div>
                            <input 
                                type="text" 
                                name="alternativa_<?php echo $letras[$i]; ?>" 
                                placeholder="Alternativa <?php echo $letras[$i]; ?>" 
                                <?php echo $i < 4 ? 'required' : ''; ?>
                            >
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cadastrar Questão
                </button>
            </div>
        </form>
    </div>
        </div>
		
			<script src="pusaber.js"></script>
 </body>
</html>