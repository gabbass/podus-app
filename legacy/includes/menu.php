<?php
require_once dirname(__DIR__) . '/../config/legacy.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$perfil = $_SESSION['perfil'] ?? '';
$menus = LegacyConfig::menuForProfile($perfil);
$sidebarTitle = LegacyConfig::sidebarTitle();
?>
<aside class="sidebar active" id="sidebar">
    <div class="sidebar-header">
        <h3><?php echo htmlspecialchars($sidebarTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
        <button class="close-sidebar" id="close-sidebar" onclick="document.querySelector('.sidebar').classList.remove('active')" >&times;</button>
    </div>
   <ul class="sidebar-menu">
    <?php
    if (!empty($menus)) {
        foreach ($menus as $item) {
            [$label, $file, $icon] = $item;
            $active = $currentPage === $file ? 'active' : '';
            echo <<<HTML
            <li>
                <a href="$file" class="$active"><i class="fa $icon"></i> <span>$label</span></a>
            </li>
            HTML;
        }
    }
    ?>
</ul>

</aside>

<script src="js/pusaber.js"></script>
