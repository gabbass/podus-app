<?php
session_start();

if($_SESSION['perfil'] == 'Administrador'){
    header('location:dashboard');
}elseif($_SESSION['perfil'] == 'Professor'){
    header('location:dashboard-professor');
}