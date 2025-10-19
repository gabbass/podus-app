document.addEventListener('DOMContentLoaded', function () {
    const botaoExportar = document.getElementById('btnExportarDoc');
    if (!botaoExportar) {
        alert('Botão de exportação (ID "btnExportarDoc") não encontrado.');
        return;
    }

    let id = '';
    const inputId = document.getElementById('id-planejamento-mensal');
    if (inputId && inputId.value) {
        id = inputId.value;
    } else {
        const params = new URLSearchParams(window.location.search);
        id = params.get('id') || '';
    }

    if (!id) {
        alert('ID do planejamento ausente. Verifique se há um <input type="hidden" id="id-planejamento-mensal" /> ou parâmetro "id" na URL.');
        return;
    }

    botaoExportar.addEventListener('click', function (e) {
        e.preventDefault();
        const url = `/portal/includes/exp_docx_direto.php?id=${encodeURIComponent(id)}`;
        window.location.href = url;
    });
});
