//Contar
window.addEventListener('DOMContentLoaded', function() {
    atualizarListaTurmas();
});
  
// Função para limpar os filtros
       document.getElementById('btnLimparFiltro').addEventListener('click', function() {
    document.getElementById('nome-filtro').value = '';
    fetch('includes/listar-turmas.php?t=' + Date.now())
      .then(resp => resp.text())
      .then(html => {
          document.getElementById('lista-turmas').innerHTML = html;
          // Atualiza o contador de registros pelo atributo data-total da nova tabela
          const tabela = document.getElementById('lista-turmas').querySelector('#tabela-turmas');
          if (tabela) {
              const total = tabela.getAttribute('data-total');
              document.getElementById('contadorRegistros').innerHTML = `Exibindo <strong>${total}</strong> registro(s) encontrado(s)`;
          }
      });
});

const nomeFiltro = document.getElementById('nome-filtro');
const btnLimpar = document.getElementById('btnLimparFiltro');

nomeFiltro.addEventListener('input', function() {
    btnLimpar.disabled = nomeFiltro.value.trim() === '';
});

btnLimpar.addEventListener('click', function() {
    nomeFiltro.value = '';
    btnLimpar.disabled = true;
    atualizarListaTurmas('');
});
      
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
	  
function confirmarExclusao(id) {
	if (confirm('Tem certeza que deseja excluir esta turma? Esta ação não pode ser desfeita.')) {
		window.location.href = `excluir-turma?id=${id}`;
	}
}

// Selecionar todos os checkboxes
document.getElementById('selectAll').addEventListener('change', function() {
	const checkboxes = document.querySelectorAll('.checkbox-item');
	checkboxes.forEach(checkbox => {
		checkbox.checked = this.checked;
	});
});

// Selecionar todos os linha      
function selecionarLinha(linha) {
		var linhas = document.querySelectorAll(".tabela-turmas tbody tr");
		linhas.forEach(function(item) {
		item.classList.remove("selecionada");
		});
		linha.classList.add("selecionada");
}
//Criação
document.getElementById('turmaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let form = e.target;
    let data = new FormData(form);

    fetch('includes/turmas.php', {
        method: 'POST',
        body: data
    })
    .then(resp => resp.json())
    .then(res => {
	
        if (res.sucesso) {
			
            mostrarAlerta('Turma salva com sucesso!', 'success');
            fecharSegundoContainer();
            atualizarListaTurmas();
        } else {
            mostrarAlerta('Erro: ' + res.mensagem, 'danger');
			
        }
    })
.catch(error => {
    
    mostrarAlerta('Erro inesperado ao salvar!', 'danger');
});

});

//Fechar
document.getElementById('btnCancelar').addEventListener('click', fecharSegundoContainer);
function fecharSegundoContainer() {
    document.querySelector('.segundo-container').classList.add('oculto');
}

function atualizarListaTurmas(nome = '') {
	const url = 'includes/listar-turmas.php?nome=' + encodeURIComponent(nome) + '&t=' + Date.now();
    fetch(url)
      .then(resp => resp.text())
      .then(html => {
          document.getElementById('lista-turmas').innerHTML = html;
          const tabela = document.getElementById('lista-turmas').querySelector('#tabela-turmas');
          if (tabela) {
              const total = tabela.getAttribute('data-total');
              document.getElementById('contadorRegistros').innerHTML = `Exibindo <strong>${total}</strong> registro(s) encontrado(s)`;
          }
      });
}

//Abrir
function abrirCadastroTurma() {
    // Limpa o formulário (opcional)
    document.getElementById('turmaForm').reset();
    // Mostra o container
	// Quando quiser destacar:
	rolarEDestacarPrimeiroDestaque();
	setTituloSubtituloForm('criar')
    document.querySelector('.segundo-container').classList.remove('oculto');
}


