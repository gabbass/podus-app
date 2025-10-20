const MOBILE_BREAKPOINT = 768;

function setSidebarMargin({ sidebar, mainContent }) {
    if (!sidebar || !mainContent) {
        return;
    }

    if (window.innerWidth > MOBILE_BREAKPOINT) {
        sidebar.classList.remove('active');
        if (!sidebar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '250px';
            sidebar.style.transform = 'translateX(0)';
        } else {
            mainContent.style.marginLeft = '70px';
            sidebar.style.transform = 'translateX(0)';
        }
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.style.marginLeft = '0';
        if (!sidebar.classList.contains('active')) {
            sidebar.style.transform = 'translateX(-100%)';
        }
    }
}

export function initResponsiveMenu(root = document) {
    if (!root) {
        return;
    }

    const sidebar = root.querySelector('#sidebar');
    if (!sidebar || sidebar.dataset.menuInitialised === '1') {
        return;
    }

    const menuToggle = root.querySelector('#menu-toggle');
    const mainContent = root.querySelector('#main-content');
    const closeSidebar = root.querySelector('#close-sidebar');

    const context = { sidebar, mainContent };

    const resizeHandler = () => setSidebarMargin(context);

    if (closeSidebar) {
        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebar.style.transform = 'translateX(-100%)';
        });
    }

    if (menuToggle && sidebar && mainContent) {
        menuToggle.addEventListener('click', () => {
            if (window.innerWidth <= MOBILE_BREAKPOINT) {
                sidebar.classList.toggle('active');
                sidebar.style.transform = sidebar.classList.contains('active')
                    ? 'translateX(0)'
                    : 'translateX(-100%)';
            } else {
                sidebar.classList.toggle('collapsed');
                setSidebarMargin(context);
            }
        });
        window.addEventListener('resize', resizeHandler);
        setSidebarMargin(context);
    }

    sidebar.dataset.menuInitialised = '1';
}

export function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    if (!menu) {
        return;
    }

    const isVisible = menu.style.display === 'block';
    menu.style.display = isVisible ? 'none' : 'block';
}

export function bindUserMenuDismiss() {
    if (document.body.dataset.userMenuBound === '1') {
        return;
    }

    document.addEventListener('mousedown', (event) => {
        const menu = document.getElementById('user-menu');
        const toggle = document.querySelector('.user-dropdown-toggle');
        if (!menu || !toggle) {
            return;
        }

        if (menu.style.display === 'block' && !menu.contains(event.target) && !toggle.contains(event.target)) {
            menu.style.display = 'none';
        }
    });

    document.body.dataset.userMenuBound = '1';
}

export function mostrarAlerta(mensagem, tipo = 'success') {
    const area = document.getElementById('alertas-area');
    if (!area) {
        return;
    }

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
}

export function destacarPrimeiroElemento() {
    const element = document.querySelector('.destaque');
    if (!element) {
        return;
    }

    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    element.classList.remove('destaque-piscar');
    void element.offsetWidth;
    element.classList.add('destaque-piscar');

    setTimeout(() => {
        element.classList.remove('destaque-piscar');
    }, 2000);
}

export function iniciarDestaques() {
    document.querySelectorAll('.destaque').forEach((el) => {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.remove('destaque-piscar');
        void el.offsetWidth;
        el.classList.add('destaque-piscar');
        setTimeout(() => {
            el.classList.remove('destaque-piscar');
        }, 2000);
    });
}
