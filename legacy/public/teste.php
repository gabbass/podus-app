<?php


// Teste simples de e-mail
$testEmail = mail(
    'contatoheliosander@gmail.com', 
    'Teste de e-mail', 
    'Esta é uma mensagem de teste'
);

if ($testEmail) {
    echo "E-mail de teste enviado com sucesso!";
} else {
    echo "Falha ao enviar e-mail de teste. Verifique a configuração do servidor.";
}

?>