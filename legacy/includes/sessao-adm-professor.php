<?php
session_start();
if( isset($_SESSION['login'] )  and ( $_SESSION['perfil'] == 'Administrador' or $_SESSION['perfil'] == 'Professor' ) ) {
    $login = $_SESSION['login'];
    $perfil = $_SESSION['perfil'];
    $cliente = $_SESSION['cliente'];
    //$cpf = $_SESSION['cpf'];
	$escola = $_SESSION['escola'];
    $nome = $_SESSION['nome'];
	$id_professor = $_SESSION[''] ?? ($_SESSION['id'] ?? null);

    
}else{
   header('Location: sair.php');
    exit;
}