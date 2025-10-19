<?php
require('sessao-professor.php');
require('conexao.php');

header('Content-Type: application/json; charset=utf-8');

/* ------------------------------------------------------------------ */
/* 1. Validação básica dos parâmetros                                 */
/* ------------------------------------------------------------------ */
$matricula = $_GET['matricula'] ?? '';
$idprova   = $_GET['idprova']   ?? '';

if (!ctype_digit($matricula) || !ctype_digit($idprova)) {
    echo json_encode(['sucesso' => false, 'msg' => 'IDs inválidos']);
    exit;
}

/* ------------------------------------------------------------------ */
/* 2. Tenta obter a lista de questões desta prova online              */
/* ------------------------------------------------------------------ */
try {
    $stmt = $conexao->prepare("SELECT lista_quest FROM provas_online WHERE id = ?");
    $stmt->execute([$idprova]);

    $lista = $stmt->fetchColumn();
    if (!$lista) {
        echo json_encode(['sucesso' => true, 'respostas' => []]);
        exit;
    }

    /* mantém apenas IDs numéricos */
    $idsQuest = array_filter(
        array_map('trim', explode(',', $lista)),
        fn($id) => ctype_digit($id)
    );
    if (!$idsQuest) {
        echo json_encode(['sucesso' => true, 'respostas' => []]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /* 3. Carrega todas as respostas do aluno para esta prova             */
    /* ------------------------------------------------------------------ */
    $respStmt = $conexao->prepare(
        "SELECT * FROM respostas_alunos
         WHERE id_provas_online = ? AND id_matricula = ?"
    );
    $respStmt->execute([$idprova, $matricula]);

    $map = [];
    while ($row = $respStmt->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['id_questao']] = $row;
    }

    /* ------------------------------------------------------------------ */
    /* 4. Monta o retorno, mas só inclui tentativas válidas               */
    /* ------------------------------------------------------------------ */
    $resultado = [];

    $qQuest = $conexao->prepare("SELECT * FROM questoes WHERE id = ?");

    foreach ($idsQuest as $idq) {
        $qQuest->execute([$idq]);
        $questao = $qQuest->fetch(PDO::FETCH_ASSOC);

        if (!$questao) {                /* questão inexistente ⇒ ignora   */
            continue;
        }

        $linha      = $map[$idq] ?? [];
        $tentativas = [];

        for ($t = 1; $t <= 3; $t++) {
            $resp = isset($linha["resposta_tenta{$t}"])
                    ? strtoupper($linha["resposta_tenta{$t}"])
                    : '';

            $gab = isset($linha["gabarito_tenta{$t}"])
                   ? strtoupper($linha["gabarito_tenta{$t}"])
                   : strtoupper($questao['resposta']);

            /* ignora tentativas totalmente vazias */
            if ($resp === '' && $gab === '') {
                continue;
            }

            $tentativas[] = [
                'tentativa'         => $t,
                'resposta'          => $resp !== '' ? $resp : null,
                'gabarito'          => $gab,
                'gabarito_original' => strtoupper($questao['resposta']),
                'correta'           => ($resp !== '' && $resp === $gab)
            ];
        }

        /* só inclui a questão se existir ao menos 1 tentativa */
        if ($tentativas) {
            $resultado[] = [
                'enunciado'  => mb_substr($questao['questao'] ?? $questao['pergunta'], 0, 250),
                'tentativas' => $tentativas
            ];
        }
    }

    echo json_encode(['sucesso' => true, 'respostas' => $resultado]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'msg' => 'Erro: ' . $e->getMessage()]);
}
