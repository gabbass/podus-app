document.addEventListener("click", async function (event) {
    const botao = event.target.closest(".btnSugestao");
    if (!botao) return;

    const tipo = botao.getAttribute("data-type");

    const mapaTarget = {
        "conteudos_linha": "#conteudos-linha",
        "metodologias_linha": "#metodologias-linha"
    };

    const textareaSelector = mapaTarget[tipo];
    if (!textareaSelector) {
        console.error("Tipo de sugestão desconhecido:", tipo);
        return;
    }

    const etapa = document.getElementById("etapa-linha")?.value || "";
    const ano = document.getElementById("ano-linha")?.value || "";
    const area = document.getElementById("area-linha")?.selectedOptions[0]?.textContent || "";
    const componente = document.getElementById("componente-linha")?.selectedOptions[0]?.textContent || "";
    const unidade = document.getElementById("unidadeTematica-linha")?.selectedOptions[0]?.textContent || "";
    const objeto = document.getElementById("objetosConhecimento-linha")?.selectedOptions[0]?.textContent || "";
    const habilidades = Array.from(document.getElementById("habilidades-linha")?.selectedOptions || []).map(opt => opt.textContent).join(", ");

    // 🔁 Prompt dinâmico
    let prompt = "";
    if (tipo === "conteudos_linha") {
        prompt = `Você é um assistente pedagógico.
Com base nas seguintes informações, sugira uma lista de conteúdos:

Etapa: ${etapa}
Ano: ${ano}
Área de conhecimento: ${area}
Componente curricular: ${componente}
Unidade temática: ${unidade}
Objeto do conhecimento: ${objeto}
Habilidades envolvidas: ${habilidades}

Sua resposta deve conter apenas os conteúdos sugeridos para esse contexto e se possível, as fontes em formato abnt no final`;
    } else if (tipo === "metodologias_linha") {
        prompt = `Você é um especialista em didática.
Com base nas informações a seguir, sugira metodologias ativas e estratégias de ensino adequadas ao contexto:

Etapa: ${etapa}
Ano: ${ano}
Área de conhecimento: ${area}
Componente curricular: ${componente}
Unidade temática: ${unidade}
Objeto do conhecimento: ${objeto}
Habilidades envolvidas: ${habilidades}

A resposta deve conter apenas metodologias aplicáveis a esse plano de aula.`;
    } else {
        alert("Tipo de sugestão não suportado.");
        return;
    }

    botao.disabled = true;
    const icone = botao.querySelector("i");
    const originalClass = icone?.className;
    if (icone) icone.className = "fas fa-spinner fa-spin";

    try {
        const res = await fetch("/portal/ai/includes/openai-handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ prompt })
        });

        const json = await res.json();
        if (json.sucesso) {
            const resposta = json.resposta.trim();
            console.log("Resposta IA:", resposta);

            // Aplica ao summernote correspondente
            if ($(textareaSelector).length > 0) {
                $(textareaSelector).summernote('reset');
                $(textareaSelector).summernote('code', resposta);
            } else {
                console.warn("Campo Summernote não encontrado:", textareaSelector);
            }
        } else {
            console.error(json);
            alert("Erro ao processar resposta.");
        }
    } catch (erro) {
        console.error(erro);
        alert("Erro de conexão com a IA.");
    } finally {
        botao.disabled = false;
        if (icone) icone.className = originalClass;
    }
});
