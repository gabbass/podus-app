<?php
require('sessao-professor.php');
require('conexao.php');

// Buscar opções para os selects
try {
    // Materias
    $sql_materias = "SELECT DISTINCT materia FROM questoes ORDER BY materia";
    $stmt = $conexao->query($sql_materias);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Níveis de ensino
    $sql_niveis = "SELECT DISTINCT grau_escolar FROM questoes ORDER BY grau_escolar";
    $stmt = $conexao->query($sql_niveis);
    $niveis_ensino = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Todos os assuntos (serão filtrados via JavaScript)
    $sql_assuntos = "SELECT DISTINCT materia, assunto FROM questoes ORDER BY materia, assunto";
    $stmt = $conexao->query($sql_assuntos);
    $todos_assuntos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Turmas do professor logado
    $login_professor = $_SESSION['login'];
    $sql_turmas = "SELECT id, nome FROM turmas WHERE login = :login ORDER BY nome";
    $stmt = $conexao->prepare($sql_turmas);
    $stmt->bindValue(':login', $login_professor);
    $stmt->execute();
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $erro = "Erro ao carregar opções: " . $e->getMessage();
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materia = $_POST['materia'] ?? '';
    $nivel_ensino = $_POST['nivel_ensino'] ?? '';
    $assunto = $_POST['assunto'] ?? '';
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $turma_id = $_POST['turma'] ?? '';
    $data_prova = $_POST['data_prova'] ?? '';
    $login_professor = $_SESSION['login'];
    
    // Validações (apenas quantidade, turma e data são obrigatórios)
    if ($quantidade <= 0 || empty($turma_id) || empty($data_prova)) {
        $erro = "Quantidade, turma e data são campos obrigatórios!";
    } else {
        try {
            // 1. Buscar os alunos da turma selecionada com suas matrículas
            $sql_alunos = "SELECT login, matricula FROM login WHERE turma = :turma_id AND perfil = 'Aluno'";
            $stmt = $conexao->prepare($sql_alunos);
            $stmt->bindValue(':turma_id', $turma_id);
            $stmt->execute();
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($alunos) == 0) {
                $erro = "Nenhum aluno encontrado nesta turma!";
            } else {
                // 2. Obter nome da turma
                $sql_turma = "SELECT nome FROM turmas WHERE nome = :turma_nome";
                $stmt = $conexao->prepare($sql_turma);
                $stmt->bindValue(':turma_nome', $turma_id);
                $stmt->execute();
                $turma = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$turma) {
                    $erro = "Turma não encontrada!";
                } else {
                    // 3. Converter data para timestamp
                    $data_timestamp = strtotime($data_prova);
                    
                    // 4. Iniciar transação
                    $conexao->beginTransaction();
                    $total_questoes = 0;
                    $alunos_com_prova = 0;
                    
                    // 5. Para cada aluno, gerar uma prova única
                    foreach ($alunos as $aluno) {
                        // Construir a query dinamicamente baseada nos critérios selecionados
                        $sql_questoes = "SELECT id FROM questoes WHERE 1=1";
                        $params = [];
                        
                        if (!empty($materia)) {
                            $sql_questoes .= " AND materia = :materia";
                            $params[':materia'] = $materia;
                        }
                        
                        if (!empty($nivel_ensino)) {
                            $sql_questoes .= " AND grau_escolar = :nivel_ensino";
                            $params[':nivel_ensino'] = $nivel_ensino;
                        }
                        
                        if (!empty($assunto)) {
                            $sql_questoes .= " AND assunto = :assunto";
                            $params[':assunto'] = $assunto;
                        }
                        
                        $sql_questoes .= " ORDER BY RAND() LIMIT :quantidade";
                        $params[':quantidade'] = $quantidade;
                        
                        // Executar a query para o aluno atual
                        $stmt = $conexao->prepare($sql_questoes);
                        
                        foreach ($params as $key => $value) {
                            $paramType = ($key === ':quantidade') ? PDO::PARAM_INT : PDO::PARAM_STR;
                            $stmt->bindValue($key, $value, $paramType);
                        }
                        
                        $stmt->execute();
                        $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($questoes) > 0) {
                            // Inserir cada questão na tabela provas para o aluno atual
                            foreach ($questoes as $questao) {
                                $sql_prova = "INSERT INTO provas 
                                            (data, id_questao, login, matricula, turma) 
                                            VALUES (:data, :id_questao, :login, :matricula, :turma)";
                                
                                $stmt = $conexao->prepare($sql_prova);
                                $stmt->bindValue(':data', $data_timestamp);
                                $stmt->bindValue(':id_questao', $questao['id']);
                                $stmt->bindValue(':login', $login_professor);
                                $stmt->bindValue(':matricula', $aluno['matricula']);
                                $stmt->bindValue(':turma', $turma_id);
                                $stmt->execute();
                            }
                            
                            $total_questoes += count($questoes);
                            $alunos_com_prova++;
                        }
                    }
                    
                    $conexao->commit();
                    
                    $mensagem = "Provas criadas com sucesso para a turma " . htmlspecialchars($turma['nome']) . 
                               "!<br>Foram geradas " . $alunos_com_prova . " provas com um total de " . 
                               $total_questoes . " questões distribuídas.";
                }
            }
        } catch (PDOException $e) {
            $conexao->rollBack();
            $erro = "Erro ao gerar provas: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Prova Aleatória - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>


 :root {
            --primary-blue: #0057b7;
            --primary-orange: #ffa500;
            --dark-blue: #003d7a;
            --dark-orange: #cc8400;
            --light-gray: #f5f7fa;
            --medium-gray: #e1e5eb;
            --dark-gray: #6c757d;
        }
        
        body {
            background-color: var(--light-gray);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
        }
        
        h1 {
            color: var(--dark-blue);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background-color: #e7f5ff;
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-random"></i> Gerar Provas Aleatórias</h1>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i> O sistema irá gerar provas distintas para cada aluno da turma selecionada.
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="materia">Matéria:</label>
                <select id="materia" name="materia">
                    <option value="">Todas as matérias</option>
                    <?php foreach ($materias as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['materia']); ?>" <?php echo (isset($_POST['materia']) && $_POST['materia'] == $m['materia']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['materia']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nivel_ensino">Nível de Ensino:</label>
                <select id="nivel_ensino" name="nivel_ensino">
                    <option value="">Todos os níveis</option>
                    <?php foreach ($niveis_ensino as $nivel): ?>
                        <option value="<?php echo htmlspecialchars($nivel['grau_escolar']); ?>" <?php echo (isset($_POST['nivel_ensino']) && $_POST['nivel_ensino'] == $nivel['grau_escolar']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nivel['grau_escolar']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="assunto">Assunto:</label>
                <select id="assunto" name="assunto">
                    <option value="">Todos os assuntos</option>
                    <?php 
                    // Mostrar assuntos pré-selecionados se vier do POST
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['materia'])) {
                        $materia_selecionada = $_POST['materia'];
                        $sql_assuntos_filtrados = "SELECT DISTINCT assunto FROM questoes WHERE materia = :materia ORDER BY assunto";
                        $stmt = $conexao->prepare($sql_assuntos_filtrados);
                        $stmt->bindValue(':materia', $materia_selecionada);
                        $stmt->execute();
                        $assuntos_filtrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($assuntos_filtrados as $ass) {
                            $selected = (isset($_POST['assunto']) && $_POST['assunto'] == $ass['assunto']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($ass['assunto']).'" '.$selected.'>'.htmlspecialchars($ass['assunto']).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quantidade">Quantidade de Questões por Aluno:</label>
                <input type="number" id="quantidade" name="quantidade" min="1" required value="<?php echo isset($_POST['quantidade']) ? htmlspecialchars($_POST['quantidade']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="turma">Turma:</label>
                <select id="turma" name="turma" required>
                    <option value="">Selecione uma turma</option>
                    <?php foreach ($turmas as $turma): ?>
                        <option value="<?php echo $turma['nome']; ?>" <?php echo (isset($_POST['turma']) && $_POST['turma'] == $turma['nome']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($turma['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="data_prova">Data da Prova:</label>
                <input type="date" id="data_prova" name="data_prova" required value="<?php echo isset($_POST['data_prova']) ? htmlspecialchars($_POST['data_prova']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-magic"></i> Gerar Provas
                </button>
                
                <a href="questoes-professor" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>

    <script>

        // Dados de assuntos por matéria
        const assuntosPorMateria = <?php 
            $assuntos_organizados = [];
            foreach ($todos_assuntos as $assunto) {
                $materia = $assunto['materia'];
                if (!isset($assuntos_organizados[$materia])) {
                    $assuntos_organizados[$materia] = [];
                }
                $assuntos_organizados[$materia][] = $assunto['assunto'];
            }
            echo json_encode($assuntos_organizados);
        ?>;

        // Elementos do DOM
        const materiaSelect = document.getElementById('materia');
        const assuntoSelect = document.getElementById('assunto');

        // Atualizar assuntos quando a matéria mudar
        materiaSelect.addEventListener('change', function() {
            const materiaSelecionada = this.value;
            
            // Limpar opções atuais
            assuntoSelect.innerHTML = '<option value="">Todos os assuntos</option>';
            
            // Adicionar novas opções se uma matéria foi selecionada
            if (materiaSelecionada && assuntosPorMateria[materiaSelecionada]) {
                assuntosPorMateria[materiaSelecionada].forEach(function(assunto) {
                    const option = document.createElement('option');
                    option.value = assunto;
                    option.textContent = assunto;
                    assuntoSelect.appendChild(option);
                });
            }
        });

        // Se veio do POST, manter a seleção
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            window.addEventListener('DOMContentLoaded', function() {
                const materiaSelecionada = "<?php echo isset($_POST['materia']) ? $_POST['materia'] : ''; ?>";
                const assuntoSelecionado = "<?php echo isset($_POST['assunto']) ? $_POST['assunto'] : ''; ?>";
                
                if (materiaSelecionada) {
                    // Disparar o evento para carregar os assuntos
                    materiaSelect.value = materiaSelecionada;
                    const event = new Event('change');
                    materiaSelect.dispatchEvent(event);
                    
                    // Depois de um pequeno delay, selecionar o assunto correto
                    setTimeout(function() {
                        if (assuntoSelecionado) {
                            assuntoSelect.value = assuntoSelecionado;
                        }
                    }, 100);
                }
            });
        <?php endif; ?>

    </script>
</body>
</html>