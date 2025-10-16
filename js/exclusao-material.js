// Função para confirmar exclusão
function confirmarExclusao(id) {
	if (confirm('Tem certeza que deseja excluir este material?')) {
		window.location.href = 'excluir-material.php?id=' + id;
	}
}