<?php 
require('sessao-adm-professor.php');
require('conexao.php');

// Inicializa variáveis de filtro e ordenação
$filtro_materia = $_GET['materia'] ?? '';
$filtro_assunto = $_GET['assunto'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'data_desc';
$coluna_ordenacao = 'data';
$direcao_ordenacao = 'DESC';

switch($ordenacao) {
    case 'id_asc': $coluna_ordenacao = 'id'; $direcao_ordenacao = 'ASC'; break;
    case 'id_desc': $coluna_ordenacao = 'id'; $direcao_ordenacao = 'DESC'; break;
    case 'materia_asc': $coluna_ordenacao = 'materia'; $direcao_ordenacao = 'ASC'; break;
    case 'materia_desc': $coluna_ordenacao = 'materia'; $direcao_ordenacao = 'DESC'; break;
    case 'assunto_asc': $coluna_ordenacao = 'assunto'; $direcao_ordenacao = 'ASC'; break;
    case 'assunto_desc': $coluna_ordenacao = 'assunto'; $direcao_ordenacao = 'DESC'; break;
    case 'data_asc': $coluna_ordenacao = 'data'; $direcao_ordenacao = 'ASC'; break;
    default: $coluna_ordenacao = 'data'; $direcao_ordenacao = 'DESC';
}

$sql = "SELECT * FROM questoes WHERE 1=1";
$params = [];

if (!empty($filtro_materia)) {
    $sql .= " AND (materia LIKE :materia OR id = :id)";
    $params[':materia'] = '%' . $filtro_materia . '%';
    if (is_numeric($filtro_materia)) {
        $params[':id'] = $filtro_materia;
    } else {
        $sql = str_replace("OR id = :id", "", $sql);
        unset($params[':id']);
    }
}

if (!empty($filtro_assunto)) {
    $sql .= " AND (assunto LIKE :assunto OR grau_escolar LIKE :grau_escolar)";
    $params[':assunto'] = '%' . $filtro_assunto . '%';
    $params[':grau_escolar'] = '%' . $filtro_assunto . '%';
}

$sql .= " ORDER BY $coluna_ordenacao $direcao_ordenacao";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_registros = count($questoes);
} catch (PDOException $e) {
    die("Erro ao consultar questões: " . $e->getMessage());
}

function buildUrl($params) {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        $currentParams[$key] = $value;
    }
    return '?' . http_build_query($currentParams);
}
?>
