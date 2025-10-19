document.addEventListener('DOMContentLoaded', function() {
    const btnNovo = document.getElementById('btnNovoMaterial');
    const formContainer = document.getElementById('formNovoMaterial');
    const materialForm = document.getElementById('materialForm');
    const alertaSucesso = document.getElementById('alertaSucesso');
    const alertaErro = document.getElementById('alertaErro');
    const btnSubmit = document.getElementById('btnSubmit'); // O botão único
	const btnCancelar = document.getElementById('btnCancelar');

	btnCancelar.addEventListener('click', function() {
			materialForm.reset(); // Limpa todos os campos
			document.getElementById('id_material').value = '';
			document.getElementById('arquivoAtual').innerHTML = '';
			formContainer.classList.add('oculto'); // Oculta o container do formulário
			alertaSucesso.classList.add('oculto');
			if (alertaErro) alertaErro.classList.add('oculto');
		});

	

    // Oculta tudo ao carregar
    if (formContainer) formContainer.classList.add('oculto');
    if (alertaSucesso) alertaSucesso.classList.add('oculto');
    if (alertaErro) alertaErro.classList.add('oculto');

    // Função para modo novo material
    function modoNovoMaterial() {
        document.getElementById('id_material').value = '';
        materialForm.reset();
        document.getElementById('grupoArquivo').classList.remove('oculto');
		document.getElementById('arquivoAtual').innerHTML = '';
        btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Enviar Material';
        formContainer.querySelector('h3').textContent = 'Enviar Novo Material';
    }

    // Ao clicar em "Novo material"
    btnNovo.addEventListener('click', function(e) {
        e.preventDefault();
        modoNovoMaterial();
        formContainer.classList.remove('oculto');
        alertaSucesso.classList.add('oculto');
        if (alertaErro) alertaErro.classList.add('oculto');
        formContainer.scrollIntoView({ behavior: "smooth" });
    });

    // Handler global para edição
   window.editarMaterial = function(id, descricao, turma, materia, arquivo_nome, arquivo_url) {
        document.getElementById('id_material').value = id;
		document.getElementById('arquivoAtual').innerHTML = 
        arquivo_nome
        ? `<span>Arquivo atual: <a href="${arquivo_url}" target="_blank">${arquivo_nome}</a></span>`
        : '<span>Nenhum arquivo enviado</span>';
        document.getElementById('descricao').value = descricao;
        document.getElementById('turma').value = turma;
        document.getElementById('materia').value = materia;
		document.getElementById('grupoArquivo').classList.remove('oculto');
        btnSubmit.innerHTML = '<i class="fas fa-save"></i> Salvar Alterações';
        formContainer.querySelector('h3').textContent = 'Editar Material';
        formContainer.classList.remove('oculto');
        alertaSucesso.classList.add('oculto');
        if (alertaErro) alertaErro.classList.add('oculto');
        formContainer.scrollIntoView({ behavior: "smooth" });
    };

    // Submit do form (cadastro ou edição)
    materialForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(materialForm);

        fetch('material.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alertaSucesso.textContent = data.message;
                alertaSucesso.classList.remove('oculto');
                formContainer.classList.add('oculto');
                materialForm.reset();
                setTimeout(() => {
                    alertaSucesso.classList.add('oculto');
                    location.reload();
                }, 3000);
            } else {
                if (alertaErro) {
                    alertaErro.textContent = data.message;
                    alertaErro.classList.remove('oculto');
                    setTimeout(() => {
                        alertaErro.classList.add('oculto');
                    }, 5000);
                } else {
                    alertaSucesso.textContent = data.message;
                    alertaSucesso.classList.remove('oculto');
                    setTimeout(() => {
                        alertaSucesso.classList.add('oculto');
                    }, 5000);
                }
            }
        })
        .catch(error => {
            if (alertaErro) {
                alertaErro.textContent = 'Erro ao enviar material!';
                alertaErro.classList.remove('oculto');
                setTimeout(() => {
                    alertaErro.classList.add('oculto');
                }, 5000);
            } else {
                alertaSucesso.textContent = 'Erro ao enviar material!';
                alertaSucesso.classList.remove('oculto');
                setTimeout(() => {
                    alertaSucesso.classList.add('oculto');
                }, 5000);
            }
        });
    });
});
//Exclusao
const modalGeral = new bootstrap.Modal(document.getElementById('modalGeral'));
const modalGeralLabel = document.getElementById('modalGeralLabel');
const modalGeralBody = document.getElementById('modalGeralBody');
const modalGeralConfirmar = document.getElementById('modalGeralConfirmar');

function abrirModalExclusao(id, descricao) {
    modalGeralLabel.textContent = "Excluir material?";
    modalGeralBody.innerHTML = `<p>Deseja realmente excluir o material <strong>${descricao}</strong>? Esta ação não poderá ser desfeita.</p>`;
    modalGeralConfirmar.textContent = "Sim, excluir";
    modalGeralConfirmar.classList.remove('btn-success');
    modalGeralConfirmar.classList.add('btn-cancelar');
    // Remove listeners antigos para evitar múltiplas execuções
    modalGeralConfirmar.replaceWith(modalGeralConfirmar.cloneNode(true));
    const novoBtn = document.getElementById('modalGeralConfirmar');
    novoBtn.onclick = function () {
        excluirMaterial(id);
        modalGeral.hide();
    };
    modalGeral.show();
}

function excluirMaterial(id) {
    fetch('excluir-material.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alertaSucesso.textContent = data.message;
            alertaSucesso.classList.remove('oculto');
            setTimeout(() => {
                alertaSucesso.classList.add('oculto');
                location.reload();
            }, 1500);
        } else {
            if (alertaErro) {
                alertaErro.textContent = data.message || 'Erro ao excluir material.';
                alertaErro.classList.remove('oculto');
                setTimeout(() => {
                    alertaErro.classList.add('oculto');
                }, 5000);
            } else {
                alertaSucesso.textContent = data.message || 'Erro ao excluir material.';
                alertaSucesso.classList.remove('oculto');
                setTimeout(() => {
                    alertaSucesso.classList.add('oculto');
                }, 5000);
            }
        }
    })
    .catch(() => {
        if (alertaErro) {
            alertaErro.textContent = 'Erro de comunicação ao tentar excluir material.';
            alertaErro.classList.remove('oculto');
            setTimeout(() => {
                alertaErro.classList.add('oculto');
            }, 5000);
        } else {
            alertaSucesso.textContent = 'Erro de comunicação ao tentar excluir material.';
            alertaSucesso.classList.remove('oculto');
            setTimeout(() => {
                alertaSucesso.classList.add('oculto');
            }, 5000);
        }
    });
}
