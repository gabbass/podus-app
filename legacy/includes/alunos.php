<?php 
require('conexao.php');

if (isset($_GET['excluido']) && $_GET['excluido'] == 1) {
    echo '<p style="color: red; font-weight: bold;">Aluno excluído com sucesso!</p>';
}

// Inicializa variáveis de filtro e ordenação
$filtro_matricula = $_GET['matricula'] ?? '';
$filtro_nome = $_GET['nome'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'nome_asc';
$coluna_ordenacao = 'nome';
$direcao_ordenacao = 'ASC';

// Define a ordenação com base no parâmetro
switch($ordenacao) {
    case 'matricula_asc':
        $coluna_ordenacao = 'matricula';
        $direcao_ordenacao = 'ASC';
        break;
    case 'matricula_desc':
        $coluna_ordenacao = 'matricula';
        $direcao_ordenacao = 'DESC';
        break;
    case 'nome_asc':
        $coluna_ordenacao = 'nome';
        $direcao_ordenacao = 'ASC';
        break;
    case 'nome_desc':
        $coluna_ordenacao = 'nome';
        $direcao_ordenacao = 'DESC';
        break;
    case 'turma_asc':
        $coluna_ordenacao = 'turma';
        $direcao_ordenacao = 'ASC';
        break;
    case 'turma_desc':
        $coluna_ordenacao = 'turma';
        $direcao_ordenacao = 'DESC';
        break;
    default:
        $coluna_ordenacao = 'nome';
        $direcao_ordenacao = 'ASC';
}

// Prepara a consulta SQL com filtros
$sql = "SELECT * FROM login WHERE perfil = 'Aluno' AND login = :login";
$params = [
    ':login' => $_SESSION['login'],
];

if (!empty($filtro_matricula)) {
    $sql .= " AND matricula LIKE :matricula";
    $params[':matricula'] = '%' . $filtro_matricula . '%';
}

if (!empty($filtro_nome)) {
    $sql .= " AND nome LIKE :nome";
    $params[':nome'] = '%' . $filtro_nome . '%';
}

$sql .= " ORDER BY $coluna_ordenacao $direcao_ordenacao";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_registros = count($alunos);
} catch (PDOException $e) {
    die("Erro ao consultar alunos: " . $e->getMessage());
}

// Função para gerar URL com parâmetros
function buildUrl($params) {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        $currentParams[$key] = $value;
    }
    return '?' . http_build_query($currentParams);
}
?>