const DEFAULT_BREAKPOINTS = [
    { name: 'mobile', width: 0, mode: 'overlay' },
    { name: 'tablet', width: 768, mode: 'overlay' },
    { name: 'desktop', width: 1024, mode: 'inline' },
];

const BREAKPOINT_CLASS_PREFIX = 'is-breakpoint-';

function parseBreakpoints(raw) {
    if (!raw) {
        return null;
    }

    if (Array.isArray(raw)) {
        return raw;
    }

    if (typeof raw === 'string') {
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed;
            }
        } catch (error) {
            console.warn('[menu] Não foi possível ler os breakpoints fornecidos.', error);
        }
    }

    return null;
}

function normaliseBreakpoints(breakpoints) {
    const source = parseBreakpoints(breakpoints) ?? DEFAULT_BREAKPOINTS;

    return source
        .map((bp, index) => {
            if (typeof bp === 'number') {
                return {
                    name: `bp-${index}`,
                    width: bp,
                    mode: index === source.length - 1 ? 'inline' : 'overlay',
                };
            }

            const width = Number(bp.width ?? bp.breakpoint ?? 0);
            const name = typeof bp.name === 'string' ? bp.name : `bp-${index}`;
            const mode = bp.mode === 'inline' ? 'inline' : 'overlay';

            return { name, width, mode };
        })
        .sort((a, b) => a.width - b.width);
}

function resolveContainer(root) {
    if (!root) {
        return null;
    }

    if (root instanceof Document) {
        return root.querySelector('[data-layout-container]') ?? root.body ?? null;
    }

    if (root.matches && root.matches('[data-layout-container]')) {
        return root;
    }

    return root.querySelector?.('[data-layout-container]') ?? root;
}

function syncToggleAria(container, toggle) {
    if (!toggle) {
        return;
    }

    const isOverlay = container.classList.contains('is-sidebar-overlay');
    let expanded;

    if (isOverlay) {
        expanded = container.classList.contains('is-sidebar-open');
    } else {
        expanded = !container.classList.contains('is-sidebar-collapsed');
    }

    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
}

function ensureOverlayState(container, backdrop) {
    const isOverlay = container.classList.contains('is-sidebar-overlay');
    const isOpen = container.classList.contains('is-sidebar-open');

    if (!isOverlay) {
        container.classList.remove('is-sidebar-hidden');
        if (backdrop) {
            backdrop.setAttribute('hidden', 'hidden');
        }
        return;
    }

    if (isOpen) {
        container.classList.remove('is-sidebar-hidden');
        if (backdrop) {
            backdrop.removeAttribute('hidden');
        }
    } else {
        container.classList.add('is-sidebar-hidden');
        if (backdrop) {
            backdrop.setAttribute('hidden', 'hidden');
        }
    }
}

function applyBreakpointState(container, breakpoints, toggle, backdrop) {
    const active = breakpoints.reduce((current, bp) => {
        if (window.innerWidth >= bp.width) {
            return bp;
        }
        return current;
    }, breakpoints[0]);

    breakpoints.forEach((bp) => {
        container.classList.remove(`${BREAKPOINT_CLASS_PREFIX}${bp.name}`);
    });

    container.classList.add(`${BREAKPOINT_CLASS_PREFIX}${active.name}`);
    container.dataset.layoutBreakpoint = active.name;

    if (active.mode === 'overlay') {
        container.classList.add('is-sidebar-overlay');
    } else {
        container.classList.remove('is-sidebar-overlay');
        container.classList.remove('is-sidebar-open');
        container.classList.remove('is-sidebar-hidden');
    }

    ensureOverlayState(container, backdrop);
    syncToggleAria(container, toggle);
}

export function initResponsiveMenu(root = document, options = {}) {
    const container = resolveContainer(root);
    const sidebar = container?.querySelector?.('#sidebar');

    if (!container || !sidebar || sidebar.dataset.menuInitialised === '1') {
        return;
    }

    const menuToggle = container.querySelector('#menu-toggle');
    const closeSidebar = container.querySelector('#close-sidebar');
    const backdrop = container.querySelector('[data-sidebar-backdrop]');

    const breakpoints = normaliseBreakpoints(options.breakpoints ?? container.dataset.menuBreakpoints);

    const handleResize = () => applyBreakpointState(container, breakpoints, menuToggle, backdrop);

    const openSidebar = () => {
        container.classList.add('is-sidebar-open');
        ensureOverlayState(container, backdrop);
        syncToggleAria(container, menuToggle);
    };

    const closeOverlaySidebar = () => {
        container.classList.remove('is-sidebar-open');
        ensureOverlayState(container, backdrop);
        syncToggleAria(container, menuToggle);
    };

    const toggleSidebar = () => {
        if (container.classList.contains('is-sidebar-overlay')) {
            if (container.classList.contains('is-sidebar-open')) {
                closeOverlaySidebar();
            } else {
                openSidebar();
            }
            return;
        }

        container.classList.toggle('is-sidebar-collapsed');
        syncToggleAria(container, menuToggle);
    };

    menuToggle?.addEventListener('click', toggleSidebar);
    closeSidebar?.addEventListener('click', closeOverlaySidebar);
    backdrop?.addEventListener('click', closeOverlaySidebar);

    window.addEventListener('resize', handleResize);
    handleResize();

    sidebar.dataset.menuInitialised = '1';
    container.dataset.menuInitialised = '1';
}

export function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    const toggle = document.querySelector('.user-dropdown-toggle');
    if (!menu) {
        return;
    }

    const isHidden = menu.hasAttribute('hidden');
    if (isHidden) {
        menu.removeAttribute('hidden');
        toggle?.setAttribute('aria-expanded', 'true');
    } else {
        menu.setAttribute('hidden', 'hidden');
        toggle?.setAttribute('aria-expanded', 'false');
    }
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

        if (!menu.hasAttribute('hidden') && !menu.contains(event.target) && !toggle.contains(event.target)) {
            menu.setAttribute('hidden', 'hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.body.dataset.userMenuBound = '1';
}

export function mostrarAlerta(mensagem, tipo = 'success') {
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
