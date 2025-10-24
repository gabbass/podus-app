<?php
// portal/includes/action-provas.php

// 1) Sessão e conexões
require_once __DIR__ . '/../sessao-adm-professor.php';
require_once __DIR__ . '/../conexao.php';        // define $conexao
require_once __DIR__ . '/../conexao-bncc.php';   // define $conexao_bncc

$pdo     = $conexao;
$pdoBNCC = $conexao_bncc;
$login   = $_SESSION['login'] ?? '';
$perfil  = $_SESSION['perfil'] ?? '';

header('Content-Type: application/json; charset=utf-8');

function out(array $d, int $status = 200): void {
    http_response_code($status);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}

$acao = $_REQUEST['acao'] ?? '';

switch ($acao) {
    case 'listarTurmas':
        if ($perfil === 'Administrador') {
            $stmt = $pdo->query("SELECT id, nome FROM turmas ORDER BY nome");
        } else {
            $stmt = $pdo->prepare("SELECT id, nome FROM turmas WHERE login = ? ORDER BY nome");
            $stmt->execute([$login]);
        }
        $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        out(['sucesso' => true, 'turmas' => $turmas]);
        break;

   case 'listarQuestoes':
    // coleta parâmetro de matéria, se existir
    $filtroMat = trim($_GET['materia'] ?? '');
    
    if ($perfil === 'Administrador') {
        $sql = "SELECT id, questao, materia FROM questoes WHERE 1";
        $bind = [];
    } else {
        $sql = "SELECT id, questao, materia
                  FROM questoes
                 WHERE (isRestrito IS NULL OR isRestrito = 0)
                    OR (id_professor = :prof)";
        $bind = [':prof' => $_SESSION['id']];
    }

    // se o filtro de matéria veio preenchido, adiciona condição
    if ($filtroMat !== '') {
        $sql .= " AND materia = :mat";
        $bind[':mat'] = $filtroMat;
    }

    $sql .= " ORDER BY id DESC";
    $stmtQ = $pdo->prepare($sql);
    foreach ($bind as $k => $v) {
        $stmtQ->bindValue($k, $v);
    }
    $stmtQ->execute();
    $questoes = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    out(['sucesso' => true, 'questoes' => $questoes]);
    break;



    case 'listarMaterias':
        $stmtM = $pdoBNCC->query("SELECT DISTINCT nome FROM bncc_componentes ORDER BY nome");
        $materias = $stmtM->fetchAll(PDO::FETCH_COLUMN);
        out(['sucesso' => true, 'materias' => $materias]);
        break;

    case 'listar':
        $stmt = $pdo->prepare("
            SELECT id, turma, materia, escola, lista_quest
            FROM provas_online
            WHERE login = ?
            ORDER BY id DESC
        ");
        $stmt->execute([$login]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        out(['sucesso' => true, 'provas' => $rows]);
        break;

    case 'buscar':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            out(['sucesso' => false, 'msg' => 'ID ausente'], 422);
        }
        $stmt = $pdo->prepare("
            SELECT * 
            FROM provas_online
            WHERE id = ? AND login = ?
        ");
        $stmt->execute([$id, $login]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            out(['sucesso' => false, 'msg' => 'Não encontrado'], 404);
        }
        out(['sucesso' => true, 'dado' => $p]);
        break;

    case 'criar':
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            out(['sucesso' => false, 'msg' => 'Método errado'], 405);
        }
        $id      = $_POST['id'] ?? null;
        $turma   = $_POST['turma'] ?? '';
        $escola  = $_POST['escola'] ?? '';
        $materia = $_POST['materia'] ?? '';
        $lista   = $_POST['lista_quest'] ?? [];

        if (!$turma || !$materia || count($lista) === 0) {
            out(['sucesso' => false, 'msg' => 'Dados incompletos'], 422);
        }

        if ($acao === 'criar') {
            $stmt = $pdo->prepare("
                INSERT INTO provas_online
                (data, turma, materia, login, escola, lista_quest)
                VALUES (CURDATE(), ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$turma, $materia, $login, $escola, implode(',', $lista)]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE provas_online
                SET turma = ?, materia = ?, escola = ?, lista_quest = ?
                WHERE id = ? AND login = ?
            ");
            $stmt->execute([$turma, $materia, $escola, implode(',', $lista), $id, $login]);
        }

        out(['sucesso' => true]);
        break;

    case 'excluir':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            out(['sucesso' => false, 'msg' => 'Método errado'], 405);
        }
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            out(['sucesso' => false, 'msg' => 'ID ausente'], 422);
        }
        $stmt = $pdo->prepare("
            DELETE FROM provas_online
            WHERE id = ? AND login = ?
        ");
        $stmt->execute([$id, $login]);
        out(['sucesso' => true]);
        break;

    default:
        out(['sucesso' => false, 'msg' => 'Ação inválida'], 400);
        break;
}
