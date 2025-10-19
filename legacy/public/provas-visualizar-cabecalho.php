<?php
require_once '../conexao.php';

$idProva = $_GET['id'] ?? null;
$idAluno = $_GET['id_aluno'] ?? null;
$de = $_GET['de'] ?? '';

if (!$idProva) {
    echo "<p class='alert alert-warning'>ID da prova não informado.</p>";
    return;
}

// ─────────────────────────────────────────────────────────────
// Buscar dados da prova
$stmt = $pdo->prepare("SELECT * FROM provas WHERE id = ?");
$stmt->execute([$idProva]);
$prova = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prova) {
    echo "<p class='alert alert-warning'>Prova não encontrada.</p>";
    return;
}

// ─────────────────────────────────────────────────────────────
// Buscar nome do professor
$nomeProfessor = '';
if (!empty($prova['login'])) {
    $stmt = $pdo->prepare("SELECT nome FROM login WHERE login = ?");
    $stmt->execute([$prova['login']]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeProfessor = $prof['nome'] ?? '';
}

// ─────────────────────────────────────────────────────────────
// Buscar nome da turma
$nomeTurma = '';
if (!empty($prova['turma'])) {
    $stmt = $pdo->prepare("SELECT nome FROM turmas WHERE id = ?");
    $stmt->execute([$prova['turma']]);
    $turma = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeTurma = $turma['nome'] ?? '';
}

// ─────────────────────────────────────────────────────────────
// Buscar dados do aluno (se necessário)
$nomeAluno = '';
$matricula = '';
$nota = '';

if ($de === 'notas' && $idAluno) {
    $stmt = $pdo->prepare("SELECT nome, matricula FROM login WHERE id = ?");
    $stmt->execute([$idAluno]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeAluno = $aluno['nome'] ?? '';
    $matricula = $aluno['matricula'] ?? '';

    // nota está salva diretamente na tabela provas
    $nota = $prova['nota'] ?? '';
}
?>

<!-- Cabeçalho de exibição -->
<div class="row mb-4">
  <div class="col-md-6">
    <p><strong>Matéria:</strong> <?= htmlspecialchars($prova['materia']) ?></p>
    <p><strong>Data:</strong> <?= date('d/m/Y', intval($prova['data'])) ?></p>
    <p><strong>Turma:</strong> <?= htmlspecialchars($nomeTurma) ?></p>
    <p><strong>Professor:</strong> <?= htmlspecialchars($nomeProfessor) ?></p>
  </div>

  <?php if ($de === 'notas'): ?>
  <div class="col-md-6">
    <p><strong>Aluno:</strong> <?= htmlspecialchars($nomeAluno) ?></p>
    <p><strong>Matrícula:</strong> <?= htmlspecialchars($matricula) ?></p>
    <p><strong>Nota:</strong> <?= htmlspecialchars($nota) ?></p>
  </div>
  <?php endif; ?>
</div>
