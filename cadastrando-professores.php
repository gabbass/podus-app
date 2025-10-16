<?php
require('sessao-adm.php');
require('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Receber os dados do formulário
        $nomeCompleto = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $escola = filter_input(INPUT_POST, 'escola', FILTER_SANITIZE_NUMBER_INT);

        if (!$nomeCompleto || !$email || !$escola) {
            throw new Exception("Dados inválidos ou incompletos.");
        }

        // Extrair o primeiro nome
        $nomes = explode(" ", $nomeCompleto);
        $primeiroNome = ucfirst(strtolower($nomes[0]));

        // Gerar 4 números aleatórios
        $numerosAleatorios = rand(1000, 9999);

        // Montar o login
        $login = $primeiroNome . $numerosAleatorios;

        // Definir perfil fixo como "Professor"
        $perfil = "Professor";

        // Inserir no banco de dados usando PDO
        $stmt = $conexao->prepare("INSERT INTO login (nome, login, perfil, email, escola) VALUES (?, ?, ?, ?, ?)");
        $resultado = $stmt->execute([$nomeCompleto, $login, $perfil, $email, $escola]);

        if ($resultado) {
            echo "<script>
                    alert('Professor cadastrado com sucesso! Login gerado: {$login}');
                    window.location.href = 'dashboard.php';
                  </script>";
            exit;
        } else {
            throw new Exception("Erro ao cadastrar professor.");
        }
    } catch (Exception $e) {
        echo "<script>
                alert('" . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>