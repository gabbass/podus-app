<?php
require('conexao.php');
session_start();
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    ob_clean(); // Limpa qualquer output anterior!
}

// Array de matérias pré-definidas
$materias = [
    'Português', 'Matemática', 'Ciências', 'História', 'Geografia',
    'Inglês', 'Artes', 'Educação Física', 'Filosofia', 'Sociologia'
];

// Consulta para obter as turmas da escola do professor
$query_turmas = "SELECT nome FROM turmas WHERE login = :login ORDER BY nome";
$stmt_turmas = $conexao->prepare($query_turmas);
$stmt_turmas->bindValue(':login', $_SESSION['login']);
$stmt_turmas->execute();
$turmas = $stmt_turmas->fetchAll(PDO::FETCH_ASSOC);

// Inicialize variáveis de controle
$erro = '';
$sucesso = '';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Processar o envio do material
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_material = $_POST['id_material'] ?? '';
    $turma = $_POST['turma'] ?? '';
    $materia = $_POST['materia'] ?? '';
    $descricao = $_POST['descricao'] ?? '';

    // Se vier arquivo novo, processa upload
    $novoArquivo = isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK;
    $caminhoArquivo = null;

    if ($novoArquivo) {
        $arquivo = $_FILES['arquivo'];
        $uploadDir = 'uploads/materiais/' . $_SESSION['login'] . '/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $nomeArquivo = preg_replace('/[^A-Za-z0-9._-]/', '_', $arquivo['name']);
        $caminhoArquivo = $uploadDir . time() . '_' . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
            $erro = "Erro ao fazer upload do arquivo!";
        }
    }

    if (empty($turma) || empty($materia) || empty($descricao) || ($id_material === '' && !$novoArquivo)) {
        $erro = "Preencha todos os campos e selecione um arquivo válido!";
    } elseif ($id_material) {
		// Dentro do bloco de edição, antes do UPDATE:
			if ($novoArquivo) {
				// Busca caminho do arquivo atual
				$queryAntigo = "SELECT caminho_arquivo FROM materiais_pedagogicos WHERE id = :id AND login_professor = :login";
				$stmtAntigo = $conexao->prepare($queryAntigo);
				$stmtAntigo->bindValue(':id', $id_material);
				$stmtAntigo->bindValue(':login', $_SESSION['login']);
				$stmtAntigo->execute();
				$antigo = $stmtAntigo->fetch(PDO::FETCH_ASSOC);
				if ($antigo && file_exists($antigo['caminho_arquivo'])) {
					unlink($antigo['caminho_arquivo']);
				}
			}

        // EDITAR
        $query = "UPDATE materiais_pedagogicos 
                    SET turma = :turma, materia = :materia, descricao = :descricao"
                 . ($novoArquivo ? ", caminho_arquivo = :caminho" : "") . "
                    WHERE id = :id AND login_professor = :login";
        try {
            $stmt = $conexao->prepare($query);
            $stmt->bindValue(':turma', $turma);
            $stmt->bindValue(':materia', $materia);
            $stmt->bindValue(':descricao', $descricao);
            if ($novoArquivo) $stmt->bindValue(':caminho', $caminhoArquivo);
            $stmt->bindValue(':id', $id_material);
            $stmt->bindValue(':login', $_SESSION['login']);
            $stmt->execute();

            $sucesso = "Material editado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar: " . $e->getMessage();
            if ($novoArquivo && file_exists($caminhoArquivo)) unlink($caminhoArquivo);
        }
    } else {
        // NOVO CADASTRO (fluxo antigo)
        if ($novoArquivo) {
            $query = "INSERT INTO materiais_pedagogicos 
                        (login_professor, turma, materia, descricao, caminho_arquivo, data_envio)
                        VALUES (:login, :turma, :materia, :descricao, :caminho, NOW())";
            try {
                $stmt = $conexao->prepare($query);
                $stmt->bindValue(':login', $_SESSION['login']);
                $stmt->bindValue(':turma', $turma);
                $stmt->bindValue(':materia', $materia);
                $stmt->bindValue(':descricao', $descricao);
                $stmt->bindValue(':caminho', $caminhoArquivo);
                $stmt->execute();
                $sucesso = "Material cadastrado com sucesso!";
            } catch (PDOException $e) {
                $erro = "Erro ao salvar no banco de dados: " . $e->getMessage();
                if ($novoArquivo && file_exists($caminhoArquivo)) unlink($caminhoArquivo);
            }
        }
    }

    // RESPOSTA AJAX OU NORMAL
    if ($isAjax) {
        header('Content-Type: application/json');
        if ($sucesso) {
            echo json_encode(['success' => true, 'message' => $sucesso]);
        } else {
            echo json_encode(['success' => false, 'message' => $erro]);
        }
        exit;
    }
}


// Consulta para listar os materiais já enviados
$query_materiais = "SELECT * FROM materiais_pedagogicos 
                   WHERE login_professor = :login
                   ORDER BY data_envio DESC";
$stmt_materiais = $conexao->prepare($query_materiais);
$stmt_materiais->bindValue(':login', $_SESSION['login']);
$stmt_materiais->execute();
$materiais = $stmt_materiais->fetchAll(PDO::FETCH_ASSOC);
?>
