document.addEventListener('DOMContentLoaded', async function () {
  const urlParams = new URLSearchParams(window.location.search);
  const de = urlParams.get('de');
  const id = urlParams.get('id');
  const idAluno = urlParams.get('id_aluno');
  const grupoBotoes = document.querySelector('.btn-group');
  const botaoOriginal = document.getElementById('btnNovaProva');

  // Mostrar conteúdo da prova (de=provas)
  if (de === 'provas') {
    fetch(`includes/carregar-prova.php?id=${id}&de=provas`)
      .then(resp => resp.text())
      .then(html => {
        const div = document.getElementById('resposta-prova');
        div.innerHTML = html;
        div.classList.remove('oculto');
        document.getElementById('resposta-notas')?.classList.add('oculto');
      });
    return;
  }

  // Mostrar tentativas (de=notas)
  if (de === 'notas' && idAluno && id) {
    if (botaoOriginal) botaoOriginal.remove();

    let maiorTentativa = 0;

    try {
      const resp = await fetch(`includes/buscar-tentativa-aluno.php?id_prova=${id}&id_aluno=${idAluno}`);
      const dados = await resp.json();
      maiorTentativa = parseInt(dados.maiorTentativa || 0);
    } catch (e) {
      console.warn("Falha ao obter tentativa. Usando modo legado.");
    }

    // Gerar botões somente se houver tentativas 1-3
    if (maiorTentativa >= 1 && maiorTentativa <= 3) {
      for (let i = 1; i <= maiorTentativa; i++) {
        const btn = document.createElement('button');
        btn.className = 'btn btn-primary me-2';
        btn.innerHTML = `<i class="fa-solid fa-file"></i> Tentativa ${i}`;
        btn.dataset.tentativa = i;
        btn.addEventListener('click', () => carregarTentativa(i));
        grupoBotoes.appendChild(btn);
      }
    }

    // Carrega a tentativa mais alta ou modo legado
    carregarTentativa(maiorTentativa || null);
  }
});

function carregarTentativa(tentativa = null) {
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get('id');
  const idAluno = urlParams.get('id_aluno');
  const de = urlParams.get('de');

  if (!id || !de || !idAluno) return;

  const url = `includes/carregar-prova.php?id=${id}&de=${de}&id_aluno=${idAluno}` +
              (tentativa ? `&tentativa=${tentativa}` : '');

  fetch(url)
    .then(resp => resp.text())
    .then(html => {
      const alvo = document.getElementById('resposta-notas');
      alvo.innerHTML = html;
      alvo.classList.remove('oculto');
      document.getElementById('resposta-prova')?.classList.add('oculto');
    })
    .catch(erro => {
      console.error('Erro ao carregar tentativa:', erro);
      alert('Falha ao carregar tentativa. Verifique a conexão.');
    });
}
