document.addEventListener('DOMContentLoaded', function () {
    const botaoImprimir = document.getElementById('btnImprimir');
    if (!botaoImprimir) return;

    botaoImprimir.addEventListener('click', function(e){
        e.preventDefault();

        var divExport = document.getElementById('plano-final');
        if (!divExport) {
            alert('Bloco para impressão não encontrado!');
            return;
        }

        // Copia o HTML da div
        var conteudo = divExport.innerHTML;

        // Abre janela temporária
        var janela = window.open('', '', 'height=900,width=1200');

        // Escreve conteúdo com estilos básicos (você pode incluir mais CSS se quiser)
        janela.document.write('<html><head><title>Impressão do Planejamento</title>');
        // Opcional: puxe um CSS para manter o layout básico
        janela.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        janela.document.write('</head><body>');
        janela.document.write(conteudo);
        janela.document.write('</body></html>');

        janela.document.close(); // Necessário para o IE!
        janela.focus();

        // Aguarda carregar e imprime
        janela.onload = function() {
            janela.print();
            janela.close();
        };
    });
});
