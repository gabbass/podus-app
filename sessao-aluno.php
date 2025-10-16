<?php
session_start();
// Verifica se o usuário está logado e se o perfil é "Aluno"
if (  isset($_SESSION['matricula']) AND $_SESSION['perfil'] == 'Aluno') {
    // Atribui variáveis de sessão às respectivas variáveis locais
    $login = $_SESSION['login'];
    $perfil = $_SESSION['perfil'];
    $escola = $_SESSION['escola'] ?? '';
    $nome = $_SESSION['nome'] ?? '';
    $turma = $_SESSION['turma'] ?? '';

} else {
    // Exibe alerta e redireciona para a página de login
    echo "<script>alert('É necessário fazer o login na página');</script>";
    echo "<script>window.location.href = 'login-aluno';</script>";
    exit;
}
