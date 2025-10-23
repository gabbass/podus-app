// Arquivo legado mantido para compatibilidade. Toda a lógica de menu/alertas
// é delegada aos módulos ES6 carregados via Vite quando disponíveis.

document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.initResponsiveMenu === 'function') {
        window.initResponsiveMenu(document);
    }
    if (typeof window.bindUserMenuDismiss === 'function') {
        window.bindUserMenuDismiss();
    }
    if (typeof window.iniciarDestaques === 'function') {
        window.iniciarDestaques();
    }
});

window.mostrarAlerta = window.mostrarAlerta || function mostrarAlerta(mensagem, tipo = 'success') {
    const area = document.getElementById('alertas-area');
    if (!area) {
        return;
    }

    const safeTipo = typeof tipo === 'string' && tipo.trim() !== '' ? tipo.trim() : 'success';
    area.innerHTML = `
        <div class="alert alert-${safeTipo}">
            ${mensagem}
        </div>
    `;
    area.classList.remove('oculto');

    setTimeout(() => {
        area.innerHTML = '';
        area.classList.add('oculto');
    }, 3000);
};

window.toggleUserMenu = window.toggleUserMenu || function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    const toggle = document.querySelector('.user-dropdown-toggle');
    if (!menu || !toggle) {
        return;
    }

    const isHidden = menu.hasAttribute('hidden');
    if (isHidden) {
        menu.removeAttribute('hidden');
        toggle.setAttribute('aria-expanded', 'true');
    } else {
        menu.setAttribute('hidden', 'hidden');
        toggle.setAttribute('aria-expanded', 'false');
    }
};

if (typeof window.bindUserMenuDismiss !== 'function') {
    window.bindUserMenuDismiss = function bindUserMenuDismiss() {
        if (document.body.dataset.userMenuBound === '1') {
            return;
        }

        document.addEventListener('mousedown', (event) => {
            const menu = document.getElementById('user-menu');
            const toggle = document.querySelector('.user-dropdown-toggle');
            if (!menu || !toggle) {
                return;
            }

            if (!menu.hasAttribute('hidden') && !menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.setAttribute('hidden', 'hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.body.dataset.userMenuBound = '1';
    };
}
