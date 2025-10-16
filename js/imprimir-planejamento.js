document.addEventListener('DOMContentLoaded', function () {
    const botao = document.getElementById('btnImprimir');
    if (!botao) return;

    botao.addEventListener('click', function (e) {
        e.preventDefault();

        const id = botao.getAttribute('data-id');
        if (!id) {
			mostrarAlerta('ID do planejamento não encontrado no atributo data-id.', 'danger');
             return;
        }

        const url = `/portal/planejador-mensal-visualizar.php?id=${encodeURIComponent(id)}&imprimir=1`;
        window.open(url, '_blank');
    });
});
