async function gerarPlanejador(btn) {
  const id = btn.dataset.id;
  if (!id) return alert('ID ausente');

  const url = `/portal/includes/exp_docx_direto.php?id=${id}`;

  try {
    const resp = await fetch(url);
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    const blob = await resp.blob();

    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `planejamento_${id}.docx`;
    document.body.appendChild(a);
    a.click();
    URL.revokeObjectURL(a.href);
    document.body.removeChild(a);
  } catch (e) {
    console.error(e);
    alert('Falha ao gerar documento. Tente novamente.');
  }
}
