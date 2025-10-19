 <table>
                <thead>
                    <tr>
                        <!--<th class="checkbox-header" width='5%'>
                            <input type="checkbox" id="selectAll">
                        </th>-->
                        <th onclick="ordenar('matricula')" class="<?php echo $coluna_ordenacao == 'matricula' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Matrícula
                        </th>
                        <th onclick="ordenar('nome')" class="<?php echo $coluna_ordenacao == 'nome' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>" width='25%'>
                            Nome
                        </th>
                        <th onclick="ordenar('turma')" class="<?php echo $coluna_ordenacao == 'turma' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>" width='15%'>
                            Turma
                        </th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($alunos) > 0): ?>
                        <?php foreach($alunos as $aluno): ?>
                            <tr data-id="<?= $aluno['id']; ?>">
                                <!--<td class="checkbox-cell">
                                    <input type="checkbox" class="checkbox-item" value="<//?php echo $aluno['id']; ?>">
                                </td>-->
                                <td><?php echo htmlspecialchars($aluno['matricula'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['turma'] ?? 'N/A'); ?></td>
                                
                                <td class="<?php echo ($aluno['status'] ?? 1) == 1 ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($aluno['status'] ?? 1) == 1 ? 'Ativo' : 'Inativo'; ?>
                                </td>
                                <td>
                                    <button class="btn-action btn-view" onclick="visualizarAluno(<?php echo $aluno['id']; ?>)">
                                        <i class="fas fa-eye"></i> 
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editarAluno(<?php echo $aluno['id']; ?>)">
                                        <i class="fas fa-edit"></i> 
                                    </button>
                                    <button class="btn-action btn-delete" onclick="confirmarExclusao(<?php echo $aluno['id']; ?>)">
                                        <i class="fas fa-trash-alt"></i> 
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Nenhum aluno encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>