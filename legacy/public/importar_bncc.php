<?php

require_once "conexao-bncc.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$arquivo_fundamental = __DIR__ . '/bncc-json/fundamental.json';
$arquivo_medio = __DIR__ . '/bncc-json/medio.json';

// Checagem prévia dos arquivos JSON
if (!file_exists($arquivo_fundamental)) {
    die("Arquivo fundamental.json não encontrado em $arquivo_fundamental");
}
if (!file_exists($arquivo_medio)) {
    die("Arquivo medio.json não encontrado em $arquivo_medio");
}

// Limpa todas as tabelas BNCC (fora da transação!)
limparBncc($conexao_bncc);
echo "Tabelas BNCC limpas!<br>";

// Agora sim, inicia a transação
$pdo = $conexao_bncc;
$pdo->beginTransaction();
echo "Transação iniciada<br>";

// --- Funções ---
function limparBncc($pdo) {
    $tabelas = [
        'bncc_habilidades',
        'bncc_objetos_conhecimento',
        'bncc_unidades_tematicas',
        'bncc_componentes',
        'bncc_anos',
        'bncc_etapas',
        'bncc_areas'
    ];
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    foreach ($tabelas as $tb) $pdo->exec("TRUNCATE TABLE `$tb`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
}

function inserirOuBuscarId($pdo, $tabela, $campos) {
    $where = [];
    $params = [];
    foreach ($campos as $campo => $valor) {
        $where[] = "`$campo` = ?";
        $params[] = $valor;
    }
    $sql = "SELECT id FROM `$tabela` WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $id = $stmt->fetchColumn();
    if ($id) return $id;
    $insert = "INSERT INTO `$tabela` (" . implode(',', array_keys($campos)) . ") VALUES (" . implode(',', array_fill(0, count($campos), '?')) . ")";
    $stmt = $pdo->prepare($insert);
    $stmt->execute(array_values($campos));
    return $pdo->lastInsertId();
}

function importarFundamental($pdo, $json) {
    $id_etapa = inserirOuBuscarId($pdo, 'bncc_etapas', ['nome' => 'Ensino Fundamental']);
    $areas_map = [];
    foreach ($json as $disciplinaKey => $disciplinaData) {
        $nome_componente = $disciplinaData['nome_disciplina'] ?? $disciplinaKey;
        $nome_area = identificarAreaPorComponente($nome_componente);
        if (!$nome_area) $nome_area = 'Área Geral';
        if (!isset($areas_map[$nome_area])) {
            $areas_map[$nome_area] = inserirOuBuscarId($pdo, 'bncc_areas', ['nome' => $nome_area]);
        }
        $id_area = $areas_map[$nome_area];
        $id_componente = inserirOuBuscarId($pdo, 'bncc_componentes', [
            'id_area' => $id_area,
            'nome' => $nome_componente
        ]);
        foreach ($disciplinaData['ano'] as $ano) {
            $anos = $ano['nome_ano'] ?? [];
            foreach ($anos as $nome_ano) {
                $id_ano = inserirOuBuscarId($pdo, 'bncc_anos', [
                    'id_etapa' => $id_etapa,
                    'ano' => $nome_ano
                ]);
                foreach ($ano['unidades_tematicas'] as $unidade) {
                    $id_unidade = inserirOuBuscarId($pdo, 'bncc_unidades_tematicas', [
                        'id_componente' => $id_componente,
                        'nome' => $unidade['nome_unidade']
                    ]);
                    foreach ($unidade['objeto_conhecimento'] as $objeto) {
                        $id_objeto = inserirOuBuscarId($pdo, 'bncc_objetos_conhecimento', [
                            'id_unidade_tematica' => $id_unidade,
                            'nome' => $objeto['nome_objeto']
                        ]);
                        foreach ($objeto['habilidades'] as $habilidade) {
                            $codigo = '';
                            $descricao = $habilidade['nome_habilidade'];
                            if (preg_match('/^\(([\w\d]+)\)\s+(.+)$/', $descricao, $m)) {
                                $codigo = $m[1];
                                $descricao = $m[2];
                            }
                            inserirOuBuscarId($pdo, 'bncc_habilidades', [
                                'id_objeto' => $id_objeto,
                                'codigo' => $codigo,
                                'descricao' => $descricao
                            ]);
                        }
                    }
                }
            }
        }
    }
}

function identificarAreaPorComponente($componente) {
    $map = [
        'Língua Portuguesa' => 'Linguagens',
        'Arte' => 'Linguagens',
        'Educação Física' => 'Linguagens',
        'Língua Inglesa' => 'Linguagens',
        'Matemática' => 'Matemática',
        'Ciências' => 'Ciências da Natureza',
        'Geografia' => 'Ciências Humanas',
        'História' => 'Ciências Humanas',
        'Ensino Religioso' => 'Ensino Religioso',
        'Computação' => 'Computação'
    ];
    return $map[$componente] ?? null;
}

function importarMedio($pdo, $json) {
    $id_etapa = inserirOuBuscarId($pdo, 'bncc_etapas', ['nome' => 'Ensino Médio']);
    foreach ($json as $disciplinaKey => $disciplinaData) {
        $nome_componente = $disciplinaData['nome_disciplina'] ?? $disciplinaKey;
        $nome_area = identificarAreaPorComponente($nome_componente);
        if (!$nome_area) $nome_area = 'Área Geral';
        $id_area = inserirOuBuscarId($pdo, 'bncc_areas', ['nome' => $nome_area]);
        $id_componente = inserirOuBuscarId($pdo, 'bncc_componentes', [
            'id_area' => $id_area,
            'nome' => $nome_componente
        ]);
        foreach ($disciplinaData['ano'] as $ano) {
            $anos = $ano['nome_ano'] ?? [];
            foreach ($anos as $nome_ano) {
                $id_ano = inserirOuBuscarId($pdo, 'bncc_anos', [
                    'id_etapa' => $id_etapa,
                    'ano' => $nome_ano
                ]);
                if (isset($ano['codigo_habilidade'])) {
                    foreach ($ano['codigo_habilidade'] as $hab) {
                        $codigo = $hab['nome_codigo'] ?? '';
                        $descricao = $hab['nome_habilidade'] ?? '';
                        $id_unidade = inserirOuBuscarId($pdo, 'bncc_unidades_tematicas', [
                            'id_componente' => $id_componente,
                            'nome' => 'Competências/Habilidades'
                        ]);
                        $id_objeto = inserirOuBuscarId($pdo, 'bncc_objetos_conhecimento', [
                            'id_unidade_tematica' => $id_unidade,
                            'nome' => 'Competências/Habilidades'
                        ]);
                        inserirOuBuscarId($pdo, 'bncc_habilidades', [
                            'id_objeto' => $id_objeto,
                            'codigo' => $codigo,
                            'descricao' => $descricao
                        ]);
                    }
                }
            }
        }
    }
}

// MAIN
try {
    // Importar Fundamental
    $jsonFund = json_decode(file_get_contents($arquivo_fundamental), true);
    importarFundamental($pdo, $jsonFund);

    // Importar Médio
    $jsonMedio = json_decode(file_get_contents($arquivo_medio), true);
    importarMedio($pdo, $jsonMedio);

    $pdo->commit();
    echo "BNCC importada com sucesso!";
} catch (Exception $e) {
    // Protetor de rollback (não morre por falta de transação)
    try {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Exception $ex) {}
    echo "Erro: " . $e->getMessage();
}
?>
