<?php
require_once dirname(__DIR__) . '/../config/legacy.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$perfil = $_SESSION['perfil'] ?? '';
$menus = LegacyConfig::menuForProfile($perfil);
$sidebarTitle = LegacyConfig::sidebarTitle();
?>
<aside id="sidebar" class="layout-sidebar sidebar" data-menu-component>
    <div class="layout-sidebar__header sidebar-header">
        <div class="layout-sidebar__title">
            <h3><?= htmlspecialchars($sidebarTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
        </div>
        <button class="layout-sidebar__close close-sidebar" id="close-sidebar" type="button" aria-label="Fechar menu">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <nav class="layout-sidebar__nav" aria-label="Menu principal">
        <ul class="layout-sidebar__list sidebar-menu">
            <?php if (!empty($menus)): ?>
                <?php foreach ($menus as $item): ?>
                    <?php
                        [$label, $file, $icon] = $item;
                        $active = $currentPage === $file;
                    ?>
                    <li class="layout-sidebar__item">
                        <a href="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>"
                           class="layout-sidebar__link<?= $active ? ' is-active' : ''; ?>"
                           <?= $active ? 'aria-current="page"' : ''; ?>>
                            <?php if (!empty($icon)): ?>
                                <i class="fa <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                            <?php endif; ?>
                            <span class="layout-sidebar__label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
