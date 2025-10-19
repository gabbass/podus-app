document.addEventListener('DOMContentLoaded', function () {
    // Formulário de dados cadastrais
    const formCadastro = document.getElementById('form-cadastro');
    const btnCadastro = formCadastro.querySelector('button[type="submit"]');

    // Máscara para telefone (padrão brasileiro)
    formCadastro.telefone.addEventListener('input', function (e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
        e.target.value = !x[2]
            ? x[1]
            : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });

    function validarEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validarCadastro() {
        const nome = formCadastro.nome.value.trim();
        const email = formCadastro.email.value.trim();
        const telefone = formCadastro.telefone.value.trim();

        if (!nome || !email || !telefone) {
            mostrarAlerta('Todos os campos são obrigatórios!', 'danger');
            return false;
        }
        if (!validarEmail(email)) {
            mostrarAlerta('E-mail inválido!', 'danger');
            return false;
        }
        return true;
    }

    // --- Preenche dados do cadastro via AJAX ao carregar a página ---
    fetch('includes/cad-professor.php')
        .then(response => response.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                formCadastro.nome.value     = data.dados.nome || '';
                formCadastro.email.value    = data.dados.email || '';
                formCadastro.telefone.value = data.dados.telefone || '';
                formCadastro.escola.value   = data.dados.escola || '';
            } else {
                mostrarAlerta(data.mensagem || 'Erro ao buscar cadastro.', 'danger');
            }
        })
        .catch(() => {
            mostrarAlerta('Erro ao buscar dados.', 'danger');
        });

    // --- Submissão AJAX do cadastro ---
    formCadastro.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validarCadastro()) return;

        btnCadastro.disabled = true;
        btnCadastro.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...`;

        const formData = new FormData(formCadastro);

        fetch('includes/cad-professor.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    mostrarAlerta(data.mensagem || 'Cadastro atualizado!', 'success');
                } else {
                    mostrarAlerta(data.mensagem || 'Erro ao atualizar cadastro.', 'danger');
                }
            })
            .catch(() => {
                mostrarAlerta('Erro de conexão com o servidor.', 'danger');
            })
            .finally(() => {
                btnCadastro.disabled = false;
                btnCadastro.innerHTML = `<i class="fas fa-save"></i> Salvar Alterações`;
            });
    });

    // --- Formulário de senha ---
    const formSenha = document.getElementById('form-senha');
    const btnSenha = formSenha.querySelector('button[type="submit"]');

    function validarSenha() {
        const novaSenha = formSenha.nova_senha.value;
        const confirmarSenha = formSenha.confirmar_senha.value;
        if (!novaSenha || !confirmarSenha) {
            mostrarAlerta('Preencha ambos os campos de senha!', 'danger');
            return false;
        }
        if (novaSenha !== confirmarSenha) {
            mostrarAlerta('A nova senha e a confirmação não coincidem!', 'danger');
            return false;
        }
        return true;
    }

    // --- Submissão AJAX da senha ---
    formSenha.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validarSenha()) return;

        btnSenha.disabled = true;
        btnSenha.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...`;

        const formData = new FormData(formSenha);

        fetch('includes/alterar-senha-professor.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    mostrarAlerta(data.mensagem || 'Senha alterada!', 'success');
                    formSenha.nova_senha.value = '';
                    formSenha.confirmar_senha.value = '';
                } else {
                    mostrarAlerta(data.mensagem || 'Erro ao alterar senha.', 'danger');
                }
            })
            .catch(() => {
                mostrarAlerta('Erro de conexão com o servidor.', 'danger');
            })
            .finally(() => {
                btnSenha.disabled = false;
                btnSenha.innerHTML = `<i class="fas fa-save"></i> Alterar Senha`;
            });
    });

    // --- Limpa alerta ao digitar em qualquer campo de ambos os forms ---
    [formCadastro, formSenha].forEach(form => {
        Array.from(form.elements).forEach(el => {
            el.addEventListener('input', () => {
                const area = document.getElementById('alertas-area');
                if (area) {
                    area.innerHTML = '';
                    area.classList.add('oculto');
                }
            });
        });
    });
});
