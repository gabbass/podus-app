document.addEventListener('DOMContentLoaded', async () => {
  const url = new URLSearchParams(window.location.search);
  const id = url.get('id');
  const de = url.get('de');
  const id_aluno = url.get('id_aluno');

  if (!id || !de) return;

  const destino = (de === 'notas') ? 'resposta-notas' : 'resposta-prova';
  const divDestino = document.getElementById(destino);

  try {
    const resposta = await fetch(`includes/ajax/carregar-prova.php?id=${id}&de=${de}&id_aluno=${id_aluno ?? ''}`);
    const html = await resposta.text();
    divDestino.innerHTML = html;
    divDestino.classList.remove('oculto');
  } catch (e) {
    console.error('Erro ao carregar prova:', e);
  }
});
