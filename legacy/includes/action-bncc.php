<?php
// maps-bncc.php  ─ monta todos os mapas BNCC para o front-end
require_once __DIR__ . '/../conexao-bncc.php';
header('Content-Type: application/json; charset=utf-8');

/* Helper genérico para tabelas simples */
function getMap(PDO $pdo, string $table, string $id = 'id', string $label = 'nome'): array {
    $stmt = $pdo->query("SELECT $id AS id, $label AS label FROM $table");
    $out  = [];
    foreach ($stmt as $row) {
        $out[$row['id']] = $row['label'];
    }
    return $out;
}

$maps = [
    'etapas'              => getMap($conexao_bncc, 'bncc_etapas'),
    'anos'                => getMap($conexao_bncc, 'bncc_anos', 'id', 'ano'),
    'areas'               => getMap($conexao_bncc, 'bncc_areas'),
    'componentes'         => getMap($conexao_bncc, 'bncc_componentes'),
    'unidades_tematicas'  => getMap($conexao_bncc, 'bncc_unidades_tematicas'),
    'objetosConhecimento' => getMap($conexao_bncc, 'bncc_objetos_conhecimento'),
];

// 1) id numérico → "código – descrição"
$stmt = $conexao_bncc->query("
    SELECT id,
           CONCAT(codigo, ' – ', descricao) AS label,
           codigo,
           descricao
      FROM bncc_habilidades
");
$maps['habilidades'] = [];
foreach ($stmt as $row) {
    $maps['habilidades'][$row['id']] = $row['label'];   // 1018 → "EF05MA03 – Resolver problemas…"
}

// 2) código → "código – descrição"  (opcional)
$maps['habilidades_codigo'] = [];
foreach ($stmt as $row) {            // o $stmt ainda tem os dados em buffer
    $maps['habilidades_codigo'][$row['codigo']] = $row['label'];
}
echo json_encode($maps);
exit;

?>
