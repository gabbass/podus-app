<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../conexao.php';

$idProva   = $_GET['id'] ?? null;
$de        = $_GET['de'] ?? '';
$idAluno   = $_GET['id_aluno'] ?? null;
$tentativa = isset($_GET['tentativa']) ? (int) $_GET['tentativa'] : null;

if (!$idProva || !$de) {
    echo "<p>Parâmetros ausentes.</p>";
    exit;
}

// ─────────────── Buscar estrutura da prova ────────────────
$stmt = $conexao->prepare("SELECT * FROM provas_online WHERE id = ?");
$stmt->execute([$idProva]);
$prova = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prova || empty($prova['lista_quest'])) {
    echo "<p>Prova não encontrada ou sem questões atribuídas.</p>";
    exit;
}

$ids = array_map('intval', explode(',', $prova['lista_quest']));
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// ─────────────── Buscar as questões ────────────────
$stmt = $conexao->prepare("SELECT * FROM questoes WHERE id IN ($placeholders)");
$stmt->execute($ids);
$todasQuestoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$questoes = [];
foreach ($ids as $id) {
    foreach ($todasQuestoes as $q) {
        if ($q['id'] == $id) {
            $questoes[] = $q;
            break;
        }
    }
}

// ─────────────── Buscar respostas do aluno ────────────────
$respostasAluno = [];
$gabaritos = [];

if ($de === 'notas' && $idAluno) {
    $stmt = $conexao->prepare("SELECT matricula FROM login WHERE id = ?");
    $stmt->execute([$idAluno]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    $matricula = $aluno['matricula'] ?? '';

    if ($matricula !== '') {
        $stmt = $conexao->prepare("SELECT * FROM respostas_alunos WHERE id_provas_online = ? AND id_matricula = ?");
        $stmt->execute([$idProva, $matricula]);

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idQ = $r['id_questao'];

            // Detectar tentativa mais alta, se não passada
            if (is_null($tentativa)) {
                if (!empty($r['resposta_tenta3'])) $tentativa = 3;
                elseif (!empty($r['resposta_tenta2'])) $tentativa = 2;
                elseif (!empty($r['resposta_tenta1'])) $tentativa = 1;
                else $tentativa = 0;
            }

            // Obter dados corretos
            if ($tentativa === 1) {
                $respostasAluno[$idQ] = $r['resposta_tenta1'] ?? '';
                $gabaritos[$idQ]      = $r['gabarito_tenta1'] ?? '';
            } elseif ($tentativa === 2) {
                $respostasAluno[$idQ] = $r['resposta_tenta2'] ?? '';
                $gabaritos[$idQ]      = $r['gabarito_tenta2'] ?? '';
            } elseif ($tentativa === 3) {
                $respostasAluno[$idQ] = $r['resposta_tenta3'] ?? '';
                $gabaritos[$idQ]      = $r['gabarito_tenta3'] ?? '';
            } else {
                $respostasAluno[$idQ] = $r['resposta'] ?? '';
                $gabaritos[$idQ]      = $r['gabarito_original'] ?? '';
            }
        }
    }
}

// ─────────────── Exibição HTML ────────────────
ob_start();
echo "<div class='prova-corpo'>";

foreach ($questoes as $i => $q) {
    echo "<div class='questao'>";
    echo "<p><strong>" . ($i + 1) . ". </strong>" . nl2br(htmlspecialchars($q['questao'])) . "</p>";

    foreach (['A','B','C','D','E'] as $letra) {
        $texto = trim($q["alternativa_$letra"] ?? '');
        if ($texto === '') continue;

        $classe = '';
        $sufixo = '';

        $idQ = $q['id'];
        $respostaAluno = strtoupper(trim($respostasAluno[$idQ] ?? ''));
        $gabarito      = strtoupper(trim($gabaritos[$idQ] ?? $q['resposta'] ?? ''));

        if ($de === 'provas') {
            if (strtoupper(trim($q['resposta'])) === $letra) {
                $classe = 'correta';
                $sufixo = ' ✅';
            }
        }

        if ($de === 'notas') {
            if ($respostaAluno === $letra && $respostaAluno !== $gabarito) {
                $classe = 'errada';
                $sufixo = ' ❌';
            } elseif ($respostaAluno === $letra && $respostaAluno === $gabarito) {
                $classe = 'correta';
                $sufixo = ' ✅';
            } elseif ($gabarito === $letra) {
                $classe = 'gabarito';
                $sufixo = ' ✅';
            }
        }

        echo "<div class='alternativa {$classe}'><strong>($letra)</strong> " . nl2br(htmlspecialchars($texto)) . $sufixo . "</div>";
    }

    echo "</div><hr><p></p>";
}

echo "</div>";
echo ob_get_clean();
?>