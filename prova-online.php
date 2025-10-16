<?php
// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('sessao-professor.php');
require('conexao.php');

// Verifica se há IDs de questões selecionadas
if (!isset($_GET['ids'])) {
    die("Erro: Nenhum ID de questão foi recebido.");
}

$ids_questoes = explode(',', $_GET['ids']);

// Remove valores vazios do array
$ids_questoes = array_filter($ids_questoes);

// Verifica se há pelo menos uma questão selecionada
if (empty($ids_questoes)) {
    die("Erro: Nenhuma questão válida foi selecionada.");
}

// Prepara a consulta para obter as questões selecionadas
try {
    $placeholders = implode(',', array_fill(0, count($ids_questoes), '?'));
    $sql = "SELECT * FROM questoes WHERE id IN ($placeholders)";
    $stmt = $conexao->prepare($sql);
    
    // Executa a consulta com os IDs
    $stmt->execute($ids_questoes);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($questoes)) {
        die("Erro: Nenhuma questão encontrada com os IDs fornecidos.");
    }
    
    // Gera um código único para a prova
    $codigo_prova = substr(md5(uniqid(rand(), true)), 0, 8);
    
    // Insere cada questão na tabela provas_online
    foreach ($questoes as $questao) {
        $sql_insert = "INSERT INTO provas_online (
            data, id_questao, questao, alternativa_A, alternativa_B, 
            alternativa_C, alternativa_D, alternativa_E, resposta, 
            turma, materia, login, escola, codigo_prova
        ) VALUES (
            :data, :id_questao, :questao, :alternativa_A, :alternativa_B, 
            :alternativa_C, :alternativa_D, :alternativa_E, :resposta, 
            :turma, :materia, :login, :escola, :codigo_prova
        )";
        
        $stmt_insert = $conexao->prepare($sql_insert);
        
        $result = $stmt_insert->execute([
            ':data' => time(),
            ':id_questao' => $questao['id'],
            ':questao' => $questao['questao'],
            ':alternativa_A' => $questao['alternativa_A'],
            ':alternativa_B' => $questao['alternativa_B'],
            ':alternativa_C' => $questao['alternativa_C'],
            ':alternativa_D' => $questao['alternativa_D'],
            ':alternativa_E' => $questao['alternativa_E'],
            ':resposta' => $questao['resposta'],
            ':turma' => $questao['turma'] ?? 'Não especificada',
            ':materia' => $questao['materia'],
            ':login' => $_SESSION['login'],
            ':escola' => $_SESSION['escola'],
            ':codigo_prova' => $codigo_prova
        ]);
        
        if (!$result) {
            die("Erro ao inserir questão ID {$questao['id']} na prova online.");
        }
    }
    
    // Redireciona para uma página de sucesso com o código da prova
    header("Location: prova-online-sucesso.php?codigo=$codigo_prova");
    exit();
    
} catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}
?>