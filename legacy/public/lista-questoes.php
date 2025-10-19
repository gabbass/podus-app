 <table>
                <thead>
                    <tr>
                        <th class="checkbox-header" width='5%'>
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th onclick="ordenar('id')" class="<?php echo $coluna_ordenacao == 'id' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>" width='10%'>
                            ID
                        </th>
                        <th width='10%' onclick="ordenar('data')" class="<?php echo $coluna_ordenacao == 'data' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Data
                        </th>
                        <th width='15%' onclick="ordenar('materia')" class="<?php echo $coluna_ordenacao == 'materia' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Matéria
                        </th>
                        <th width='12%'onclick="ordenar('assunto')" class="<?php echo $coluna_ordenacao == 'assunto' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Assunto
                        </th>
                        <th width='15%'>Nível de Ensino</th>
                        <th width='15%'>Tipo</th>
                        <th width='22%'>Ações</th>
                    </tr>
                </thead>
               <tbody>
<?php if (count($questoes) > 0): ?>
    <?php foreach($questoes as $questao): ?>
        <tr>
            <td class="checkbox-cell">
                <input type="checkbox" class="checkbox-item" value="<?php echo $questao['id']; ?>">
            </td>
            <td><?php echo $questao['id']; ?></td>
            <td><?php echo date('d/m/Y', ($questao['data'])); ?></td>
            <td><?php echo htmlspecialchars($questao['materia']); ?></td>
            <td><?php echo htmlspecialchars($questao['assunto']); ?></td>
            <td><?php echo htmlspecialchars($questao['grau_escolar']); ?></td>
            <td><?php echo htmlspecialchars($questao['tipo']); ?></td>
            <td>
                <button class="btn-action btn-view" onclick="abrirQuestao(<?php echo $questao['id']; ?>)">
                    <i class="fas fa-eye"></i> 
                </button>
                <button class="btn-action btn-edit" onclick="editarQuestao(<?php echo $questao['id']; ?>)">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="8" style="text-align: center;">Nenhuma questão encontrada.</td>
    </tr>
<?php endif; ?>
</tbody>
            </table>