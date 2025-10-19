<?php

session_start();

if( isset($_SESSION['login'] )  and $_SESSION['perfil'] == 'Administrador'  ) {
    $login = $_SESSION['login'];
    $perfil = $_SESSION['perfil'];
    $cliente = $_SESSION['cliente'];
    $cpf = $_SESSION['cpf'];

    
}else{
    echo "<script>alert('É necessário fazer o login na página')</script>";
    echo "<script>location.assign('index-portal')</script>";
}