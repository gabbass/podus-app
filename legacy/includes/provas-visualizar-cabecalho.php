<?php
require_once __DIR__ . '/../conexao.php';

$idProva  = $_GET['id'] ?? null;
$idAluno  = $_GET['id_aluno'] ?? null;
$de       = $_GET['de'] ?? '';
$tentativa = isset($_GET['tentativa']) ? intval($_GET['tentativa']) : 0;

if (!$idProva) {
    echo "<p class='alert alert-warning'>ID da prova não informado.</p>";
    return;
}

// ─────────────────────────────────────────────
// Buscar dados da prova em provas_online
$stmt = $conexao->prepare("SELECT * FROM provas_online WHERE id = ?");
$stmt->execute([$idProva]);
$provaOnline = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$provaOnline) {
    echo "<p class='alert alert-warning'>Prova não encontrada.</p>";
    return;
}

// ─────────────────────────────────────────────
// Buscar nome do professor
$nomeProfessor = '';
if (!empty($provaOnline['login'])) {
    $stmt = $conexao->prepare("SELECT nome FROM login WHERE login = ?");
    $stmt->execute([$provaOnline['login']]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeProfessor = $prof['nome'] ?? '';
}

// ─────────────────────────────────────────────
// Preparar variáveis da prova
$materia = $provaOnline['materia'] ?? '';
$turma   = $provaOnline['turma'] ?? '';
$dataRaw = $provaOnline['data'] ?? '';
$dataProva = (is_numeric($dataRaw) && $dataRaw > 1000000000) ? date('d/m/Y', intval($dataRaw)) : '—';

// ─────────────────────────────────────────────
// Se for de=notas, buscar nome, matrícula e nota
$nomeAluno = '';
$matricula = '';
$nota      = '';

if ($de === 'notas' && $idAluno) {
    // Buscar nome e matrícula
    $stmt = $conexao->prepare("SELECT nome, matricula FROM login WHERE id = ?");
    $stmt->execute([$idAluno]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeAluno = $aluno['nome'] ?? '';
    $matricula = $aluno['matricula'] ?? '';

    // Buscar todas as notas do aluno para a turma/materia
    if ($materia && $turma && $matricula) {
        $stmt = $conexao->prepare("
            SELECT *
            FROM provas
            WHERE turma = ? AND materia = ? AND matricula = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$turma, $materia, $matricula]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        // Seleciona a nota correta conforme a tentativa
        if ($linha) {
            switch ($tentativa) {
                case 1:
                    $nota = $linha['nota_tenta1'] ?? '';
                    break;
                case 2:
                    $nota = $linha['nota_tenta2'] ?? '';
                    break;
                case 3:
                    $nota = $linha['nota_tenta3'] ?? '';
                    break;
                default:
                    $nota = $linha['nota'] ?? '';
                    break;
            }
        }
    }
}
?>

<!-- Tabela separada em 2 colunas -->
<div class="row mb-4">
  <!-- Bloco da prova -->
  <div class="col-md-6">
    <table class="table table-bordered w-100">
      <tr><th>Matéria</th><td><?= htmlspecialchars($materia) ?></td></tr>
      <tr><th>Data</th><td><?= $dataProva ?></td></tr>
      <tr><th>Turma</th><td><?= htmlspecialchars($turma) ?></td></tr>
      <tr><th>Professor</th><td><?= htmlspecialchars($nomeProfessor) ?></td></tr>
    </table>
  </div>

  <!-- Bloco do aluno -->
  <?php if ($de === 'notas'): ?>
  <div class="col-md-6">
    <table class="table table-bordered w-100">
      <tr><th>Aluno</th><td><?= htmlspecialchars($nomeAluno) ?></td></tr>
      <tr><th>Matrícula</th><td><?= htmlspecialchars($matricula) ?></td></tr>
      <tr><th>Nota</th><td><?= htmlspecialchars($nota) ?></td></tr>
    </table>
  </div>
  <?php endif; ?>
</div>