//Filtros
document.getElementById('filtrosForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const nome = document.getElementById('nome-filtro').value;
    const url = 'includes/listar-turmas.php?nome=' + encodeURIComponent(nome) + '&t=' + Date.now();
    console.log('Buscando', url);
    fetch(url)
      .then(resp => resp.text())
      .then(html => {
		document.getElementById('lista-turmas').innerHTML = html;

		// Atualiza o contador de registros pelo atributo data-total da nova tabela
		const tabela = document.getElementById('lista-turmas').querySelector('#tabela-turmas');
		if (tabela) {
			const total = tabela.getAttribute('data-total');
			document.getElementById('contadorRegistros').innerHTML = `Exibindo <strong>${total}</strong> registro(s) encontrado(s)`;
		}
});

});

//Visualizar
function visualizarTurma(id) {
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
	// Quando quiser destacar:
	rolarEDestacarPrimeiroDestaque();
	setTituloSubtituloForm('visualizar')
    document.getElementById('id-turma').value = id;
    document.getElementById('nome').value = nome;
    document.getElementById('nome').setAttribute('readonly', true);
    document.getElementById('btnSalvarTurma').classList.add('oculto');
    document.getElementById('btnCancelar').classList.add('oculto');
    document.getElementById('btnEditarTurma').classList.remove('oculto');
    document.querySelector('.segundo-container').classList.remove('oculto');
}

//Editar
function editarTurma(id) {
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
	// Quando quiser destacar:
	rolarEDestacarPrimeiroDestaque();
	setTituloSubtituloForm('editar')
    document.getElementById('id-turma').value = id;
    document.getElementById('nome').value = nome;
    document.getElementById('nome').removeAttribute('readonly');
    document.getElementById('btnEditarTurma').classList.add('oculto');
    document.getElementById('btnSalvarTurma').classList.remove('oculto');
    document.getElementById('btnCancelar').classList.remove('oculto');
    document.querySelector('.segundo-container').classList.remove('oculto');
	
}

//Fechar editar
function fecharSegundoContainer() {
    document.getElementById('turmaForm').reset();
    document.getElementById('nome').removeAttribute('readonly');
    document.getElementById('btnEditarTurma').classList.add('oculto');
    document.getElementById('btnSalvarTurma').classList.remove('oculto');
    document.getElementById('btnCancelar').classList.remove('oculto');
    document.querySelector('.segundo-container').classList.add('oculto');
}

//Editar ao ler
document.getElementById('btnEditarTurma').addEventListener('click', function() {
	setTituloSubtituloForm('editar')
    document.getElementById('nome').removeAttribute('readonly');
    this.classList.add('oculto');
    document.getElementById('btnSalvarTurma').classList.remove('oculto');
    document.getElementById('btnCancelar').classList.remove('oculto');
});


//Exclusao
let idTurmaParaExcluir = null;

function abrirConfirmacaoExcluir(id) {
    idTurmaParaExcluir = id;
    document.getElementById('modalGeralLabel').innerText = "Excluir Turma";
    document.getElementById('modalGeralBody').innerText = "Deseja realmente excluir esta turma?";
    document.getElementById('modalGeralConfirmar').onclick = confirmarExclusaoAJAX;
    // Mostra o modal bootstrap via JS
    let modal = new bootstrap.Modal(document.getElementById('modalGeral'));
    modal.show();
}

//Confirmar Exclusao
function confirmarExclusaoAJAX() {
    if (!idTurmaParaExcluir) return;
    const data = new FormData();
    data.append('id-turma', idTurmaParaExcluir);
    data.append('acao', 'excluir');

    fetch('includes/turmas.php', {
        method: 'POST',
        body: data
    })
    .then(resp => resp.json())
    .then(res => {
        if (res.sucesso) {
            mostrarAlerta('Turma excluída com sucesso!', 'success');
            atualizarListaTurmas();
        } else {
            mostrarAlerta(res.mensagem || 'Erro ao excluir turma!', 'danger');
        }
        fecharModalGeral();
    })
    .catch(() => {
        mostrarAlerta('Erro de comunicação com o servidor!', 'danger');
        fecharModalGeral();
    });
}

//Fechar modal
function fecharModalGeral() {
    idTurmaParaExcluir = null;
    // Esconde o modal via Bootstrap
    let modal = bootstrap.Modal.getInstance(document.getElementById('modalGeral'));
    if (modal) modal.hide();
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
