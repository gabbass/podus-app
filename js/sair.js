document.addEventListener('DOMContentLoaded', function() {
    let btnSair = document.getElementById('btn-sair');
    if (btnSair) {
        btnSair.addEventListener('click', function(e) {
            e.preventDefault();
            abrirConfirmacaoSair();
        });
    }
});

function abrirConfirmacaoSair() {
    document.getElementById('modalGeralLabel').innerText = "Sair do Sistema";
    document.getElementById('modalGeralBody').innerText = "Deseja realmente sair do sistema?";
    document.getElementById('modalGeralConfirmar').onclick = confirmarSairAJAX;
    let modal = new bootstrap.Modal(document.getElementById('modalGeral'));
    modal.show();
}

function confirmarSairAJAX() {
    fetch('sair.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        window.location.href = 'sair.php';
    })
    .catch(() => {
        alert('Erro ao tentar sair!');
        fecharModalGeral();
    });
}

function fecharModalGeral() {
    let modal = bootstrap.Modal.getInstance(document.getElementById('modalGeral'));
    if (modal) modal.hide();
}
