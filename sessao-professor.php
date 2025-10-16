<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if( isset($_SESSION['login'] )  and $_SESSION['perfil'] == 'Professor'  ) {
	$login = $_SESSION['login'];
    $perfil = $_SESSION['perfil'];
    $escola = $_SESSION['escola'];
    $nome = $_SESSION['nome'];
	$id_professor = $_SESSION[''] ?? ($_SESSION['id'] ?? null);


}else{
   header('Location: sair.php');
    exit;
}