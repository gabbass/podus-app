<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../conexao.php';

$idProvaOnline = $_GET['id_prova'] ?? null;
$idAluno       = $_GET['id_aluno'] ?? null;

if (!$idProvaOnline || !$idAluno) {
    echo json_encode(['erro' => 'Parâmetros ausentes']);
    exit;
}

// Buscar matrícula do aluno
$stmt = $conexao->prepare("SELECT matricula FROM login WHERE id = ?");
$stmt->execute([$idAluno]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);
$matricula = $aluno['matricula'] ?? '';

if ($matricula === '') {
    echo json_encode(['erro' => 'Aluno não encontrado']);
    exit;
}

// Buscar tentativa mais alta preenchida
$stmt = $conexao->prepare("
    SELECT 
        MAX(CASE WHEN resposta_tenta3 IS NOT NULL AND resposta_tenta3 != '' THEN 3
                 WHEN resposta_tenta2 IS NOT NULL AND resposta_tenta2 != '' THEN 2
                 WHEN resposta_tenta1 IS NOT NULL AND resposta_tenta1 != '' THEN 1
                 ELSE 0 END) as maiorTentativa,
        COUNT(*) as totalRespostas
    FROM respostas_alunos
    WHERE id_provas_online = ? AND id_matricula = ?
");
$stmt->execute([$idProvaOnline, $matricula]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$maiorTentativa = intval($row['maiorTentativa'] ?? 0);

// Fallback: verifica se ao menos o campo 'resposta' foi preenchido
if ($maiorTentativa === 0) {
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as temResposta
        FROM respostas_alunos
        WHERE id_provas_online = ? AND id_matricula = ? AND resposta IS NOT NULL AND resposta != ''
    ");
    $stmt->execute([$idProvaOnline, $matricula]);
    $temResposta = $stmt->fetchColumn();

    if ($temResposta > 0) {
        echo json_encode(['maiorTentativa' => 0]); // usa 'resposta' + 'gabarito_original'
        exit;
    }
}

echo json_encode(['maiorTentativa' => $maiorTentativa]);
