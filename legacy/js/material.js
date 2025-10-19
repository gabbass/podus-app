// Função GLOBAL: editar material (chamada via onclick na tabela)
function editarMaterial(id) {
    rolarEDestacarPrimeiroDestaque();
    const container = document.getElementById('formNovoMaterial');
    if (container) container.classList.remove('oculto');

    // Pega a linha correta
    var linha = document.querySelector('tr[data-id="' + id + '"]');
    if (!linha) {
        mostrarAlerta('Erro: linha não encontrada na tabela.', 'danger');
        return;
    }

    setTituloSubtituloFormMaterial('editar');
    const idInput = document.getElementById('id-material');
    if (idInput) idInput.value = id;

    // Valores da linha
    const tdTurma = linha.querySelector('.col-turma');
    const valorAtualTurma = tdTurma ? tdTurma.textContent.trim() : '';

    const tdMateria = linha.querySelector('.col-materia');
    const valorAtualMateria = tdMateria ? tdMateria.textContent.trim() : '';

    const descricao = document.getElementById('descricao');
    if (descricao) {
        const tdDescricao = linha.querySelector('.col-descricao');
        if (tdDescricao) descricao.value = tdDescricao.textContent.trim();
        descricao.removeAttribute('readonly');
    }

    const turma = document.getElementById('turma');
    if (turma) {
        turma.value = valorAtualTurma;
        turma.removeAttribute('disabled');
    }

    const materia = document.getElementById('materia');
    if (materia) {
        materia.value = valorAtualMateria;
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
    const grupoArquivo = document.getElementById('grupoArquivo');
    if (grupoArquivo) grupoArquivo.classList.remove('oculto');

    popularSelectTurmas('turma', valorAtualTurma);
    popularSelectMaterias('materia', valorAtualMateria);
}

// Função GLOBAL: excluir material (chamada via onclick na tabela)
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

// Função GLOBAL: abrir form para cadastro (não precisa alterar, já está global por uso no addEventListener)
function abrirCadastroMaterial() {
    rolarEDestacarPrimeiroDestaque();
    if (!formMaterial) return;
    formMaterial.reset();

    const container = document.getElementById('formNovoMaterial');
    if (container) container.classList.remove('oculto');

    let valorAtualTurma = '';
    let valorAtualMateria = '';

    const idInput = document.getElementById('id-material');
    if (idInput) idInput.value = '';
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

    popularSelectTurmas('turma', valorAtualTurma);
    popularSelectMaterias('materia', valorAtualMateria);
}

// Fechar/Clean form
function fecharSegundoContainerMaterial() {
    if (!formMaterial) return;
    formMaterial.reset();
    const container = document.getElementById('formNovoMaterial');
    if (container) container.classList.add('oculto');
}

// Atualizar tabela de materiais (reload via AJAX)
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
                const tabela = listaMateriais.querySelector('.tabela-materiais');
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

// Popular selects AJAX
function popularSelectTurmas(idSelect, valorAtual = '') {
    fetch('includes/material.php?acao=turmas')
        .then(resp => resp.text())
        .then(text => {
            let turmas;
            try { turmas = JSON.parse(text); }
            catch (e) {
                mostrarAlerta('Erro ao processar resposta do servidor (turmas): ' + text, 'danger');
                return;
            }
            const select = document.getElementById(idSelect);
            if (select) {
                select.innerHTML = '<option value="">Selecione uma turma</option>';
                turmas.forEach(turma => {
                    select.innerHTML += `<option value="${turma.nome}">${turma.nome}</option>`;
                });
                if (valorAtual) select.value = valorAtual;
            }
        })
        .catch(err => {
            mostrarAlerta('Erro de rede ao buscar turmas: ' + err, 'danger');
        });
}
function popularSelectMaterias(idSelect, valorAtual = '') {
    fetch('includes/material.php?acao=materias')
        .then(resp => resp.text())
        .then(text => {
            let materias;
            try { materias = JSON.parse(text); }
            catch (e) {
                mostrarAlerta('Erro ao processar resposta do servidor (matérias): ' + text, 'danger');
                return;
            }
            const select = document.getElementById(idSelect);
            if (select) {
                select.innerHTML = '<option value="">Selecione uma matéria</option>';
                materias.forEach(materia => {
                    select.innerHTML += `<option value="${materia}">${materia}</option>`;
                });
                if (valorAtual) select.value = valorAtual;
            }
        })
        .catch(err => {
            mostrarAlerta('Erro de rede ao buscar matérias: ' + err, 'danger');
        });
}

// DOMContentLoaded: só inicialização e binds (não funções globais)
window.addEventListener('DOMContentLoaded', function () {
    atualizarListaMateriais();
    popularSelectTurmas('turma-filtro');
    popularSelectMaterias('materia-filtro');

    const btnNovoMaterial = document.getElementById('btnNovoMaterial');
    if (btnNovoMaterial) {
        btnNovoMaterial.addEventListener('click', function (e) {
            e.preventDefault();
            abrirCadastroMaterial();
        });
    }

    const btnCancelarMaterial = document.getElementById('btnCancelarMaterial');
    if (btnCancelarMaterial) {
        btnCancelarMaterial.addEventListener('click', fecharSegundoContainerMaterial);
    }

    // Submissão do form (criar/editar material)
    const formMaterial = document.getElementById('crudMaterialForm');
    if (formMaterial) {
        window.formMaterial = formMaterial; // Deixa global para as outras funções usarem
        formMaterial.addEventListener('submit', function (e) {
            e.preventDefault();
            let data = new FormData(formMaterial);

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

    // Filtros
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
});

// Setar título/subtítulo
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
