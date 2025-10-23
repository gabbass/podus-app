<?php
require 'cabecalho-fun.php';
?>
<header class="layout-header top-nav" data-header-component>
    <div class="layout-header__menu">
        <button class="menu-toggle"
                id="menu-toggle"
                type="button"
                aria-label="Alternar menu lateral"
                aria-expanded="false"
                data-menu-toggle>
            <i class="fa fa-bars" aria-hidden="true"></i>
        </button>
    </div>

    <div id="alertas-area" class="layout-header__alerts<?= empty($sucesso) && empty($erro) ? ' oculto' : ''; ?>">
        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success">
                <?= $sucesso; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger">
                <?= $erro; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="layout-header__user user-area">
        <div class="user-img">
            <img src="<?= $dataUri; ?>" alt="Avatar" />
        </div>
        <span class="user-name"><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></span>
        <button class="user-dropdown-toggle"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                onclick="window.toggleUserMenu?.()">
            <i class="fa fa-chevron-down" aria-hidden="true"></i>
        </button>
        <div id="user-menu" class="user-menu" hidden>
            <?php if ($perfil === 'Professor'): ?>
                <a href="meu-cadastro-professor.php"><i class="fa fa-user" aria-hidden="true"></i> Meu Cadastro</a>
            <?php endif; ?>
            <a href="/termos-uso.html" target="_blank" rel="noopener">
                <i class="fa fa-file-contract" aria-hidden="true"></i> Termos de Uso
            </a>
            <a href="/politica-privacidade.html" target="_blank" rel="noopener">
                <i class="fa fa-user-shield" aria-hidden="true"></i> Política de Privacidade
            </a>
            <a href="#" id="btn-sair">
                <i class="fa fa-sign-out-alt" aria-hidden="true"></i> Sair
            </a>
        </div>
    </div>
</header>

<script src="js/sair.js"></script>
<script src="js/pusaber.js"></script>
<?php include __DIR__ . '/modal-geral.php'; ?>
