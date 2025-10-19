<?php
if (!isset($gid)) {
  $gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
}
?>

<div class="bloco-linha content-container sub-container" data-grupo="<?= $gid ?>">
  <!-- Cabeçalho do bloco: título e botão de criar linha -->
  <div class="page-header" id="bloco-header-<?= $gid ?>">
    <div class="page-title">
      <h3 id="tituloAddLinhaMensal-<?= $gid ?>">Adicionar temas</h3>
      <p id="subtituloAddLinhaMensal-<?= $gid ?>">Para preencher as informações, clique em Criar nova linha</p>
    </div>
    <div class="btn-group">
      <button type="button"
              class="btn btn-secondary"
              id="btnAdicionarLinha-<?= $gid ?>">
        <i class="fas fa-plus"></i> Criar nova linha
      </button>
    </div>
  </div>

  <!-- Tabela de linhas já adicionadas neste bloco -->
  <div class="linhas-adicionadas sub-container mb-4" id="bloco-tabela-<?= $gid ?>">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Etapa/Ano</th>
          <th>Área</th>
          <th>Componente</th>
          <th>Unidade Temática</th>
          <th>Objeto Conhecimento</th>
          <th>Habilidades</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody id="tbody-linhas-planejamento-<?= $gid ?>">
      <!-- preenchido via JS -->
    </tbody>
    </table>
  </div>

  <!-- Formulário de adição/edição de linha -->
  <div id="form-linha-bncc-<?= $gid ?>" class="adicionar-linhas oculto">
    <h4>Crie uma linha com base na BNCC</h4>
    <p>Busque as informações da base para acrescentar uma linha em seu plano mensal</p>

    <!-- ► ETAPA -------------------------------------------------- -->
    <div class="form-group" id="grupo-etapa-<?= $gid ?>">
      <label for="etapa-linha-<?= $gid ?>">Etapa <span class="required">*</span></label>
      <select id="etapa-linha-<?= $gid ?>" name="etapa-linha-<?= $gid ?>">
        <option value="" disabled selected>Selecione a etapa</option>
      </select>
    </div>

    <!-- ► ANO ---------------------------------------------------- -->
    <div class="form-group campo-bncc-sequencial oculto destaque"
         id="grupo-ano-<?= $gid ?>">
      <label for="ano-linha-<?= $gid ?>">Ano <span class="required">*</span></label>
      <select id="ano-linha-<?= $gid ?>" name="ano-linha-<?= $gid ?>">
        <option value="" disabled selected>Selecione a etapa primeiro</option>
      </select>
    </div>

    <!-- ► ÁREA --------------------------------------------------- -->
    <div class="form-group campo-bncc-sequencial oculto"
         id="grupo-area-<?= $gid ?>">
      <label for="area-linha-<?= $gid ?>">Área de Conhecimento</label>
      <select id="area-linha-<?= $gid ?>" name="area-linha-<?= $gid ?>"></select>
    </div>

    <!-- ► COMPONENTE --------------------------------------------- -->
    <div class="form-group campo-bncc-sequencial oculto"
         id="grupo-componente-<?= $gid ?>">
      <label for="componente-linha-<?= $gid ?>">Componente Curricular</label>
      <select id="componente-linha-<?= $gid ?>" name="componente-linha-<?= $gid ?>"></select>
    </div>

    <!-- ► UNIDADE TEMÁTICA --------------------------------------- -->
    <div class="form-group campo-bncc-sequencial oculto destaque"
         id="grupo-unidade-<?= $gid ?>">
      <label for="unidadeTematica-linha-<?= $gid ?>">Unidade Temática</label>
      <select id="unidadeTematica-linha-<?= $gid ?>" name="unidadeTematica-linha-<?= $gid ?>"></select>
    </div>

    <!-- ► OBJETO DO CONHECIMENTO -------------------------------- -->
    <div class="form-group campo-bncc-sequencial oculto"
         id="grupo-objetos-<?= $gid ?>">
      <label for="objetosConhecimento-linha-<?= $gid ?>">Objeto do Conhecimento</label>
      <select id="objetosConhecimento-linha-<?= $gid ?>" name="objetosConhecimento-linha-<?= $gid ?>"></select>
    </div>

    <!-- ► HABILIDADES ------------------------------------------- -->
    <div class="form-group campo-bncc-sequencial oculto"
         id="grupo-habilidades-<?= $gid ?>">
      <label for="habilidades-linha-<?= $gid ?>">Habilidades</label>
      <select id="habilidades-linha-<?= $gid ?>" name="habilidades-linha-<?= $gid ?>[]" multiple></select>
    </div>

    <hr>
    <h4>Detalhes</h4>
    <p>Preencha mais informações para o seu plano</p>

    <!-- Conteúdos -->
    <div class="form-group">
      <label for="conteudos-linha-<?= $gid ?>">Conteúdos</label>
      <textarea id="conteudos-linha-<?= $gid ?>" name="conteudos-linha-<?= $gid ?>"></textarea>
      <button type="button"
              class="btn btn-primary"
              id="btnSugestao-conteudos-linha-<?= $gid ?>"
              data-type="conteudos_linha"
              data-bloco="<?= $gid ?>">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Sugestão de conteúdos
      </button>
    </div>

    <!-- Metodologias -->
    <div class="form-group">
      <label for="metodologias-linha-<?= $gid ?>">Metodologias</label>
      <textarea id="metodologias-linha-<?= $gid ?>" name="metodologias-linha-<?= $gid ?>"></textarea>
      <button type="button"
              class="btn btn-primary"
              id="btnSugestao-metodologias-linha-<?= $gid ?>"
              data-type="metodologias_linha"
              data-bloco="<?= $gid ?>">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Sugestão de metodologias
      </button>
    </div>

    <!-- Botões de ação -->
    <div class="form-actions">
      <button type="button"
              class="btn btn-primary"
              id="btnSalvarLinha-<?= $gid ?>">
        <i class="fas fa-save"></i> Salvar Linha
      </button>
      <button type="button"
              class="btn btn-cancelar"
              id="btnCancelarLinha-<?= $gid ?>">
        <i class="fas fa-times"></i> Cancelar
      </button>
    </div>
  </div>
</div>
