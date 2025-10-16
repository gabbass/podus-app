window.addEventListener('DOMContentLoaded', function () {
    atualizarListaMateriais();
	popularSelectTurmas('turma-filtro');
    popularSelectMaterias('materia-filtro');

// Abrir form novo material
const btnNovoMaterial = document.getElementById('btnNovoMaterial');
if (btnNovoMaterial) {
    btnNovoMaterial.addEventListener('click', function (e) {
        e.preventDefault();
        abrirCadastroMaterial();
    });
}

// Limpar e fechar o form de material
const btnCancelarMaterial = document.getElementById('btnCancelarMaterial');
if (btnCancelarMaterial) {
    btnCancelarMaterial.addEventListener('click', fecharSegundoContainerMaterial);
}

// Submissão do form (criar/editar material)
const formMaterial = document.getElementById('crudMaterialForm');
if (formMaterial) {
    formMaterial.addEventListener('submit', function (e) {
        e.preventDefault();
        let form = e.target;
        let data = new FormData(form);

        fetch('includes/material.php', {
            method: 'POST',
            body: data
        })
            .then(resp => resp.json())
            .then(res => {
                if (res.sucesso) {
                    mostrarAlerta(res.mensagem || 'Material salvo com sucesso!', 'success');
                    fecharSegundoContainerMaterial();
                    atualizarListaMateriais();
                } else {
                    mostrarAlerta(res.mensagem || 'Erro ao salvar material!', 'danger');
                }
            })
            .catch(() => {
                mostrarAlerta('Erro inesperado ao salvar!', 'danger');
            });
    });
}

// Abrir formulário para cadastro
function abrirCadastroMaterial() {
	rolarEDestacarPrimeiroDestaque();
    if (!formMaterial) return;
    formMaterial.reset();
	let valorAtualTurma = tdTurma ? tdTurma.textContent.trim() : '';
	let valorAtualMateria = tdMateria ? tdMateria.textContent.trim() : '';
    const idInput = document.getElementById('id-material');
    if (idInput) idInput.value = '';
    setTituloSubtituloFormMaterial('criar');

    const container = document.querySelector('.segundo-container');
    if (container) container.classList.remove('oculto');

    const btnSalvar = document.getElementById('btnSalvarMaterial');
    if (btnSalvar) btnSalvar.classList.remove('oculto');

    const btnEditar = document.getElementById('btnEditarMaterial');
    if (btnEditar) btnEditar.classList.add('oculto');

    const descricao = document.getElementById('descricao');
    if (descricao) descricao.removeAttribute('readonly');

    const turma = document.getElementById('turma');
    if (turma) turma.removeAttribute('disabled');

    const materia = document.getElementById('materia');
    if (materia) materia.removeAttribute('disabled');

    const grupoArquivo = document.getElementById('grupoArquivo');
    if (grupoArquivo) grupoArquivo.classList.remove('oculto');

    const arquivoAtual = document.getElementById('arquivoAtual');
    if (arquivoAtual) arquivoAtual.innerHTML = '';
	
	popularSelectTurmas('turma', valorAtualTurma);
    popularSelectMaterias('materia', valorAtualMateria);
}

// Fechar/Clean form
function fecharSegundoContainerMaterial() {
    if (!formMaterial) return;
    formMaterial.reset();
    const idInput = document.getElementById('id-material');
    if (idInput) idInput.value = '';
    const container = document.querySelector('.segundo-container');
    if (container) container.classList.add('oculto');
    setTituloSubtituloFormMaterial('criar');

    const btnSalvar = document.getElementById('btnSalvarMaterial');
    if (btnSalvar) btnSalvar.classList.remove('oculto');

    const btnEditar = document.getElementById('btnEditarMaterial');
    if (btnEditar) btnEditar.classList.add('oculto');

    const descricao = document.getElementById('descricao');
    if (descricao) descricao.removeAttribute('readonly');

    const turma = document.getElementById('turma');
    if (turma) turma.removeAttribute('disabled');

    const materia = document.getElementById('materia');
    if (materia) materia.removeAttribute('disabled');

    const grupoArquivo = document.getElementById('grupoArquivo');
    if (grupoArquivo) grupoArquivo.classList.remove('oculto');

    const arquivoAtual = document.getElementById('arquivoAtual');
    if (arquivoAtual) arquivoAtual.innerHTML = '';
}

// Editar material
function editarMaterial(id) {
	rolarEDestacarPrimeiroDestaque();
	let valorAtualTurma = tdTurma ? tdTurma.textContent.trim() : '';
	let valorAtualMateria = tdMateria ? tdMateria.textContent.trim() : '';
    var linha = document.querySelector('tr[data-id="' + id + '"]');
    if (!linha) {
        mostrarAlerta('Erro: linha não encontrada na tabela.', 'danger');
        return;
    }
    setTituloSubtituloFormMaterial('editar');
    const idInput = document.getElementById('id-material');
    if (idInput) idInput.value = id;

    const descricao = document.getElementById('descricao');
    if (descricao) {
        const tdDescricao = linha.querySelector('.col-descricao');
        if (tdDescricao) descricao.value = tdDescricao.textContent.trim();
        descricao.removeAttribute('readonly');
    }

    const turma = document.getElementById('turma');
    if (turma) {
        const tdTurma = linha.querySelector('.col-turma');
        if (tdTurma) turma.value = tdTurma.textContent.trim();
        turma.removeAttribute('disabled');
    }

    const materia = document.getElementById('materia');
    if (materia) {
        const tdMateria = linha.querySelector('.col-materia');
        if (tdMateria) materia.value = tdMateria.textContent.trim();
        materia.removeAttribute('disabled');
    }

    const arquivoAtual = document.getElementById('arquivoAtual');
    if (arquivoAtual) {
        const tdArquivo = linha.querySelector('.col-arquivo');
        arquivoAtual.innerHTML = tdArquivo ? tdArquivo.innerHTML : '';
    }

    const btnSalvar = document.getElementById('btnSalvarMaterial');
    if (btnSalvar) btnSalvar.classList.remove('oculto');

    const btnEditar = document.getElementById('btnEditarMaterial');
    if (btnEditar) btnEditar.classList.add('oculto');

    const container = document.querySelector('.segundo-container');
    if (container) container.classList.remove('oculto');

    const grupoArquivo = document.getElementById('grupoArquivo');
    if (grupoArquivo) grupoArquivo.classList.remove('oculto');
	
	popularSelectTurmas('turma', valorAtualTurma);
	popularSelectMaterias('materia', valorAtualMateria);
}

// Botão editar dentro do form
const btnEditarMaterial = document.getElementById('btnEditarMaterial');
if (btnEditarMaterial) {
    btnEditarMaterial.addEventListener('click', function () {
		rolarEDestacarPrimeiroDestaque();
        const idInput = document.getElementById('id-material');
        if (idInput) editarMaterial(idInput.value);
    });
}

// Excluir material - abrir modal
let idMaterialParaExcluir = null;
function abrirConfirmacaoExcluirMaterial(id) {
	rolarEDestacarPrimeiroDestaque();
    idMaterialParaExcluir = id;
    const modalLabel = document.getElementById('modalGeralLabel');
    if (modalLabel) modalLabel.innerText = "Excluir Material";
    const modalBody = document.getElementById('modalGeralBody');
    if (modalBody) modalBody.innerText = "Deseja realmente excluir este material?";
    const modalConfirmar = document.getElementById('modalGeralConfirmar');
    if (modalConfirmar) modalConfirmar.onclick = confirmarExclusaoAJAXMaterial;
    let modal = new bootstrap.Modal(document.getElementById('modalGeral'));
    modal.show();
}

// Confirmar exclusão via AJAX
function confirmarExclusaoAJAXMaterial() {
    if (!idMaterialParaExcluir) return;
    const data = new FormData();
    data.append('id-material', idMaterialParaExcluir);
    data.append('acao', 'excluir');

    fetch('includes/material.php', {
        method: 'POST',
        body: data
    })
        .then(resp => resp.json())
        .then(res => {
            if (res.sucesso) {
                mostrarAlerta('Material excluído com sucesso!', 'success');
                atualizarListaMateriais();
            } else {
                mostrarAlerta(res.mensagem || 'Erro ao excluir material!', 'danger');
            }
            fecharModalGeral();
        })
        .catch(() => {
            mostrarAlerta('Erro de comunicação com o servidor!', 'danger');
            fecharModalGeral();
        });
}

// Fechar modal
function fecharModalGeral() {
    idMaterialParaExcluir = null;
    let modal = bootstrap.Modal.getInstance(document.getElementById('modalGeral'));
    if (modal) modal.hide();
}

// Recarregar tabela de materiais (reload parcial, nunca reload global)
function atualizarListaMateriais(descricao = '', turma = '', materia = '') {
    const url = 'includes/listar-materiais.php?descricao=' + encodeURIComponent(descricao)
        + '&turma=' + encodeURIComponent(turma)
        + '&materia=' + encodeURIComponent(materia)
        + '&t=' + Date.now();
    fetch(url)
        .then(resp => resp.text())
        .then(html => {
            const listaMateriais = document.getElementById('lista-materiais');
            if (listaMateriais) {
                listaMateriais.innerHTML = html;
                const tabela = listaMateriais.querySelector('#tabela-materiais');
                if (tabela) {
                    const total = tabela.getAttribute('data-total');
                    const contador = document.getElementById('contadorRegistrosMateriais');
                    if (contador) {
                        contador.innerHTML = `Exibindo <strong>${total}</strong> registro(s) encontrado(s)`;
                    }
                }
            }
        });
}

// Filtros (submit do form de filtro)
const filtrosFormMateriais = document.getElementById('filtrosFormMateriais');
if (filtrosFormMateriais) {
    filtrosFormMateriais.addEventListener('submit', function (e) {
        e.preventDefault();
        const descricao = document.getElementById('descricao-filtro')?.value || '';
        const turma = document.getElementById('turma-filtro')?.value || '';
        const materia = document.getElementById('materia-filtro')?.value || '';
        atualizarListaMateriais(descricao, turma, materia);
    });
}

// Limpar filtro
const btnLimparFiltroMateriais = document.getElementById('btnLimparFiltroMateriais');
if (btnLimparFiltroMateriais) {
    btnLimparFiltroMateriais.addEventListener('click', function () {
        const desc = document.getElementById('descricao-filtro');
        if (desc) desc.value = '';
        const turma = document.getElementById('turma-filtro');
        if (turma) turma.value = '';
        const materia = document.getElementById('materia-filtro');
        if (materia) materia.value = '';
        atualizarListaMateriais('', '', '');
    });
}

// Setar título e subtítulo do formulário (opcional, ajuste para o seu HTML)
function setTituloSubtituloFormMaterial(acao) {
    const titulo = document.getElementById('tituloFormMaterial');
    const subtitulo = document.getElementById('subtituloFormMaterial');
    if (!titulo || !subtitulo) return;

    if (acao === 'criar') {
        titulo.textContent = 'Novo Material';
        subtitulo.textContent = 'Preencha os dados para cadastrar um novo material.';
    } else if (acao === 'editar') {
        titulo.textContent = 'Editar Material';
        subtitulo.textContent = 'Altere os dados e clique em salvar.';
    } else if (acao === 'visualizar') {
        titulo.textContent = 'Visualizar Material';
        subtitulo.textContent = 'Veja os detalhes. Clique em editar para modificar.';
    } else {
        titulo.textContent = 'Carregando...';
        subtitulo.textContent = 'Carregando...';
    }
}


function popularSelectTurmas(idSelect, valorAtual = '') {
    fetch('includes/material.php?acao=turmas')
        .then(resp => resp.text())
        .then(text => {
            let turmas;
            try {
                turmas = JSON.parse(text);
            } catch (e) {
                mostrarAlerta('Erro ao processar resposta do servidor (turmas): ' + text, 'danger');
                return;
            }
            console.log("RESPOSTA CRUA TURMAS:", turmas);
            const select = document.getElementById(idSelect);
            if (select) {
                select.innerHTML = '<option value="">Selecione uma turma</option>';
                turmas.forEach(turma => {
                    select.innerHTML += `<option value="${turma.nome}">${turma.nome}</option>`;
                });
                if (valorAtual) select.value = valorAtual;
            }
        })
        .catch((err) => {
            mostrarAlerta('Erro de rede ao buscar turmas: ' + err, 'danger');
        });
}

function popularSelectMaterias(idSelect, valorAtual = '') {
    fetch('includes/material.php?acao=materias')
        .then(resp => resp.text())
        .then(text => {
            let materias;
            try {
                materias = JSON.parse(text);
            } catch (e) {
                mostrarAlerta('Erro ao processar resposta do servidor (matérias): ' + text, 'danger');
                return;
            }
            console.log("RESPOSTA CRUA MATERIAS:", materias);
            const select = document.getElementById(idSelect);
            if (select) {
                select.innerHTML = '<option value="">Selecione uma matéria</option>';
                materias.forEach(materia => {
                    select.innerHTML += `<option value="${materia}">${materia}</option>`;
                });
                if (valorAtual) select.value = valorAtual;
            }
        })
        .catch((err) => {
            mostrarAlerta('Erro de rede ao buscar matérias: ' + err, 'danger');
        });
}



});
