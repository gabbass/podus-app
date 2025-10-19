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
        
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este aluno? Esta ação não pode ser desfeita.')) {
                window.location.href = `excluir-aluno?id=${id}`;
            }
        }
        
        // Selecionar todos os checkboxes
       // document.getElementById('selectAll').addEventListener('change', function() {
        //     const checkboxes = document.querySelectorAll('.checkbox-item');
       //      checkboxes.forEach(checkbox => {
        //         checkbox.checked = this.checked;
       //      });
       //  });
        
        // Edição em lote
      //   document.getElementById('btnEditarLote').addEventListener('click', function() {
        //     const checkboxes = document.querySelectorAll('.checkbox-item:checked');
      //       const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
      //       
      //       if (ids.length === 0) {
       //          alert('Selecione pelo menos um aluno para editar em lote.');
       //          return;
       //      }
            
       //      window.location.href = `editar-lote-alunos.php?ids=${ids.join(',')}`;
     //    });
		
		
//Abrir
function abrirCadastroAluno() {
    // Limpa o formulário (opcional)
    //document.getElementById('alunoForm').reset();
    // Mostra o container
	// Quando quiser destacar:
	rolarEDestacarPrimeiroDestaque();
	setTituloSubtituloForm('criar')
    document.querySelector('.segundo-container').classList.remove('oculto');
}


//Editar
function editarAluno(id) {
    var linha = document.querySelector('tr[data-id="' + id + '"]');
    if (!linha) {
        mostrarAlerta('Erro: linha não encontrada na tabela.', 'danger');
        return;
    }
    var nomeTd = linha.querySelector('.col-nome');
    if (!nomeTd) {
        mostrarAlerta('Erro: coluna do nome não encontrada.', 'danger');
        return;
    }
    var nome = nomeTd.textContent.trim();
	document.getElementById('alunoForm').reset();
	// Quando quiser destacar:
	rolarEDestacarPrimeiroDestaque();
	setTituloSubtituloForm('editar')
    document.querySelector('.segundo-container').classList.remove('oculto');
	
}


//Mudar título
function setTituloSubtituloForm(acao) {
    const titulo = document.getElementById('tituloFormTurma');
    const subtitulo = document.getElementById('subtituloFormTurma');
    if (!titulo || !subtitulo) return;

    if (acao === 'criar') {
        titulo.textContent = 'Nova Turma';
        subtitulo.textContent = 'Preencha as informações para cadastrar uma nova turma.';
    } else if (acao === 'editar') {
        titulo.textContent = 'Editar Turma';
        subtitulo.textContent = 'Altere os dados e clique em salvar para atualizar a turma.';
    } else if (acao === 'visualizar') {
        titulo.textContent = 'Visualizar Turma';
        subtitulo.textContent = 'Veja os detalhes da turma. Clique em editar para modificar.';
    } else {
        titulo.textContent = 'Carregando...';
        subtitulo.textContent = 'Carregando...';
    }
}