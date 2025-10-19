//Criar nova questao
	var btnCriarQuestao = document.getElementById('btnCriarQuestao');
		if (btnCriarQuestao) {
		btnCriarQuestao.addEventListener('click', function () {
        window.location.href = 'criar-editar-questao.php';
		});
		}

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
	var selectAll = document.getElementById('selectAll');
		if (selectAll) {
			selectAll.addEventListener('change', function() {
				const checkboxes = document.querySelectorAll('.checkbox-item');
				checkboxes.forEach(checkbox => {
				checkbox.checked = this.checked;
				});
			});
		}
	
	// Gerar Prova
	var btnGerarProvaOnline = document.getElementById('gerarProvaOnline');
		if (btnGerarProvaOnline) {
			btnGerarProvaOnline.addEventListener('click', function() {
				const checkboxes = document.querySelectorAll('.checkbox-item:checked');
				const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
				
				if (ids.length === 0) {
					alert('Selecione pelo menos uma questão para gerar a prova online.');
					return;
				}
				
				window.location.href = `prova-online.php?ids=${ids.join(',')}`;
			});
		}
        
	// Vincular questões a turma
	var btnVincularTurma = document.getElementById('btnVincularTurma');
		if (btnVincularTurma) {
			btnVincularTurma.addEventListener('click', function() {
				const checkboxes = document.querySelectorAll('.checkbox-item:checked');
				const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
				
				if (ids.length === 0) {
					alert('Selecione pelo menos uma questão para vincular a turma.');
					return;
				}
				
				window.location.href = `vincular-turma.php?ids=${ids.join(',')}`;
			});
		}	

	// Gerar Prova Aleatória
	var btnGerarProvaAleatoria = document.getElementById('btnGerarProvaAleatoria');
		if (btnGerarProvaAleatoria) {
			btnGerarProvaAleatoria.addEventListener('click', function() {
				window.location.href = `gerar-aleatoria-prova`;
			});
		}

        
	// Gerar Prova Online
	var btnGerarProvaOnline = document.getElementById('gerarProvaOnline');
	if (btnGerarProvaOnline) {
		btnGerarProvaOnline.addEventListener('click', function() {
			const checkboxes = document.querySelectorAll('.checkbox-item:checked');
			const ids = Array.from(checkboxes).map(checkbox => checkbox.value);

			if (ids.length === 0) {
				alert('Selecione pelo menos uma questão para gerar a prova online.');
				return;
			}

			window.location.href = `prova-online.php?ids=${ids.join(',')}`;
		});
	}
	
// Mostra pré-visualização da imagem selecionada
	var imagemInput = document.getElementById('imagem');
		if (imagemInput) {
		imagemInput.addEventListener('change', function(e) {
			const preview = document.getElementById('preview');
			const file = e.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					preview.src = e.target.result;
					preview.style.display = 'block';
				}
				reader.readAsDataURL(file);
			} else {
				preview.style.display = 'none';
				preview.src = '#';
			}
		});
	}

// --- questoes-professor.php ---
	function abrirModal(id) {
	  document.getElementById('modal-'+id).style.display = 'block';
	}
	function fecharModal(id) {
	  document.getElementById('modal-'+id).style.display = 'none';
	}
	// Fecha o modal se o usuário clicar fora da caixa de conteúdo
	window.addEventListener('click', function(event) {
	var modais = document.getElementsByClassName('modal');
	for(var i=0; i<modais.length; i++) {
		if (event.target == modais[i]) {
			modais[i].style.display = "none";
		}
	}
	});