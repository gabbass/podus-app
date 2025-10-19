<?php
require_once '../conexao.php';

$idProva = $_GET['id'] ?? null;
$de = $_GET['de'] ?? '';
$idAluno = $_GET['id_aluno'] ?? null;

if (!$idProva) {
  echo "<p>Parâmetro ausente.</p>";
  exit;
}

// Mesma lógica das funções anteriores: buscarProva(), buscarQuestoes(), buscarRespostasAluno()

ob_start();

// Cabeçalho simples (prova escrita)
echo "<div class='prova-corpo'>";

foreach ($questoes as $idx => $q) {
  echo "<div class='questao'>";
  echo "<p><strong>Questão " . ($idx + 1) . "</strong></p>";
  echo "<p>" . nl2br(htmlspecialchars($q['questao'])) . "</p>";

  foreach (['A','B','C','D','E'] as $letra) {
    $texto = $q["alternativa_$letra"];
    $classe = '';
    $sufixo = '';

    if ($de === 'provas' && $q['resposta'] === $letra) {
      $classe = 'correta';
      $sufixo = ' ✅';
    }

    if ($de === 'notas') {
      $respostaAluno = $respostas[$q['id_questao']]['resposta'] ?? '';
      if ($respostaAluno === $letra && $respostaAluno !== $q['resposta']) {
        $classe = 'errada';
        $sufixo = ' ❌';
      }
      if ($respostaAluno === $letra && $respostaAluno === $q['resposta']) {
        $classe = 'correta';
        $sufixo = ' ✅';
      }
    }

    echo "<div class='alternativa {$classe}'><strong>($letra)</strong> " . nl2br(htmlspecialchars($texto)) . $sufixo . "</div>";
  }

  echo "</div>";
}

echo "</div>";

echo ob_get_clean();
