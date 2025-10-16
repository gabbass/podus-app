<?php

/********************************************************************
 *  Visualização do Planejamento Mensal
 *  Estrutura:
 *   ├─ planejamento           (tabela-mãe / cabeçalho)
 *   └─ planejamento_linhas    (tabela-filha / linhas)
 *       └─ id_planejamento    (FK → planejamento.id)
 *
 *  Autor: Universo Correções Rápidas
 *  Data : 29-07-2025
 ********************************************************************/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ─── Dependências ─────────────────────────────────────────────── */
require_once __DIR__ . '/sessao-adm-professor.php';
require_once __DIR__ . '/conexao.php';        // conexão principal
require_once __DIR__ . '/conexao-bncc.php';   // conexão BNCC

/* ─── CONFIGURAÇÕES AJUSTÁVEIS ─────────────────────────────────── */
const FK_LINHAS_PLANEJAMENTO = 'planejamento'; // ← troque se estiver diferente

/* ─── Validação do parâmetro id ───────────────────────────────── */
$idPlanejamento = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$idPlanejamento) {
    exit('<div class="alert alert-danger">ID do planejamento não informado.</div>');
}

/* ─── 1. Cabeçalho do planejamento ────────────────────────────── */
$sqlCab = "
    SELECT p.*,
           per.nome            AS nome_periodo,
           per.quantidadeMeses AS qtde_meses
      FROM planejamento           AS p
      JOIN planejamento_periodos  AS per ON per.id = p.tempo
     WHERE p.id = ?
     LIMIT 1
";
$stmtCab = $conexao->prepare($sqlCab);
$stmtCab->execute([$idPlanejamento]);
$plano = $stmtCab->fetch(PDO::FETCH_ASSOC);

if (!$plano) {
    exit('<div class="alert alert-warning">Planejamento não encontrado.</div>');
}

/* ─── 2. Linhas detalhadas ────────────────────────────────────── */
$sqlLin = "
    SELECT *
      FROM planejamento_linhas
     WHERE " . FK_LINHAS_PLANEJAMENTO . " = ?
  ORDER BY COALESCE(grupo,0), id
";
$stmtLin = $conexao->prepare($sqlLin);
$stmtLin->execute([$idPlanejamento]);
$linhas = $stmtLin->fetchAll(PDO::FETCH_ASSOC);

/* ─── 3. Funções utilitárias ──────────────────────────────────── */

/**
 * Converte (índice, tipo de período) → título do grupo.
 * Para 'Único' retorna string vazia.
 */
function nomeDoGrupo(int $idx, string $periodo): string
{
    $n = $idx + 1; // 0→1, 1→2 …
    switch ($periodo) {
        case 'Único':      return '';
        case 'Bimestral':  return "Bimestre {$n}";
        case 'Trimestral': return "Trimestre {$n}";
        case 'Semestral':  return "Semestre {$n}";
        case 'Anual':
            $meses = [
                'Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'
            ];
            return $meses[$idx] ?? "Mês {$n}";
        default:           return "Período {$n}";
    }
}

/**
 * Busca um campo em qualquer tabela BNCC.
 */
function bncc(PDO $cx, string $tabela, $id, string $campo = 'nome'): string
{
    if (!$id) return '';
    $s = $cx->prepare("SELECT * FROM {$tabela} WHERE id = ?");
    $s->execute([$id]);
    $d = $s->fetch(PDO::FETCH_ASSOC);
    return $d[$campo] ?? $d['descricao'] ?? $d['codigo'] ?? '';
}

/* ─── HTML ────────────────────────────────────────────────────── */
$tituloPagina = 'Planejamento – Visualizar';
include __DIR__ . '/includes/head.php';
?>
<body>
<?php include __DIR__ . '/includes/menu.php'; ?>

<div class="main-content" id="main-content">
<?php include __DIR__ . '/includes/cabecalho.php'; ?>

<div class="content-container" id="content-container">
<div class="container" id="planejamentoVisualizacao">

    <div class="page-header d-flex justify-content-between align-items-center">
        <h1>Visualizar Planejamento</h1>

        <div class="btn-group">
		 <a id="voltar" class="btn btn-voltar" href="planejador-mensal.php">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            <a id="btnExportarDoc" class="btn btn-primary" href="#">
                <i class="fas fa-file"></i> Baixar DOC
            </a>
            <a id="btnImprimir" class="btn btn-secondary" href="#">
                <i class="fas fa-print"></i> Imprimir
            </a>
        </div>
    </div>

    <!-- Cabeçalho do planejamento -->
	
