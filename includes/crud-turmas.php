<div class="form-group">
	<h2 id="tituloFormTurma">Carregando...</h2>
    <p id="subtituloFormTurma">Carregando...</p>
	<form action="" method="post" enctype="multipart/form-data" id="turmaForm">
		<input type="hidden" name="id-turma" id="id-turma">
			<div class="form-group">
				<label for="nome">Nome da Turma</label>
				<input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: 3º Ano A - Matutino" required>
			</div>
                    
			<div class="form-actions">
				<button type="submit" class="btn btn-primary" id="btnSalvarTurma">
					<i class="fas fa-save"></i> Salvar Turma
				</button>
				<button type="button" class="btn btn-cancelar" id="btnCancelar">
					<i class="fas fa-times"></i> Cancelar
				</button>
				<button type="button" class="btn btn-primary oculto" id="btnEditarTurma">
					<i class="fas fa-edit"></i> Editar Turma
				</button>
			</div>

    </form>
</div>