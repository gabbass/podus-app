<?php
require_once __DIR__ . '/../sessao-professor.php';
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

// Sempre busque o login do professor autenticado
$login_prof = $_SESSION['login'] ?? null;
if (!$login_prof) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Usuário não autenticado.']);
    exit;
}

function resposta($sucesso, $mensagem) {
    echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem]);
    exit;
}

// AJAX de listas para select
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao'])) {
    // Listar turmas - TODO: se quiser, filtrar só pelas turmas do professor
    if ($_GET['acao'] === 'turmas') {
        $turmas = $conexao->query("SELECT id, nome FROM turmas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($turmas);
        exit;
    }

    // Listar matérias distintas dos materiais cadastrados pelo professor
    if ($_GET['acao'] === 'materias') {
        // Busca apenas matérias que já foram cadastradas em materiais desse professor
        $stmt = $conexao->prepare("SELECT DISTINCT materia FROM materiais_pedagogicos WHERE login_professor = ? AND materia IS NOT NULL AND materia <> '' ORDER BY materia");
        $stmt->execute([$login_prof]);
        $materias = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($materias);
        exit;
    }
}

// CRUD (inserção, edição, exclusão)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(false, 'Método não permitido.');
}

// Recebe dados do form
$id = isset($_POST['id-material']) ? trim($_POST['id-material']) : '';
$turma = isset($_POST['turma']) ? trim($_POST['turma']) : '';
$materia = isset($_POST['materia']) ? trim($_POST['materia']) : '';
$descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
$acao = isset($_POST['acao']) ? $_POST['acao'] : '';
$data_envio = date('Y-m-d H:i:s');

// Upload de arquivo
$arquivo_enviado = isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK;

// Pasta para salvar arquivos
$pasta_upload = __DIR__ . '/../uploads/materiais/';
if (!is_dir($pasta_upload)) {
    mkdir($pasta_upload, 0777, true);
}
$caminho_arquivo = '';

// Validação do arquivo
if ($arquivo_enviado) {
    $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
    $permitidas = ['pdf','doc','docx'];
    $tamanho_max = 5 * 1024 * 1024; // 5MB

    if (!in_array($extensao, $permitidas)) {
        resposta(false, 'Extensão de arquivo não permitida. Apenas PDF, DOC ou DOCX.');
    }
    if ($_FILES['arquivo']['size'] > $tamanho_max) {
        resposta(false, 'Arquivo muito grande (máx 5MB).');
    }

    $nome_arquivo = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['arquivo']['name']));
    $caminho_destino = $pasta_upload . $nome_arquivo;
    if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminho_destino)) {
        resposta(false, 'Erro ao salvar o arquivo.');
    }
    // Caminho relativo para o banco
    $caminho_arquivo = 'uploads/materiais/' . $nome_arquivo;
}

try {
    if ($acao === 'excluir') {
        if (empty($id)) resposta(false, 'ID do material não informado.');
        // Só exclui se for do próprio professor!
        $stmt = $conexao->prepare("DELETE FROM materiais_pedagogicos WHERE id = ? AND login_professor = ?");
        $ok = $stmt->execute([$id, $login_prof]);
        if ($ok) {
            resposta(true, 'Material excluído com sucesso!');
        } else {
            resposta(false, 'Erro ao excluir material.');
        }
    } elseif (!empty($id)) {
        // Editar material só se for do professor logado
        if (!$turma || !$materia || !$descricao) resposta(false, 'Todos os campos devem ser preenchidos.');
        if ($caminho_arquivo) {
            $stmt = $conexao->prepare("UPDATE materiais_pedagogicos SET turma=?, materia=?, descricao=?, caminho_arquivo=?, data_envio=? WHERE id=? AND login_professor=?");
            $ok = $stmt->execute([$turma, $materia, $descricao, $caminho_arquivo, $data_envio, $id, $login_prof]);
        } else {
            $stmt = $conexao->prepare("UPDATE materiais_pedagogicos SET turma=?, materia=?, descricao=?, data_envio=? WHERE id=? AND login_professor=?");
            $ok = $stmt->execute([$turma, $materia, $descricao, $data_envio, $id, $login_prof]);
        }
        if ($ok) {
            resposta(true, 'Material editado com sucesso!');
        } else {
            resposta(false, 'Erro ao editar material.');
        }
    } else {
        // Novo material
        if (!$turma || !$materia || !$descricao) resposta(false, 'Todos os campos são obrigatórios.');
        if (!$caminho_arquivo) resposta(false, 'Arquivo obrigatório.');
        $stmt = $conexao->prepare("INSERT INTO materiais_pedagogicos (login_professor, turma, materia, descricao, caminho_arquivo, data_envio) VALUES (?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$login_prof, $turma, $materia, $descricao, $caminho_arquivo, $data_envio]);
        if ($ok) {
            resposta(true, 'Material cadastrado com sucesso!');
        } else {
            resposta(false, 'Erro ao cadastrar material.');
        }
    }
} catch (Exception $e) {
    resposta(false, 'Erro no servidor: ' . $e->getMessage());
}

// Se chegou aqui, algo saiu errado
echo json_encode(['sucesso' => false, 'mensagem' => 'Requisição inválida ou rota não encontrada.']);
exit;
?>
