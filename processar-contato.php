<?php
require 'conexao.php'; // Seu arquivo de conexão
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar e validar dados do formulário
    $nome = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $tipo = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $telefone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $mensagem = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
	$aceite_termos = isset($_POST['aceite_termos']);
	$aceite_whatsapp = isset($_POST['aceite_whatsapp']);
	
	
	if (!$aceite_termos || !$aceite_whatsapp) {
    header('Location: index.php?contato=naoaceito');
    exit();
	}
	
	//ReCaptacha
	error_log('ANTES DE VALIDAR RECAPTCHA');
	$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $secretKey = '6Ld4zIIrAAAAAOFJSzeom29DzogYjGeV31Fn_Z4o';
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaData = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($recaptchaData),
        ]
    ];
    $context  = stream_context_create($options);
    $verify = file_get_contents($recaptchaUrl, false, $context);
    $captchaSuccess = json_decode($verify);

    if (!($captchaSuccess && $captchaSuccess->success)) {
        header("Location: index.php?contato=recaptcha");
        exit();
    }
	
	error_log('APÓS VALIDAR RECAPTCHA');
	error_log(print_r($captchaSuccess, true));
    if ($nome && $tipo && $telefone && $email && $mensagem) {
        try {
			
			 $stmt = $conexao->prepare("
            INSERT INTO contatos
            (nome, tipo, telefone, email, mensagem, aceite_termos, aceite_whatsapp, data_envio, ip)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([
            $nome,
            $tipo,
            $telefone,
            $email,
            $mensagem,
            $aceite_termos ? 1 : 0,
            $aceite_whatsapp ? 1 : 0,
            $_SERVER['REMOTE_ADDR']
        ]);
		
            $mail = new PHPMailer(true);
            
            // Configurações do servidor SMTP (ajuste conforme seu servidor)
            $mail->isSMTP();
            $mail->Host = 'smtp.titan.email';
			$mail->SMTPAuth = true;
			$mail->Username = 'contato@portaluniversodosaber.com.br';
			$mail->Password = 'jPpb0#4w@15';
			$mail->SMTPSecure = 'tls';
			$mail->Port = 587;
			$mail->setFrom('contato@portaluniversodosaber.com.br', 'Portal Universo do Saber - Contato');
			$mail->addAddress('contato@portaluniversodosaber.com.br');
			$mail->addCC($email); // ou addBCC, se quiser oculto

             
            $mail->addReplyTo($email, $nome);
            
            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Novo contato do site - ' . $nome;
            
            $mail->Body = "
                <h2>Novo contato recebido</h2>
                <p><strong>Nome:</strong> $nome</p>
                <p><strong>Tipo:</strong> $tipo</p>
                <p><strong>Telefone:</strong> $telefone</p>
                <p><strong>E-mail:</strong> $email</p>
                <p><strong>Mensagem:</strong></p>
                <p>$mensagem</p>
				<p>Aceitou os termos? " . ($aceite_termos ? 'Sim' : 'Não') . "</p>
				<p>Autorizou WhatsApp? " . ($aceite_whatsapp ? 'Sim' : 'Não') . "</p>

                <p><em>Enviado em " . date('d/m/Y H:i') . "</em></p>
            ";
            
            $mail->AltBody = "Novo contato:\nNome: $nome\nTipo: $tipo\nTelefone: $telefone\nE-mail: $email\nMensagem:\n$mensagem";
            
            $mail->send();
			
			
			
            
            // Redireciona com mensagem de sucesso
            echo "<script>alert('Mensagem Enviada com sucesso!')</script>";
            echo "<script>location.assign('index')</script>";

        } catch (Exception $e) {
            // Redireciona com mensagem de erro
			error_log("Erro PHPMailer: " . $e->getMessage());
           // header('Location: index.php?contato=error');
            exit();
        }
    } else {
        // Dados inválidos
		  header('Location: index.php?contato=invalid');
        exit();
    }
} else {
    // Método não permitido
    header('Location: index.php');
    exit();
}