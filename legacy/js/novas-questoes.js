	//Criar nova questao
		document.getElementById('btnCriarQuestao').addEventListener('click', function () {
			window.location.href = 'criar-editar-questao.php';
			});

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
		function editarQuestao(id) {
    window.location.href = 'criar-editar-questao.php?id=' + id;
}
        function abrirQuestao(id) {
            window.location.href = `visualizar-questao?id=${id}`;
        }
        
                
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.')) {
                window.location.href = `excluir-questao?id=${id}`;
            }
        }
        
        // Selecionar todos os checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Gerar Prova
        document.getElementById('btnGerarProva').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item:checked');
            const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
            
            if (ids.length === 0) {
                alert('Selecione pelo menos uma questão para gerar a prova.');
                return;
            }
            
            window.location.href = `gerar-prova.php?ids=${ids.join(',')}`;
        });
        
        // Vincular questões a turma
        document.getElementById('btnVincularTurma').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item:checked');
            const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
            
            if (ids.length === 0) {
                alert('Selecione pelo menos uma questão para vincular a turma.');
                return;
            }
            
            window.location.href = `vincular-turma.php?ids=${ids.join(',')}`;
        });
        
        // Gerar Prova Aleatória
        document.getElementById('btnGerarProvaAleatoria').addEventListener('click', function() {
            window.location.href = `gerar-aleatoria-prova`;
        });
      
        
        // Gerar Prova Online
		document.getElementById('gerarProvaOnline').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.checkbox-item:checked');
    const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
    
    if (ids.length === 0) {
        alert('Selecione pelo menos uma questão para gerar a prova online.');
        return;
    }
    
    window.location.href = `prova-online.php?ids=${ids.join(',')}`;
});