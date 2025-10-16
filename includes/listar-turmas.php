<?php
session_start();
require(__DIR__ . '/../conexao.php');

$login = $_SESSION['login'];
$filtro_nome = $_GET['nome'] ?? '';

$sql = "SELECT * FROM turmas WHERE login = :login";
$params = [ ':login' => $login ];

if (!empty($filtro_nome)) {
    $sql .= " AND nome LIKE :nome";
    $params[':nome'] = '%' . $filtro_nome . '%';
}
$sql .= " ORDER BY id DESC";
$stmt = $conexao->prepare($sql);
$stmt->execute($params);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_registros = count($turmas);

?>
<table id="tabela-turmas" data-total="<?php echo $total_registros ?? 0; ?>">
    <thead>
        <tr>
            <!--<th class="checkbox-header">
                <input type="checkbox" id="selectAll">
            </th>-->
            <th>ID</th>
            <th>Nome da Turma</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($turmas) > 0): ?>
            <?php foreach($turmas as $turma): ?>
               <tr data-id="<?= $turma['id']; ?>">
					<!--<td><input type="checkbox" class="checkbox-item" value="<//?= $turma['id'] ?>"></td>-->
					<td><?= $turma['id'] ?></td>
					<td class="col-nome"><?= htmlspecialchars($turma['nome']) ?></td>
					<td>
						<button class="btn-action btn-view" onclick="visualizarTurma(<?= $turma['id']; ?>)">
							<i class="fas fa-eye"></i>
						</button>
						<button class="btn-action btn-edit" onclick="editarTurma(<?= $turma['id']; ?>)">
							<i class="fas fa-edit"></i>
						</button>
						<button class="btn-action btn-delete" onclick="abrirConfirmacaoExcluir(<?= $turma['id']; ?>)">
							<i class="fas fa-trash-alt"></i>
						</button>
					</td>
				</tr>

            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhuma turma encontrada.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
