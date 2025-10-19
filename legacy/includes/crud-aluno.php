<?php 
require('sessao-professor.php');
require('conexao.php');

$login_professor = $_SESSION['login'];
$mensagem = '';
$erro = '';

// Busca turmas disponíveis
$turmas = [];
try {
   $sql_turmas = "SELECT id, nome FROM turmas WHERE login = :login_professor ORDER BY nome";
$stmt = $conexao->prepare($sql_turmas); // Use prepare() ao invés de query()
$stmt->bindValue(':login_professor', $login_professor);
$stmt->execute(); // Execute a consulta antes de buscar os resultados
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro ao carregar turmas: " . $e->getMessage();
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $matricula = trim($_POST['matricula']);
    $turma_id = trim($_POST['turma']);
    
    // Validações
    if (empty($nome) || empty($matricula) || empty($turma_id) ) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        try {
            // Gera login (primeiro nome em minúsculo + número aleatório)
            $primeiro_nome = strtolower(explode(' ', $nome)[0]);
            $login = $_SESSION['login'];
            
            // Insere na tabela login
            $sql = "INSERT INTO login (nome, login, perfil, matricula, turma) 
                    VALUES (:nome, :login, 'Aluno', :matricula,:turma)";
            
            $stmt = $conexao->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':turma', $turma_id);

            if ($stmt->execute()) {
                $mensagem = "Aluno cadastrado com sucesso!<br>
                            Login: $matricula<br>
                            (Anote estas informações para o aluno)";
                
                // Limpa os campos do formulário
                $_POST = array();
            } else {
                $erro = "Erro ao cadastrar aluno!";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}
?>
		<h2 id="tituloFormMaterial">Criar novo aluno</h2>
        <p id="subtituloFormMaterial">Altere os campos abaixo</p>

                <form method="post" >
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control" value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="matricula">Matrícula</label>
                        <input type="text" id="matricula" name="matricula" class="form-control" value="<?= isset($_POST['matricula']) ? htmlspecialchars($_POST['matricula']) : '' ?>" required>
                    </div>
                    
                   
                    <div class="form-group">
                        <label for="turma">Turma</label>
                        <select id="turma" name="turma" class="form-control" required>
                            <option value="">Selecione uma turma</option>
                            <?php foreach ($turmas as $turma): ?>
                                <option value="<?= $turma['nome'] ?>" <?= (isset($_POST['turma']) && $_POST['turma'] == $turma['nome']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($turma['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                       
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Aluno
                        </button>
                    </div>
                </form>
   
