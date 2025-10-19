<?php 
require('conexao.php');
// Consulta para contar o total de questões
$query_questoes = "SELECT COUNT(*) as total FROM questoes";
$stmt_questoes = $conexao->prepare($query_questoes);
$stmt_questoes->execute();
$total_questoes = $stmt_questoes->fetch(PDO::FETCH_ASSOC)['total'];

// Consulta para contar alunos da escola do professor logado
$perfil = 'Aluno';
$query_alunos = "SELECT COUNT(*) as total FROM login 
                WHERE login = :login AND perfil = :perfil";
$stmt_alunos = $conexao->prepare($query_alunos);
$stmt_alunos->bindValue(':login', $_SESSION['login']);
$stmt_alunos->bindValue(':perfil', $perfil);
$stmt_alunos->execute();
$total_alunos = $stmt_alunos->fetch(PDO::FETCH_ASSOC)['total'];

// Consulta para contar turmas da escola do professor logado
$query_turmas = "SELECT COUNT(*) as total FROM turmas 
                WHERE login = :login";
$stmt_turmas = $conexao->prepare($query_turmas);
$stmt_turmas->bindValue(':login', $_SESSION['login']);
$stmt_turmas->execute();
$total_turmas = $stmt_turmas->fetch(PDO::FETCH_ASSOC)['total'];

// Consulta para o gráfico de pizza - alunos por turma
$query_alunos_turma = "SELECT turma, COUNT(*) as total FROM login 
                      WHERE  login = :login AND perfil = 'Aluno'
                      GROUP BY turma";
$stmt_alunos_turma = $conexao->prepare($query_alunos_turma);
$stmt_alunos_turma->bindValue(':login', $_SESSION['login']);
$stmt_alunos_turma->execute();
$alunos_por_turma = $stmt_alunos_turma->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para o gráfico
$turmas = [];
$quantidades = [];
$cores = [];

foreach($alunos_por_turma as $item) {
    $turmas[] = $item['turma'] ? $item['turma'] : 'Sem Turma';
    $quantidades[] = $item['total'];
    $cores[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); // Cores aleatórias
}
?>
