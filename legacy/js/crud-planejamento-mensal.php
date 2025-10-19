<head>
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- jQuery e Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <!-- Choices.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</head>

<body>
<h2 id="tituloFormPlanejamentoMensal">Carregando...</h2>
<p id="subtituloFormPlanejamentoMensal">Carregando...</p>

<div class="form-group">
  <label for="tempo">Tipo de Ciclo<span class="required">*</span></label>
  <p>Escolha o tipo de ciclo deste planejamento. Exemplo: Mensal, Bimestral, etc.</p>
  <select id="tempo" name="tempo" required>
    <option value="" disabled selected>Selecione o tipo de ciclo</option>
  </select>
</div>

<div id="formPlanejamentoWrapper">
  <div>
    <form id="crudPlanejamentoMensalForm" method="POST" action="" autocomplete="off">
      <h3>Informações iniciais</h3>
      <p>Estas informações ficam no começo do seu plano</p>
      <input type="hidden" id="id-planejamento-mensal" name="id-planejamento-mensal">
      <input type="hidden" id="linhas-planejamento" name="linhas_serializadas" value=[]>
	  

      <div class="form-group">
        <label for="nome-plano-mensal">Nome<span class="required">*</span></label>
        <p>Escolha um título da sua preferência para este plano</p>
        <input type="text" id="nome-plano-mensal" name="nome-plano-mensal" required>
      </div>

      <div class="form-group oculto">
        <label for="professor">Professor <span class="required">*</span></label>
        <input type="text" id="professor" name="professor" readonly>
      </div>

      <div class="form-group">
        <label for="materia">Matéria<span class="required">*</span></label>
        <p>Escolha a matéria que deve fazer o plano. Lembre-se de utilizar uma correspondente na parte de BNCC.</p>
        <select id="materia" name="materia" required>
			<option value="" disabled selected>Selecione a matéria</option>
        </select>
      </div>

      <div class="form-group">
        <label for="periodo_realizacao">Período de Realização</label>
        <p>Escolha o período que você deseja que este plano cumpra. Preferencialmente mensal.</p>
        <input type="text" id="periodo_realizacao" name="periodo_realizacao" placeholder="Ex: 01/03/2023 a 30/03/2023">
      </div>

      <div class="form-group">
        <label for="numero_aulas_semanais">Número de Aulas Semanais <span class="required">*</span></label>
        <p>Escolha a quantidade de aulas semanais</p>
        <input type="number" id="numero_aulas_semanais" name="numero_aulas_semanais" required min="1" max="40" step="1">
      </div>

      <div class="form-group">
        <label for="anos-plano">Anos do plano</label>
        <p>Escolha em que anos este plano corresponde. Lembre-se de utilizar uma correspondente na parte de BNCC.</p>
        <select id="anos-plano" name="anos_plano[]" multiple>
          <option value="" disabled selected>Selecione a etapa primeiro</option>
          <option value="1º">1º</option>
          <option value="2º">2º</option>
          <option value="3º">3º</option>
          <option value="4º">4º</option>
          <option value="5º">5º</option>
          <option value="6º">6º</option>
          <option value="7º">7º</option>
          <option value="8º">8º</option>
          <option value="9º">9º</option>
        </select>
		<!--
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="checkTodosAnos">
          <label class="form-check-label" for="checkTodosAnos">
            Este plano valerá para mais de um ano.
          </label>
        </div>-->
      </div>

      <div class="form-group">
        <label for="objetivo_geral">Objetivo Geral</label>
        <textarea id="objetivo_geral" name="objetivo_geral" placeholder="Descreva o objetivo geral do planejamento..."></textarea>
		<!--
        <button type="button" class="btn btn-primary btnSugestao" data-type="objetivo_geral">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Sugestão de objetivo geral
        </button>-->
      </div>

      <div class="form-group">
        <label for="objetivo_especifico">Objetivo Específico</label>
        <textarea id="objetivo_especifico" name="objetivo_especifico" placeholder="Descreva os objetivos específicos..."></textarea>
		<!--
        <button type="button" class="btn btn-primary btnSugestao" data-type="objetivo_especifico">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Sugestão de objetivo específico
        </button>-->
      </div>
	</div>
	<!-- Ações adicionar linhas -->
		<div class="container" id="blocos-planejamento"></div>
      <!-- Ações finais -->
       <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Salvar Planejamento
        </button>
        <button type="button" id="btnCancelarTudo" class="btn btn-cancelar">
          <i class="fas fa-times"></i> Cancelar
        </button>
      </div>
    </form>
</div>
</body>
