// js/ia-questoes.js
;(function(){
  const IA_API_URL = '/portal/ai/gerar-questao.php';

  // utilitário para pegar elemento por id
  function dom(id){
    return document.getElementById(id);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const btn = dom('btnGerarIA');
    if (!btn) return;

    // só exibe em criar
    if (typeof modoCRUD === 'undefined' || modoCRUD !== 'criar') {
      btn.classList.add('oculto');
    } else {
      btn.classList.remove('oculto');
    }

    btn.addEventListener('click', async () => {
      // validação prévia
      const mat = dom('materia').value.trim();
      const ass = dom('assunto').value.trim();
      const gra = dom('grau_escolar').value.trim();
      if (!mat || !ass || !gra) {
        mostrarAlerta(
          'Preencha os campos Matéria, Assunto e Nível de ensino antes de seguir',
          'danger'
        );
        return;
      }

      btn.disabled   = true;
      const origHTML = btn.innerHTML;
      btn.innerHTML  = '<i class="fa fa-spinner fa-spin"></i> Gerando...';

      try {
        const resp = await fetch(IA_API_URL, {
          method: 'POST',
          headers: { 'Content-Type':'application/json' },
          body: JSON.stringify({ materia: mat, assunto: ass, grau: gra })
        });
        if (!resp.ok) throw new Error('Status '+resp.status);
        const data = await resp.json();

        // repovoa matéria/assunto/grau se vier no JSON
        if (data.materia)  dom('materia').value      = data.materia;
        if (data.assunto)  dom('assunto').value      = data.assunto;
        if (data.grau)     dom('grau_escolar').value = data.grau;

        // enunciado
        $('#questao').summernote('code', data.enunciado || '');

        // alternativas
        ['A','B','C','D','E'].forEach(letter => {
          $(`#alternativa_${letter}`).summernote('code',
            data.alternativas?.[letter] || ''
          );
        });

        // resposta via Choices.js ou fallback
        if (typeof choicesResposta !== 'undefined') {
          const choices = ['A','B','C','D','E'].map(letter => ({
            value:    letter,
            label:    letter,
            selected: letter === (data.resposta||'').trim().toUpperCase()
          }));
          choicesResposta.setChoices(choices, 'value', 'label', true);
        } else {
          const sel = dom('resposta');
          sel.innerHTML = '';
          ['A','B','C','D','E'].forEach(letter => {
            const o = document.createElement('option');
            o.value = letter;
            o.textContent = letter;
            if (letter === (data.resposta||'').trim().toUpperCase()) {
              o.selected = true;
            }
            sel.appendChild(o);
          });
        }

        // justificativa
        $('#justificativa').summernote('code', data.justificativa || '');

        // fonte ABNT
        if (data.fonte) dom('fonte').value = data.fonte;

        // autor + "- Criada com Inteligência Artificial"
        const autorEl = dom('autor');
        autorEl.value = autorEl.value.replace(/- Criada com Inteligência Artificial$/,'') + '- Criada com Inteligência Artificial';

        // ** agora marca a div da alternativa correta **
        const correta = (data.resposta||'').trim().toUpperCase();
        ['A','B','C','D','E'].forEach(letter => {
          const wrap = dom('wrapper_alternativa_' + letter);
          if (wrap) wrap.classList.remove('selecionada');
        });
        const wrapCorreta = dom('wrapper_alternativa_' + correta);
        if (wrapCorreta) wrapCorreta.classList.add('selecionada');

      } catch (e) {
        console.error(e);
        mostrarAlerta('Erro ao gerar questão: ' + e.message, 'danger');
      } finally {
        btn.disabled  = false;
        btn.innerHTML = origHTML;
      }
    });

    // se existe setModoCRUD, integra show/hide do botão
    if (typeof setModoCRUD === 'function') {
      const orig = setModoCRUD;
      window.setModoCRUD = mode => {
        orig(mode);
        btn.classList.toggle('oculto', mode !== 'criar');
      };
    }
  });
})();
