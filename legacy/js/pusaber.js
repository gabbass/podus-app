// Arquivo legado mantido para compatibilidade. Toda a lógica de menu/alertas
// foi migrada para módulos ES6 carregados via Vite.

document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initResponsiveMenu === 'function') {
        window.initResponsiveMenu();
    }
    if (typeof window.bindUserMenuDismiss === 'function') {
        window.bindUserMenuDismiss();
    }
    if (typeof window.iniciarDestaques === 'function') {
        window.iniciarDestaques();
    }
});

window.mostrarAlerta = window.mostrarAlerta || function(mensagem, tipo = 'success') {
    const area = document.getElementById('alertas-area');
    if (!area) return;
    area.innerHTML = `
        <div class="alert alert-${tipo}" style="margin-bottom:10px;">
            ${mensagem}
        </div>
    `;
    area.classList.remove('oculto');
    setTimeout(() => {
        area.innerHTML = '';
        area.classList.add('oculto');
    }, 3000);
};

window.toggleUserMenu = window.toggleUserMenu || function() {
    const menu = document.getElementById('user-menu');
    if (!menu) return;
    const isVisible = menu.style.display === 'block';
    menu.style.display = isVisible ? 'none' : 'block';
};
