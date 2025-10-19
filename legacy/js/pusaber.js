// Menu
	document.addEventListener('DOMContentLoaded', function() {
		var menuToggle = document.getElementById('menu-toggle');
		var sidebar = document.getElementById('sidebar');
		var mainContent = document.getElementById('main-content');
		var closeSidebar = document.getElementById('close-sidebar');
		if (closeSidebar && sidebar) {
			closeSidebar.addEventListener('click', function() {
				sidebar.classList.remove('active');
				// Garante que some imediatamente
				sidebar.style.transform = "translateX(-100%)";
		});
									}
//Responsividade
	function setMargin() {
        if(window.innerWidth > 768) {
            // Ao voltar para desktop, sempre remove overlay mobile e garante menu visível
            sidebar.classList.remove('active');
            // Se não estiver comprimido, expande o menu (completo)
            if(!sidebar.classList.contains('collapsed')) {
                mainContent.style.marginLeft = '250px';
                sidebar.style.transform = "translateX(0)";
            } else {
                mainContent.style.marginLeft = '70px';
                sidebar.style.transform = "translateX(0)";
            }
        } else {
            // Mobile: remove compressão e deixa só overlay funcionando
            sidebar.classList.remove('collapsed');
            mainContent.style.marginLeft = '0';
            // Se não estiver ativo, esconde
            if (!sidebar.classList.contains('active')) {
                sidebar.style.transform = "translateX(-100%)";
            }
        }
    }

    if (menuToggle && sidebar && mainContent) {
        menuToggle.addEventListener('click', function() {
            if(window.innerWidth <= 768) {
                sidebar.classList.toggle('active');
                // Força visual
                sidebar.style.transform = sidebar.classList.contains('active') ? "translateX(0)" : "translateX(-100%)";
            } else {
                sidebar.classList.toggle('collapsed');
                setMargin();
            }
        });
        window.addEventListener('resize', setMargin);
        setMargin();
    }
			});

//Alerta
function mostrarAlerta(mensagem, tipo = 'success') {
    const area = document.getElementById('alertas-area');
    if (!area) return;
    area.innerHTML = `
        <div class="alert alert-${tipo}" style="margin-bottom:10px;">
            ${mensagem}
        </div>
    `;
    area.classList.remove('oculto'); // Mostra o alerta

    setTimeout(() => {
        area.innerHTML = '';
        area.classList.add('oculto'); // Esconde de novo
    }, 3000);
}

//Menu local
	function toggleUserMenu() {
		var menu = document.getElementById('user-menu');
		menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
	}

	document.addEventListener('mousedown', function(event) {
		var menu = document.getElementById('user-menu');
		var toggle = document.querySelector('.user-dropdown-toggle');
			if (!menu) return;
			// Se menu está aberto E clique não é nem no menu nem no botão, fecha
			if (menu.style.display === 'block' && 
				!menu.contains(event.target) && 
				!toggle.contains(event.target)) {
				menu.style.display = 'none';
			}
		});

// Máscara para telefone
	var telefoneInput = document.getElementById('telefone');
	if (telefoneInput) {
		telefoneInput.addEventListener('input', function(e) {
			var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
			e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
		});
	}
// Piscar
function rolarEDestacarPrimeiroDestaque() {
    // Seleciona o primeiro elemento com a classe 'destaque'
    const el = document.querySelector('.destaque');
    if (!el) return;

    // Rola até o elemento suavemente
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Remove piscar caso esteja, força reflow e adiciona novamente para reiniciar animação
    el.classList.remove('destaque-piscar');
    void el.offsetWidth; // força reflow (reset da animação)
    el.classList.add('destaque-piscar');

    // Remove a classe após a animação (opcional)
    setTimeout(() => {
        el.classList.remove('destaque-piscar');
    }, 2000); // 2 segundos (duração da animação)
}

document.querySelectorAll('.destaque').forEach(function(el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.classList.remove('destaque-piscar');
    void el.offsetWidth;
    el.classList.add('destaque-piscar');
    setTimeout(() => {
        el.classList.remove('destaque-piscar');
    }, 2000);
});
