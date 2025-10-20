import {
    initResponsiveMenu,
    toggleUserMenu,
    bindUserMenuDismiss,
    mostrarAlerta,
    destacarPrimeiroElemento,
    iniciarDestaques,
} from './modules/menu';

if (typeof window !== 'undefined') {
    window.initResponsiveMenu = initResponsiveMenu;
    window.toggleUserMenu = toggleUserMenu;
    window.bindUserMenuDismiss = bindUserMenuDismiss;
    window.mostrarAlerta = mostrarAlerta;
    window.destacarPrimeiroElemento = destacarPrimeiroElemento;
    window.iniciarDestaques = iniciarDestaques;
}

document.addEventListener('DOMContentLoaded', () => {
    initResponsiveMenu();
    bindUserMenuDismiss();
    iniciarDestaques();
});
