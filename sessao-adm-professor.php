<?php
session_start();
if( isset($_SESSION['login'] )  and ( $_SESSION['perfil'] == 'Administrador' or $_SESSION['perfil'] == 'Professor' ) ) {
    $login = $_SESSION['login'] ?? null;
    $perfil = $_SESSION['perfil'] ?? null;
    $cliente = $_SESSION['cliente'] ?? null;
    //$cpf = $_SESSION['cpf'];
	$escola = $_SESSION['escola'] ?? null;
    $nome = $_SESSION['nome'] ?? null;
	$id_professor = $_SESSION[''] ?? ($_SESSION['id'] ?? null);

    
}else{
   header('Location: sair.php');
    exit;
}