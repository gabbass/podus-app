<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecione Seu Perfil - Universo do Saber</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <main class="profile-selector">
        <div class="profile-selector__logo" onclick="location.assign('index')">
            <img src="img/logo-hor-semi-branco.webp" alt="Universo do Saber">
        </div>

        <a href="login-aluno.php" class="profile-selector__option profile-selector__option--student">
            <div class="profile-selector__content">
                <div class="profile-selector__icon">
                    <i class="fas fa-user-graduate" aria-hidden="true"></i>
                </div>
                <h2 class="profile-selector__title">Você é Aluno?</h2>
                <p class="profile-selector__description">Acesse seu portal com sua matrícula e acompanhe suas atividades, notas e materiais de estudo.</p>
                <span class="profile-selector__button">Acessar como Aluno</span>
            </div>
        </a>

        <a href="login-professor.php" class="profile-selector__option profile-selector__option--teacher">
            <div class="profile-selector__content">
                <div class="profile-selector__icon">
                    <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
                </div>
                <h2 class="profile-selector__title">Você é Professor?</h2>
                <p class="profile-selector__description">Acesse sua área exclusiva para gerenciar turmas, planejar aulas e criar avaliações.</p>
                <span class="profile-selector__button">Acessar como Professor</span>
            </div>
        </a>
    </main>
</body>
</html>
