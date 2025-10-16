<?php
/*********************************************************************
 *  includes/action-notas-ap.php
 *  Camada AJAX para todas as ações referentes a “Notas”
 *  Ações implementadas:
 *    • listar    → lista notas de uma turma e matéria (JSON)
 *    • turmas    → lista turmas disponíveis (respeitando perfil)
 *    • materias  → lista matérias disponíveis de uma turma
 *
 *  Autor: Universo Correções Rápidas
 *  Data : 07-08-2025
 *********************************************************************/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_GET['acao'] ?? ($_POST['acao'] ?? null);
if (!$acao) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não especificada.']);
    exit;
}

switch ($acao) {

    // ─────────────────────────────────────────────
    // LISTAR NOTAS DE UMA TURMA E MATÉRIA (ou todas)
    // ─────────────────────────────────────────────
    case 'listar':
    $turma   = $_GET['turma']   ?? 'todas';
    $materia = $_GET['materia'] ?? 'todas';
    $login   = $_SESSION['login'] ?? '';
    $perfil  = $_SESSION['perfil'] ?? '';

    try {
        if ($perfil === 'Professor') {
            $sql = "
                SELECT 
                    p.turma,
                    p.materia,
                    p.nota,
                    po.data                  AS data_prova,
                    po.id                    AS id_provas_online,
                    p.matricula,
                    l.id                     AS id_aluno,
                    COALESCE(l.nome,'')      AS nome
                FROM provas p
                LEFT JOIN provas_online po
                       ON po.turma   = p.turma
                      AND po.materia = p.materia
                LEFT JOIN login l
                       ON l.matricula = p.matricula
                WHERE (? = 'todas' OR p.turma = ?)
                  AND (? = 'todas' OR p.materia = ?)
                  AND p.login = ?
                ORDER BY nome
            ";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([$turma, $turma, $materia, $materia, $login]);

        } else {
            // ADM vê tudo
            $sql = "
                SELECT 
                    p.turma,
                    p.materia,
                    p.nota,
                    po.data                  AS data_prova,
                    po.id                    AS id_provas_online,
                    p.matricula,
                    l.id                     AS id_aluno,
                    COALESCE(l.nome,'')      AS nome
                FROM provas p
                LEFT JOIN provas_online po
                       ON po.turma   = p.turma
                      AND po.materia = p.materia
                LEFT JOIN login l
                       ON l.matricula = p.matricula
                WHERE (? = 'todas' OR p.turma = ?)
                  AND (? = 'todas' OR p.materia = ?)
                ORDER BY nome
            ";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([$turma, $turma, $materia, $materia]);
        }

        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['sucesso' => true, 'alunos' => $linhas]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'Erro no banco: ' . $e->getMessage()
        ]);
        exit;
    }

    // ─────────────────────────────────────────────
    // LISTAR TURMAS DISPONÍVEIS
    // ─────────────────────────────────────────────
case 'turmas':
    $login  = $_SESSION['login']  ?? '';
    $perfil = $_SESSION['perfil'] ?? '';

    try {
        if ($perfil === 'Professor') {
            $stmt = $conexao->prepare('SELECT DISTINCT turma FROM provas WHERE login = ? ORDER BY turma');
            $stmt->execute([$login]);
        } else {
            $stmt = $conexao->query('SELECT DISTINCT turma FROM provas ORDER BY turma');
        }

        $turmas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['sucesso' => true, 'turmas' => $turmas]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao buscar turmas.'
        ]);
        exit;
    }


    // ─────────────────────────────────────────────
    // LISTAR MATÉRIAS DE UMA TURMA
    // ─────────────────────────────────────────────
    case 'materias':
        $turma = $_GET['turma'] ?? '';
        if (!$turma) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Turma não especificada.'
            ]);
            exit;
        }

        try {
            $stmt = $conexao->prepare('SELECT DISTINCT materia FROM provas WHERE turma = ? ORDER BY materia');
            $stmt->execute([$turma]);
            $materias = $stmt->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode(['sucesso' => true, 'materias' => $materias]);
            exit;

        } catch (PDOException $e) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao buscar matérias.'
            ]);
            exit;
        }

    // ─────────────────────────────────────────────
    // AÇÃO INVÁLIDA
    // ─────────────────────────────────────────────
    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não reconhecida.']);
        exit;
}
