document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnGerarProva');
  if (!btn) return;

  btn.addEventListener('click', () => {
    if (questoesSelecionadas.size < 2) {
      alert('Selecione pelo menos 2 questões.');
      return;
    }

    const ids = Array.from(questoesSelecionadas);
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'includes/gerar-prova-imp.php';
    form.target = '_blank';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids';
    input.value = JSON.stringify(ids);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  });
});
