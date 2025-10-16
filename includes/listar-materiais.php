<?php
require_once __DIR__ . '/../sessao-professor.php';
require_once __DIR__ . '/../conexao.php';

$login_prof = $_SESSION['login'] ?? null;
if (!$login_prof) {
    echo '<tr><td colspan="7">Usuário não autenticado.</td></tr>';
    exit;
}

// Filtros via GET
$filtro_descricao = trim($_GET['descricao'] ?? '');
$filtro_turma = trim($_GET['turma'] ?? '');
$filtro_materia = trim($_GET['materia'] ?? '');

// Monta SQL dinâmico
$sql = "SELECT * FROM materiais_pedagogicos WHERE login_professor = ?";
$params = [$login_prof];

if ($filtro_descricao !== '') {
    $sql .= " AND descricao LIKE ?";
    $params[] = "%$filtro_descricao%";
}
if ($filtro_turma !== '') {
    $sql .= " AND turma = ?";
    $params[] = $filtro_turma;
}
if ($filtro_materia !== '') {
    $sql .= " AND materia = ?";
    $params[] = $filtro_materia;
}

$sql .= " ORDER BY data_envio DESC";

$stmt = $conexao->prepare($sql);
$stmt->execute($params);

$materiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = is_array($materiais) ? count($materiais) : 0;
?>

<table class="tabela-materiais" data-total="<?php echo $total; ?>">
    <thead>
        <tr>
            <th>ID</th>
            <th>Descrição</th>
            <th>Turma</th>
            <th>Matéria</th>
            <th>Enviado em</th>
            <th>Arquivo</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($materiais)): ?>
        <tr>
            <td colspan="7">Nenhum material enviado ainda.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($materiais as $material): ?>
            <tr data-id="<?php echo $material['id']; ?>">
                <td><?php echo $material['id']; ?></td>
                <td class="col-descricao"><?php echo htmlspecialchars($material['descricao']); ?></td>
                <td class="col-turma"><?php echo htmlspecialchars($material['turma']); ?></td>
                <td class="col-materia"><?php echo htmlspecialchars($material['materia']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($material['data_envio'])); ?></td>
                <td class="col-arquivo">
                    <?php if (!empty($material['caminho_arquivo'])): ?>
                        <a href="<?php echo htmlspecialchars($material['caminho_arquivo']); ?>" target="_blank" download>
                            <?php echo htmlspecialchars(basename($material['caminho_arquivo'])); ?>
                        </a>
                    <?php else: ?>
                        Nenhum arquivo
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn-action btn-edit"
                        onclick="editarMaterial(
                            '<?php echo $material['id']; ?>'
                        )"
                        title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-action btn-delete"
                        onclick="abrirConfirmacaoExcluirMaterial('<?php echo $material['id']; ?>')"
                        title="Excluir">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <?php if (!empty($material['caminho_arquivo'])): ?>
                        <a href="<?php echo htmlspecialchars($material['caminho_arquivo']); ?>"
                           download class="btn-action btn-view"
                           title="Baixar">
                            <i class="fas fa-download"></i>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
