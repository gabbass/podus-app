<?php
require('sessao-professor.php');
require('conexao.php');

$idQuestao = $_GET['id'] ?? null;
$modoEdicao = !is_null($idQuestao);
$dadosQuestao = [];
$sucesso = false; // Controle de mensagem

$materias = $conexao->query("SELECT DISTINCT materia FROM questoes ORDER BY materia")->fetchAll(PDO::FETCH_COLUMN);
$graus_escolares = $conexao->query("SELECT DISTINCT grau_escolar FROM questoes ORDER BY grau_escolar")->fetchAll(PDO::FETCH_COLUMN);
$assuntos = $conexao->query("SELECT DISTINCT assunto FROM questoes ORDER BY assunto")->fetchAll(PDO::FETCH_COLUMN);

if ($modoEdicao) {
    $stmt = $conexao->prepare("SELECT * FROM questoes WHERE id = :id");
    $stmt->execute(['id' => $idQuestao]);
    $dadosQuestao = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dadosQuestao) {
        die("Questão não encontrada.");
    }
}

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
        'status' => 'Ativo',
        'data' => strtotime(date('Y-m-d H:i:s')),
        'imagem' => $dadosQuestao['imagem'] ?? null
    ];

    try {
        if ($modoEdicao) {
            $dados['id'] = $idQuestao;
           $sql = "UPDATE questoes SET 
    questao=:questao, materia=:materia, assunto=:assunto, grau_escolar=:grau_escolar,
    alternativa_A=:alternativa_A, alternativa_B=:alternativa_B, alternativa_C=:alternativa_C,
    alternativa_D=:alternativa_D, alternativa_E=:alternativa_E, resposta=:resposta,
    tipo=:tipo, status=:status, data=:data, imagem=:imagem WHERE id=:id";
        } else {
            $sql = "INSERT INTO questoes (questao, materia, assunto, grau_escolar, alternativa_A, alternativa_B,
                    alternativa_C, alternativa_D, alternativa_E, resposta, tipo, status, data, imagem)
                    VALUES (:questao, :materia, :assunto, :grau_escolar, :alternativa_A, :alternativa_B,
                    :alternativa_C, :alternativa_D, :alternativa_E, :resposta, :tipo, :status, :data, :imagem)";
        }

        $stmt = $conexao->prepare($sql);
        $stmt->execute($dados);

        $questao_id = $modoEdicao ? $idQuestao : $conexao->lastInsertId();

        // Upload da imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $pasta_questoes = 'questoes/';
            if (!file_exists($pasta_questoes)) mkdir($pasta_questoes, 0777, true);

            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $novo_nome = "questao_{$questao_id}." . strtolower($extensao);
            $caminho_completo = $pasta_questoes . $novo_nome;

            move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_completo);

            $stmt_update = $conexao->prepare("UPDATE questoes SET imagem=:imagem WHERE id=:id");
            $stmt_update->execute(['imagem' => $caminho_completo, 'id' => $questao_id]);
        }

        $sucesso = true; // Só aqui!

        // Se preferir usar redirect (recomendado), use isso ao invés da linha acima:
        // header("Location: criar-editar-questao.php?id=$questao_id&sucesso=1");
        // exit();

    } catch (PDOException $e) {
        $erro = "Erro ao salvar questão: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modoEdicao ? 'Editar Questão' : 'Cadastrar Nova Questão'; ?></title>
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
            --green: #28a745;
            --red: #dc3545;
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
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            background-color: var(--light-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
        }
        .alternativa-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .resposta-btn {
            padding: 10px;
            border: none;
            border-radius: 4px;
            color: white;
            background-color: var(--red);
            cursor: pointer;
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .resposta-btn.ativa {
            background-color: var(--green);
        }
        .btn-file {
            background-color: var(--primary-blue);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-file:hover {
            background-color: var(--dark-blue);
        }
		  .form-group input[type="file"] {
            display: none;
        }
        .btn-file {
            background-color: var(--primary-blue);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-file:hover {
            background-color: var(--dark-blue);
        }
		
		.image-upload {
            margin-bottom: 20px;
        }
        .image-upload input[type="file"] {
            display: none;
        }
        .image-upload .btn-file {
            background-color: var(--primary-blue);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-block;
        }
        .image-upload .btn-file:hover {
            background-color: var(--dark-blue);
        }
		
		.alert-success {
			background: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
			border-radius: 4px;
			padding: 14px 20px;
			margin-bottom: 18px;
			font-size: 1rem;
			font-weight: 500;
		}
		.alert-erro {
			background: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
			border-radius: 4px;
			padding: 14px 20px;
			margin-bottom: 18px;
			font-size: 1rem;
			font-weight: 500;
		}


    </style>
</head>
<body>

    <div class="container">
	
		<?php if ((isset($_GET['sucesso']) && $_GET['sucesso'] == 1) || !empty($sucesso)): ?>
			<div class="alert-success">
				Questão salva com sucesso!
			</div>
		<?php endif; ?>
		
		<?php if (!empty($erro) || (!empty($_GET['erro']))): ?>
		
		<div class="alert-erro">
        <?php 
            if (!empty($erro)) {
                echo htmlspecialchars($erro);
            } else {
                echo htmlspecialchars($_GET['erro']);
            }
        ?>
		</div>
		<?php endif; ?>



		<div class="page-header">
            <div class="page-title">
                <h1><?php echo $modoEdicao ? 'Editar Questão' : 'Cadastrar Nova Questão'; ?></h1>
            </div>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>


        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="resposta" id="resposta" value="<?php echo htmlspecialchars($dadosQuestao['resposta'] ?? ''); ?>">

            <div class="form-group">
                <label for="materia">Matéria</label>
                <input list="materias" id="materia" name="materia" value="<?php echo htmlspecialchars($dadosQuestao['materia'] ?? ''); ?>" required>
                <datalist id="materias">
                    <?php foreach ($materias as $m): ?>
                        <option value="<?php echo htmlspecialchars($m); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label for="assunto">Assunto</label>
                <input list="assuntos" id="assunto" name="assunto" value="<?php echo htmlspecialchars($dadosQuestao['assunto'] ?? ''); ?>" required>
                <datalist id="assuntos">
                    <?php foreach ($assuntos as $a): ?>
                        <option value="<?php echo htmlspecialchars($a); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label for="grau_escolar">Nível de Ensino</label>
                <input list="grausEscolares" id="grau_escolar" name="grau_escolar" value="<?php echo htmlspecialchars($dadosQuestao['grau_escolar'] ?? ''); ?>" required>
                <datalist id="grausEscolares">
                    <?php foreach ($graus_escolares as $g): ?>
                        <option value="<?php echo htmlspecialchars($g); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label for="questao">Questão</label>
                <textarea id="questao" name="questao" required><?php echo htmlspecialchars($dadosQuestao['questao'] ?? ''); ?></textarea>
            </div>

            <?php
            $letras = ['A', 'B', 'C', 'D', 'E'];
            foreach ($letras as $letra):
                $alt = 'alternativa_' . $letra;
                $valor = htmlspecialchars($dadosQuestao[$alt] ?? '');
            ?>
            <div class="form-group">
                <label>Alternativa <?php echo $letra; ?></label>
                <div class="alternativa-group">
                    <input type="text" name="<?php echo $alt; ?>" value="<?php echo $valor; ?>">
                    <button type="button" class="resposta-btn<?php echo (($dadosQuestao['resposta'] ?? '') === $letra) ? ' ativa' : ''; ?>" data-letra="<?php echo $letra; ?>">
                        <?php echo $letra; ?> Correta
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

			 <div class="image-upload">
        <label for="imagem">Imagem (opcional)</label>
        <input type="file" name="imagem" id="imagem">
        <label for="imagem" class="btn-file">
            <i class="fas fa-upload"></i> Escolher Arquivo
        </label>
		<?php if (!empty($dadosQuestao['imagem'])): ?>
  <div style="margin-bottom:10px;">
    <img src="<?php echo htmlspecialchars($dadosQuestao['imagem']); ?>" alt="Imagem da questão" style="max-width:220px;max-height:220px;border-radius:6px;border:1px solid #e1e5eb;">
  </div>
<?php endif; ?>
    </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $modoEdicao ? 'Salvar Alterações' : 'Cadastrar Questão'; ?>
            </button>
        </form>
    </div>

    <script>
        const botoes = document.querySelectorAll('.resposta-btn');
        const inputResposta = document.getElementById('resposta');
		
		 setTimeout(function() {
        const msg = document.querySelector('.alert-success');
        if(msg) msg.style.display = 'none';
    }, 4000); // 4 segundos
		
		document.querySelector('form').addEventListener('submit', function(e){
		  if(!inputResposta.value){
			alert('Selecione qual alternativa é a correta.');
			e.preventDefault();
		  }
		});

        botoes.forEach(btn => {
            btn.addEventListener('click', () => {
                botoes.forEach(b => b.classList.remove('ativa'));
                btn.classList.add('ativa');
                inputResposta.value = btn.dataset.letra;
            });
        });
    </script>
</body>
</html>
