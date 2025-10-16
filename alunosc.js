 // Função para limpar os filtros
        function limparFiltros() {
            window.location.href = window.location.pathname;
        }
        
        // Função para ordenar a tabela
        function ordenar(coluna) {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            // Verifica se já está ordenando por esta coluna (inverte a direção)
            if (params.get('ordenacao') === `${coluna}_asc`) {
                params.set('ordenacao', `${coluna}_desc`);
            } else {
                params.set('ordenacao', `${coluna}_asc`);
            }
            
            window.location.href = url.pathname + '?' + params.toString();
        }
        
        // Funções para ações
        function visualizarAluno(id) {
            window.location.href = `visualizar-aluno?id=${id}`;
        }
        
        function editarAluno(id) {
            window.location.href = `editar-aluno?id=${id}`;
        }
        
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este aluno? Esta ação não pode ser desfeita.')) {
                window.location.href = `excluir-aluno?id=${id}`;
            }
        }
        
        // Selecionar todos os checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Edição em lote
        document.getElementById('btnEditarLote').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item:checked');
            const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
            
            if (ids.length === 0) {
                alert('Selecione pelo menos um aluno para editar em lote.');
                return;
            }
            
            window.location.href = `editar-lote-alunos.php?ids=${ids.join(',')}`;
        });