<div id="plano-final">	
<table class="table table-bordered mb-4" id="tblCabecalhoPlanejamento">
    <tbody>
	
        <tr><th>Nome</th> <td><?= htmlspecialchars($plano['nome'] ?? '') ?></td></tr>
		
        <tr><th>Tipo</th>
            <td><?= htmlspecialchars($plano['nome_periodo'] ?? '') ?></td></tr>
			
        <tr><th>Número de aulas semanais</th>
            <td><?= htmlspecialchars($plano['numeroDeAulas'] ?? '') ?></td></tr>

        <tr><th>Escola</th>
            <td><?= htmlspecialchars($plano['escola'] ?? '') ?></td></tr>

        <tr><th>Professor</th>
            <td><?= htmlspecialchars($_SESSION['nome'] ?? '') ?></td></tr>

        <tr><th>Período</th>
            <td><?= htmlspecialchars($plano['periodo'] ?? '') ?></td></tr>

        <tr><th>Ano</th>
            <td><?= htmlspecialchars($plano['anosDoPlano'] ?? '') ?></td></tr>

        <tr><th>Objetivo geral</th>
            <td><?= nl2br(htmlspecialchars($plano['objetivoGeral'] ?? '')) ?></td></tr>

        <tr><th>Objetivo específico</th>
            <td><?= nl2br(htmlspecialchars($plano['objetivoEspecifico'] ?? '')) ?></td></tr>
    </tbody>
</table>

    <?php if (!$linhas): ?>
        <div class="alert alert-info">Nenhuma linha cadastrada para este planejamento.</div>
    <?php else: ?>
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ano(s)</th>
                    <th>Área do conhecimento</th>
                    <th>Componente curricular</th>
                    <th>Unidade temática</th>
                    <th>Objeto do conhecimento</th>
					<th>Conteúdos</th>
                    <th>Habilidades</th>
                    <th>Metodologias</th>
                    <th>Etapa</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $grupoAtual   = null;
                $indiceGrupo  = -1;

                foreach ($linhas as $l) {
                    $grp = $l['grupo'] ?? null;
                    if ($grp !== $grupoAtual) {
                        $grupoAtual  = $grp;
                        $indiceGrupo++;

                        $tituloGrupo = nomeDoGrupo($indiceGrupo, $plano['nome_periodo']);
                        if ($tituloGrupo !== '') {
                            echo '<tr class="table-primary">
                                    <th id="grupo_' . $indiceGrupo . '" colspan="9">'
                                  . htmlspecialchars($tituloGrupo) .
                                  '</th>
                                  </tr>';
                        }
                    }
            ?>
                <tr id="linha-<?= $l['id'] ?>">
                    <td><?= bncc($conexao_bncc,'bncc_anos',$l['ano'],'ano') ?></td>
                    <td><?= bncc($conexao_bncc,'bncc_areas',$l['areaConhecimento'],'nome') ?></td>
                    <td><?= bncc($conexao_bncc,'bncc_componentes',$l['componenteCurricular'],'nome') ?></td>
                    <td><?= bncc($conexao_bncc,'bncc_unidades_tematicas',$l['unidadeTematicas'],'nome') ?></td>
                    <td><?= bncc($conexao_bncc,'bncc_objetos_conhecimento',$l['objetosConhecimento'],'nome') ?></td>
					<td><?= nl2br(htmlspecialchars($l['conteudos'])) ?></td>
                    <td>
                        <?php
                        $idsHab = array_filter(array_map('trim', explode(',', $l['habilidades'])));
                        $outHab = [];
                        foreach ($idsHab as $hab) {
                            $cod = bncc($conexao_bncc,'bncc_habilidades',$hab,'codigo');
                            $des = bncc($conexao_bncc,'bncc_habilidades',$hab,'descricao');
                            $outHab[] = "<strong>{$cod}</strong>: {$des}";
                        }
                        echo implode('<br>', $outHab);
                        ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($l['metodologias'])) ?></td>
                    <td><?= bncc($conexao_bncc,'bncc_etapas',$l['etapa'],'nome') ?></td>
                </tr>
            <?php } // foreach ?>
            </tbody>
        </table>
    <?php endif; ?>
</div><!-- /#plano-final -->
</div><!-- /.container -->
</div><!-- /.content-container -->
</div><!-- /.main-content -->

<?php
include __DIR__ . '/includes/rodape.php';
include __DIR__ . '/includes/modal-geral.php';
?>
<script src="js/gerar-doc.js"></script>
<script src="js/imprimir-div.js"></script>
<?php include __DIR__ . '/includes/foot.php'; ?>
</body>
</html>

<?php if (isset($_GET['imprimir']) && $_GET['imprimir'] == '1'): ?>
<script>
window.addEventListener('load', function () {
    const bloco = document.getElementById('plano-final');
    if (!bloco) return alert('Bloco de impressão não encontrado (#plano-final).');

    const janela = window.open('', '', 'width=1024,height=768');
    janela.document.write(`
        <html>
        <head>
            <title>Impressão do Planejamento</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #444; padding: 8px; vertical-align: top; }
                th { background-color: #f0f0f0; }
                h1, h2, h3 { margin-top: 20px; }
            </style>
        </head>
        <body>
            ${bloco.innerHTML}
        </body>
        </html>
    `);
    janela.document.close();
    janela.focus();
    janela.print();
    janela.close();
});
</script>
<?php endif; ?>
