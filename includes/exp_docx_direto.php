<?php
/*********************************************************************
 *  portal/includes/exp_docx_direto.php
 *  Gera e baixa um DOCX do planejamento mensal (espelhando a visualização)
 *********************************************************************/

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/exp_docx_error.log');
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (in_array($errno, [E_DEPRECATED, E_USER_DEPRECATED])) return true;
    error_log("PHP [$errno] $errstr in $errfile:$errline");
    http_response_code(500);
    exit('Erro interno (log gerado).');
});
set_exception_handler(function($e){
    error_log("EXCEPTION: ".$e->getMessage()."\n".$e->getTraceAsString());
    http_response_code(500);
    exit('Erro interno (log gerado).');
});

require_once __DIR__ . '/../sessao-professor.php';
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conexao-bncc.php';

require_once __DIR__ . '/../phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// ─── Parâmetro ────────────────────────────────
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('ID inválido ou ausente.');
}

// ─── Cabeçalho do planejamento + período ───────
$stmt = $conexao->prepare("
    SELECT p.*, per.nome AS nome_periodo
    FROM planejamento p
    JOIN planejamento_periodos per ON per.id = p.tempo
    WHERE p.id = ? AND p.login = ?
");
$stmt->execute([$id, $_SESSION['id']]);
$plano = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plano) {
    http_response_code(404);
    exit('Planejamento não encontrado.');
}

// ─── Linhas do planejamento ────────────────────
$stmtLin = $conexao->prepare("
    SELECT * FROM planejamento_linhas
    WHERE planejamento = ?
    ORDER BY COALESCE(grupo,0), id
");
$stmtLin->execute([$id]);
$linhas = $stmtLin->fetchAll(PDO::FETCH_ASSOC);

// ─── Função BNCC ───────────────────────────────
function bncc(string $tbl, $id, PDO $cx, string $campo = 'nome'): string {
    if (!$id) return '';
    $s = $cx->prepare("SELECT * FROM $tbl WHERE id = ?");
    $s->execute([$id]);
    $d = $s->fetch(PDO::FETCH_ASSOC);
    return $d[$campo] ?? $d['descricao'] ?? $d['codigo'] ?? '';
}

// ─── Título dos grupos ─────────────────────────
function nomeDoGrupo(int $idx, string $tipo): string {
    $n = $idx + 1;
    switch ($tipo) {
        case 'Único': return '';
        case 'Bimestral': return "Bimestre {$n}";
        case 'Trimestral': return "Trimestre {$n}";
        case 'Semestral': return "Semestre {$n}";
        case 'Anual':
            $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                      'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
            return $meses[$idx] ?? "Mês {$n}";
        default: return "Período {$n}";
    }
}

// ─── Geração do DOCX ───────────────────────────
$phpWord = new PhpWord();
$section = $phpWord->addSection(['orientation' => 'landscape']);

// ─── Tabela de cabeçalho ───────────────────────
$tMain = $section->addTable([ 'width' => 100 * 50, 'borderSize' => 6, 'borderColor' => '777']);

$mapa = [
    'Nome'                => $plano['nome'] ?? '',
    'Professor'           => $_SESSION['nome'] ?? '',
    'Escola'              => $plano['escola'] ?? '',
    'Tipo'                => $plano['nome_periodo'] ?? '',
    'Número de aulas'     => $plano['numeroDeAulas'] ?? '',
    'Ano'                 => $plano['anosDoPlano'] ?? '',
    'Objetivo Geral'      => strip_tags($plano['objetivoGeral'] ?? ''),
    'Objetivo Específico' => strip_tags($plano['objetivoEspecifico'] ?? '')
];

foreach ($mapa as $rotulo => $valor) {
    $row = $tMain->addRow();
    $row->addCell(3000)->addText($rotulo, ['bold' => true]);
    $row->addCell(10000)->addText($valor);
}

$section->addTextBreak();

// ─── Tabela de grupos/linhas ───────────────────
$tDet = $section->addTable(['borderSize' => 6, 'borderColor' => '777']);

$cabecalhos = [
    'Ano(s)', 'Área do conhecimento', 'Componente curricular',
    'Unidade temática', 'Objeto do conhecimento',
    'Conteúdos', 'Habilidades', 'Metodologias', 'Etapa'
];

$headRow = $tDet->addRow();
foreach ($cabecalhos as $h) {
    $headRow->addCell(2000, ['bgColor' => 'D9EDF7'])->addText($h, ['bold' => true]);
}

// Agrupamento
$grupoAtual = null;
$indiceGrupo = -1;

foreach ($linhas as $ln) {
    $grp = $ln['grupo'] ?? null;
    if ($grp !== $grupoAtual) {
        $grupoAtual = $grp;
        $indiceGrupo++;

        $titulo = nomeDoGrupo($indiceGrupo, $plano['nome_periodo']);
        if ($titulo !== '') {
            $section->addText($titulo, ['bold' => true, 'size' => 14], ['spaceAfter' => 300]);
        }
    }

    $r = $tDet->addRow();

    $r->addCell()->addText(bncc('bncc_anos', $ln['ano'], $conexao_bncc, 'ano'));
    $r->addCell()->addText(bncc('bncc_areas', $ln['areaConhecimento'], $conexao_bncc));
    $r->addCell()->addText(bncc('bncc_componentes', $ln['componenteCurricular'], $conexao_bncc));
    $r->addCell()->addText(bncc('bncc_unidades_tematicas', $ln['unidadeTematicas'], $conexao_bncc));
    $r->addCell()->addText(bncc('bncc_objetos_conhecimento', $ln['objetosConhecimento'], $conexao_bncc));
    $r->addCell()->addText(strip_tags($ln['conteudos'] ?? ''));

    $hab = [];
    foreach (array_filter(array_map('trim', explode(',', $ln['habilidades']))) as $hid) {
        $cod = bncc('bncc_habilidades', $hid, $conexao_bncc, 'codigo');
        $des = bncc('bncc_habilidades', $hid, $conexao_bncc, 'descricao');
        $hab[] = "$cod: $des";
    }
    $r->addCell()->addText(implode("\n", $hab));
    $r->addCell()->addText(strip_tags($ln['metodologias'] ?? ''));
    $r->addCell()->addText(bncc('bncc_etapas', $ln['etapa'], $conexao_bncc));
}

// ─── Nome do arquivo ───────────────────────────
function slug(string $str): string {
    $str = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$str);
    $str = strtolower(preg_replace('/[^a-z0-9]+/', '-', $str));
    return trim($str, '-') ?: 'arquivo';
}

$tmp = tempnam(sys_get_temp_dir(), 'docx');
IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

$fname = slug($plano['nome'] ?? 'planejamento') . '_' . date('Ymd_His') . '.docx';

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $fname . '"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
unlink($tmp);
exit;
