<div class="form-group">
			    <div class="form-group">
        <h2 id="tituloFormMaterial">Enviar Novo Material</h2>
        <p id="subtituloFormMaterial">Altere os campos abaixo</p>
        <form action="" method="post" enctype="multipart/form-data" id="crudMaterialForm" autocomplete="off">
            <input type="hidden" name="id-material" id="id-material">

            <div class="form-group">
                <label for="turma">Turma</label>
                <select name="turma" id="turma" class="form-control" required>
                    <option value="">Selecione uma turma</option>
                         <option value="">Selecione uma matéria</option>
                     </select>
            </div>
            <div class="form-group">
                <label for="materia">Matéria</label>
                <select name="materia" id="materia" class="form-control" required>
                    <option value="">Selecione uma matéria</option>
                            <option value="">Selecione uma matéria</option>
                    </select>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição do Material</label>
                <input type="text" name="descricao" id="descricao" class="form-control" required
                    placeholder="Ex: Aula sobre frações, Lista de exercícios, etc.">
            </div>
            <div class="form-group" id="grupoArquivo">
                <label for="arquivo">Arquivo</label>
                <div id="arquivoAtual"></div>
                <input type="file" name="arquivo" id="arquivo" class="form-control">
                <small>Formatos aceitos: PDF, DOC, DOCX, ...</small>
            </div>
            <div class="form-actions" style="margin-top:10px;">
                <button type="submit" class="btn btn-primary" id="btnSalvarMaterial">
                    <i class="fas fa-upload"></i> Salvar
                </button>
                <button type="button" class="btn btn-editar oculto" id="btnEditarMaterial">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-cancelar" id="btnCancelarMaterial">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </form>
    </div>
</div>