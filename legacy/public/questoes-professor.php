<?php 
require('sessao-professor.php');
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


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Questões - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
</head>
<body>
  <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
    <div class="main-content" id="main-content">
	<?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>
	 
	<div class="content-container" id="content-container">
		<!--  Navegacao 
		<div class="top-nav">
			<a href="dashboard-professor" class="btn btn-secondary">
			<i class="fas fa-arrow-left"></i> Voltar
				</a>
		</div>-->
    
    <!-- Conteudo -->
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Gestão de Questões</h1>
                <p>Visualize, edite ou exclua questões do banco de dados</p>
            </div>
            <div>
				<a href="dashboard-professor" class="btn btn-secondary">
				<i class="fas fa-arrow-left"></i> Voltar
					</a>
				<button class="btn btn-primary" id="btnCriarQuestao" style="margin-right: 10px;">
					<i class="fas fa-plus"></i> Criar Nova Questão
				</button>
                <button class="btn btn-primary" id="btnGerarProva" style="margin-right: 10px;">
                    <i class="fas fa-file-alt"></i> Gerar Prova
                </button>
                <button class="btn btn-primary" id="gerarProvaOnline" style="margin-right: 10px;">
                    <i class="fas fa-link"></i> Gerar Prova On Line
                </button>
                
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-container">
            <form method="get" class="filtros-form" id="filtrosForm">
                <div class="filtro-group">
                    <label for="materia">Matéria</label>
                    <input type="text" id="materia" name="materia" placeholder="Filtrar por matéria ou ID" value="<?php echo htmlspecialchars($filtro_materia); ?>">
                </div>
                
                <div class="filtro-group">
                    <label for="assunto">Assunto</label>
                    <input type="text" id="assunto" name="assunto" placeholder="Filtrar por assunto ou nível de ensino" value="<?php echo htmlspecialchars($filtro_assunto); ?>">
                </div>
                
                <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
              
            </form>
            
            <div class="contador-registros">
                Exibindo <strong><?php echo $total_registros; ?></strong> registro(s) encontrado(s)
            </div>
        </div>
        
        <!-- Tabela de questões -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-header" width='5%'>
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th onclick="ordenar('id')" class="<?php echo $coluna_ordenacao == 'id' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>" width='10%'>
                            ID
                        </th>
                        <th width='10%' onclick="ordenar('data')" class="<?php echo $coluna_ordenacao == 'data' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Data
                        </th>
                        <th width='15%' onclick="ordenar('materia')" class="<?php echo $coluna_ordenacao == 'materia' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Matéria
                        </th>
                        <th width='12%'onclick="ordenar('assunto')" class="<?php echo $coluna_ordenacao == 'assunto' ? ($direcao_ordenacao == 'ASC' ? 'sorted-asc' : 'sorted-desc') : ''; ?>">
                            Assunto
                        </th>
                        <th width='15%'>Nível de Ensino</th>
                        <th width='15%'>Tipo</th>
                        <th width='22%'>Ações</th>
                    </tr>
                </thead>
               <tbody>
<?php if (count($questoes) > 0): ?>
    <?php foreach($questoes as $questao): ?>
        <tr>
            <td class="checkbox-cell">
                <input type="checkbox" class="checkbox-item" value="<?php echo $questao['id']; ?>">
            </td>
            <td><?php echo $questao['id']; ?></td>
            <td><?php echo date('d/m/Y', ($questao['data'])); ?></td>
            <td><?php echo htmlspecialchars($questao['materia']); ?></td>
            <td><?php echo htmlspecialchars($questao['assunto']); ?></td>
            <td><?php echo htmlspecialchars($questao['grau_escolar']); ?></td>
            <td><?php echo htmlspecialchars($questao['tipo']); ?></td>
            <td>
                <button class="btn-action btn-view" onclick="abrirQuestao(<?php echo $questao['id']; ?>)">
                    <i class="fas fa-eye"></i> 
                </button>
                <button class="btn-action btn-edit" onclick="editarQuestao(<?php echo $questao['id']; ?>)">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
					<tr>
					<td colspan="8" style="text-align: center;">Nenhuma questão encontrada.</td>
						</tr>
						<?php endif; ?>
								</tbody>
						</table>
					</div>
				</div>
		</div>
	</div>
    	<script src="pusaber.js"></script>
</body>
</html>