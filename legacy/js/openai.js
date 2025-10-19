function enviarPrompt() {
    const prompt = document.getElementById('promptTexto').value;
    if (!prompt.trim()) {
        alert("Escreva algo para enviar à IA.");
        return;
    }

    fetch('ai/includes/openai-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            document.getElementById('respostaIA').innerText = data.resposta;
        } else {
            alert("Erro: " + (data.erro || "Resposta inesperada"));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert("Erro na requisição");
    });
}
