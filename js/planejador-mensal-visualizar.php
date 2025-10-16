<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/sessao-professor.php');
require_once(__DIR__ . '/conexao.php'); // conexão com portal_do_universo
require_once(__DIR__ . '/conexao-bncc.php'); // conexão com banco BNCC
$tituloPagina = "Planejamento Mensal - Visualizar - Universo do Saber";
include __DIR__ . '/includes/head.php';

// 1. Busca do planejamento principal
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='alert alert-danger'>ID do planejamento não informado.</div>";
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM planejamento_mensal WHERE id = ?");
$stmt->execute([$id]);
$planejamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$planejamento) {
    echo "<div class='alert alert-warning'>Planejamento não encontrado.</div>";
    exit;
}

// 2. Busca das linhas detalhadas do planejamento
$stmtLinhas = $conexao->prepare("SELECT * FROM planejamento_mensal_linhas WHERE planejamentoMensal = ? ORDER BY id ASC");
$stmtLinhas->execute([$id]);
$linhas = $stmtLinhas->fetchAll(PDO::FETCH_ASSOC);

// 3. Função utilitária para buscar nomes na BNCC
function buscarNomeBNCC($tabela, $id, $conexao_bncc, $campo_nome = 'nome') {
    if (!$id) return '';
    $stmt = $conexao_bncc->prepare("SELECT * FROM $tabela WHERE id = ?");
    $stmt->execute([$id]);
    $dado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $dado[$campo_nome] ?? $dado['descricao'] ?? $dado['codigo'] ?? '';
}

?>

<body>
<?php include __DIR__ . '/menu.php'; ?>
<div class="main-content" id="main-content">
    <?php include __DIR__ . '/cabecalho.php'; ?>
    <div class="content-container" id="content-container">
        <div class="container">
            <div class="page-header">
                <div class="page-title">
                    <h1>Visualizar planejamento mensal</h1>
                </div>
                <div class="btn-group">
                    <a href="#" class="btn btn-primary" id="btnExportarDoc">
                        <i class="fas fa-file"></i> Baixar doc
                    </a>
                    <a href="#" class="btn btn-secondary" id="btnImprimir">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                </div>
            </div>

            <div class="segundo-container">
                <div class="table-responsive" id="planejamentoVisualizacao">
                    <table class="table table-bordered">
                        <tbody>
							<tr><th>Nome</th><td><?= htmlspecialchars($planejamento['nome'] ?? '') ?></td></tr>
							<tr><th>Matéria</th><td><?= htmlspecialchars($planejamento['materia'] ?? '') ?></td></tr>
							<tr><th>Escola</th><td><?= htmlspecialchars($planejamento['escola'] ?? '') ?></td></tr>
							<tr><th>Período</th><td><?= htmlspecialchars($planejamento['periodo'] ?? '') ?></td></tr>
							<tr><th>Número de Aulas</th><td><?= htmlspecialchars($planejamento['numeroDeAulas'] ?? '') ?></td></tr>
							<tr><th>Anos do Plano</th><td><?= htmlspecialchars($planejamento['anosDoPlano'] ?? '') ?></td></tr>
							<tr><th>Objetivo Geral</th><td><?= $planejamento['objetivoGeral'] ?? '' ?></td></tr>
							<tr><th>Objetivo Específico</th><td><?= $planejamento['objetivoEspecifico'] ?? '' ?></td></tr>
							<tr><th>Criado em</th><td><?= htmlspecialchars($planejamento['created_date'] ?? '') ?></td></tr>
							<tr><th>Atualizado em</th><td><?= htmlspecialchars($planejamento['updated_date'] ?? '') ?></td></tr>

                        </tbody>
                    </table>

                    <?php if ($linhas): ?>
                        <h3>Linhas do Planejamento</h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                     <th>Ano(s)</th>
                                    <th>Área do Conhecimento</th>
                                    <th>Componente Curricular</th>
                                    <th>Unidade Temática</th>
                                    <th>Objeto do Conhecimento</th>
                                    <th>Habilidades</th>
                                    <th>Conteúdos</th>
                                    <th>Metodologias</th>
                                    <th>Etapa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linhas as $idx => $linha): ?>
                                    <tr>
                                         <td>
                                            <?= buscarNomeBNCC('bncc_anos', $linha['ano'], $conexao_bncc, 'ano') ?>
                                        </td>
                                        <td>
                                            <?= buscarNomeBNCC('bncc_areas', $linha['areaConhecimento'], $conexao_bncc, 'nome') ?>
                                        </td>
                                        <td>
                                            <?= buscarNomeBNCC('bncc_componentes', $linha['componenteCurricular'], $conexao_bncc, 'nome') ?>
                                        </td>
                                        <td>
                                            <?= buscarNomeBNCC('bncc_unidades_tematicas', $linha['unidadeTematicas'], $conexao_bncc, 'nome') ?>
                                        </td>
                                        <td>
                                            <?= buscarNomeBNCC('bncc_objetos_conhecimento', $linha['objetosConhecimento'], $conexao_bncc, 'nome') ?>
                                        </td>
                                        <td>
                                            <?php
                                            // Habilidades pode conter múltiplos IDs separados por vírgula
                                            $habilidades = array_filter(array_map('trim', explode(',', $linha['habilidades'])));
                                            $hab_textos = [];
                                            foreach ($habilidades as $hab_id) {
                                                $hab = buscarNomeBNCC('bncc_habilidades', $hab_id, $conexao_bncc, 'descricao');
                                                $codigo = buscarNomeBNCC('bncc_habilidades', $hab_id, $conexao_bncc, 'codigo');
                                                if ($codigo) {
                                                    $hab_textos[] = "<strong>{$codigo}</strong>: {$hab}";
                                                } else {
                                                    $hab_textos[] = $hab;
                                                }
                                            }
                                            echo implode('<br>', $hab_textos);
                                            ?>
                                        </td>
										<td><?= $linha['conteudos'] ?? '' ?></td>
										<td><?= $linha['metodologias'] ?? '' ?></td>
                                        <td>
                                            <?= buscarNomeBNCC('bncc_etapas', $linha['etapa'], $conexao_bncc, 'nome') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/rodape.php'; ?>
<?php include __DIR__ . '/includes/modal-geral.php'; ?>
<script src="js/gerar-doc.js"></script>
<script src="js/imprimir-div.js"></script>
</body>
<?php include __DIR__ . '/includes/foot.php'; ?>
