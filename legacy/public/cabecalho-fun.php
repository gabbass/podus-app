<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nome = $_SESSION['nome'] ?? 'Usuário';
$perfil = $_SESSION['perfil'] ?? '';
$inicial = strtoupper(mb_substr(trim($nome), 0, 1, 'UTF-8'));
$cor = "#0057b7";
$svg = '<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
  <circle cx="20" cy="20" r="20" fill="' . $cor . '"/>
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-size="20" fill="#fff" font-family="Arial, sans-serif" dy=".1em">' . $inicial . '</text>
</svg>';
$dataUri = "data:image/svg+xml;base64," . base64_encode($svg);
?>