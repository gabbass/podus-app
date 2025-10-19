<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__) . '/public/conexao.php';
require_once dirname(__DIR__) . '/public/conexao-bncc.php';

$termo_pesquisa = $_GET['pesquisa'] ?? '';

// 1. Busca todos os planejamentos do usuário
$sql = "SELECT * FROM planejamento 
        WHERE login = :login
        AND (nome LIKE :pesquisa OR periodo LIKE :pesquisa)
        ORDER BY created_date DESC";

$stmt = $conexao->prepare($sql);
$stmt->bindValue(':login', $_SESSION['id'], PDO::PARAM_INT);
$stmt->bindValue(':pesquisa', '%' . $termo_pesquisa . '%', PDO::PARAM_STR);
$stmt->execute();
$planejamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 2. Busca nomes das matérias
$ids_materias = array_unique(array_filter(array_column($planejamentos, 'materia')));
$ids_materias = array_values($ids_materias); // <-- garante índices 0,1,2,...

$materias = [];
if (!empty($ids_materias)) {
    $placeholders = implode(',', array_fill(0, count($ids_materias), '?'));
    $stmt2 = $conexao->prepare("SELECT id, nome FROM materias WHERE id IN ($placeholders)");
    $stmt2->execute($ids_materias);
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $m) {
        $materias[$m['id']] = $m['nome'];
    }
}


// 3. Busca todas as linhas dos planejamentos
$linhasPorPlanejamento = [];
$componentes_ids = [];
$anosPorPlanejamento = [];
if (!empty($planejamentos)) {
    $ids_planejamentos = array_column($planejamentos, 'id');
    $placeholders = implode(',', array_fill(0, count($ids_planejamentos), '?'));
    $stmt3 = $conexao->prepare(
        "SELECT id, planejamento, ano, componenteCurricular 
         FROM planejamento_linhas 
         WHERE planejamento IN ($placeholders)"
    );
    $stmt3->execute($ids_planejamentos);
    foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $linhasPorPlanejamento[$linha['planejamento']][] = $linha;
        if (!empty($linha['componenteCurricular'])) {
            $componentes_ids[] = $linha['componenteCurricular'];
        }
        if (!empty($linha['ano'])) {
            $anosPorPlanejamento[$linha['planejamento']][] = $linha['ano'];
        }
    }
}

// 4. Busca nomes dos componentes curriculares (BNCC)
$componentes = [];
$componentes_ids = array_unique(array_filter($componentes_ids));
$componentes_ids = array_values($componentes_ids); // <-- ESSENCIAL!
if (!empty($componentes_ids)) {
    $placeholders = implode(',', array_fill(0, count($componentes_ids), '?'));
    $stmt4 = $conexao_bncc->prepare("SELECT id, nome FROM bncc_componentes WHERE id IN ($placeholders)");
    $stmt4->execute($componentes_ids);
    foreach ($stmt4->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $componentes[$c['id']] = $c['nome'];
    }
}
else {
    // Não executa nada se vazio (EVITA o erro!)
}


?>
<div id="lista-planejamentos-mensais">
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Nome</th>
      <th>Matéria</th>
      <th>Anos</th>
      <th>Componentes Curriculares</th>
      <th>Período</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody id="tbody-lista-planejamentos">
    <?php if (count($planejamentos) > 0): ?>
      <?php foreach ($planejamentos as $planejamento): ?>
        <tr>
          <td><?php echo htmlspecialchars($planejamento['id']); ?></td>
          <td><?php echo htmlspecialchars($planejamento['nome']);  ?></td>
          <td><?php echo htmlspecialchars($materias[$planejamento['materia']] ?? $planejamento['materia']); ?></td>
          <td>
            <?php
              $anos = $anosPorPlanejamento[$planejamento['id']] ?? [];
              echo $anos ? implode(', ', array_unique($anos)) : '-';
            ?>
          </td>
          <td>
            <?php
              // Exibe os nomes dos componentes curriculares usados neste planejamento
              $linhas = $linhasPorPlanejamento[$planejamento['id']] ?? [];
              $lista = [];
              foreach ($linhas as $linha) {
                $idComp = $linha['componenteCurricular'] ?? null;
                if ($idComp && !in_array($idComp, $lista)) {
                    $lista[] = $idComp;
                }
              }
              $nComp = [];
              foreach ($lista as $idComp) {
                  $nComp[] = htmlspecialchars($componentes[$idComp] ?? $idComp);
              }
              echo $nComp ? implode(', ', $nComp) : '-';
            ?>
          </td>
          <td><?php echo htmlspecialchars($planejamento['periodo']); ?></td>
          <td>
           <button class="btn-action btn-view" onclick="window.location.href='planejador-mensal-visualizar.php?id=<?php echo $planejamento['id']; ?>'">
			  <i class="fas fa-eye"></i>
			</button>

            <button class="btn-action btn-edit" onclick="editarPlanejamentoMensal(<?php echo $planejamento['id']; ?>)">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action btn-delete" data-action="editar-plano" data-id="<?= $planejamento['id'] ?>" title="Editar">
              <i class="fas fa-trash"></i>
            </button>
            <button class="btn-action btn-baixar" data-id="<?= $planejamento['id']; ?>"  onclick="gerarPlanejador(this)" title="Baixar DOCX">
              <i class="fas fa-file-pdf"></i>
            </button>
            <button class="btn-action btn-imprimir" onclick="gerarPlanejador()" title="Imprimir">
              <i class="fas fa-print"></i>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="7" style="text-align: center;">
          <?php echo $termo_pesquisa ?
            'Nenhum resultado encontrado para sua pesquisa.' :
            'Nenhum planejamento cadastrado ainda.'; ?>
        </td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
</div>