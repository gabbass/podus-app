document.addEventListener('DOMContentLoaded', function() {
    // Menu Mobile
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    
    mobileMenuBtn.addEventListener('click', function() {
        mainNav.classList.toggle('active');
    });
    
    // Fechar menu ao clicar em um link
    const navLinks = document.querySelectorAll('.main-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            mainNav.classList.remove('active');
        });
    });
    
    // Hero Slider
    const sliderContainer = document.querySelector('.slider-container');
    const slides = document.querySelectorAll('.slide');
    const dotsContainer = document.querySelector('.slider-dots');
    const prevBtn = document.querySelector('.prev-slide');
    const nextBtn = document.querySelector('.next-slide');
    
    let currentSlide = 0;
    const slideCount = slides.length;
    
    // Criar dots
    slides.forEach((slide, index) => {
        const dot = document.createElement('div');
        dot.classList.add('slider-dot');
        if(index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.slider-dot');
    
    function goToSlide(index) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        
        currentSlide = (index + slideCount) % slideCount;
        
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }
    
    function nextSlide() {
        goToSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        goToSlide(currentSlide - 1);
    }
    
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    // Slider automático
    let slideInterval = setInterval(nextSlide, 5000);
    
    sliderContainer.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    sliderContainer.addEventListener('mouseleave', () => {
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    // Testimonials Slider
    const testimonialsContainer = document.querySelector('.testimonials-slider');
    const testimonials = document.querySelectorAll('.testimonial-item');
    const testimonialsDotsContainer = document.querySelector('.testimonials-dots');
    const prevTestimonialBtn = document.querySelector('.prev-testimonial');
    const nextTestimonialBtn = document.querySelector('.next-testimonial');
    
    let currentTestimonial = 0;
    const testimonialCount = testimonials.length;
    
    // Criar dots para depoimentos
    testimonials.forEach((testimonial, index) => {
        const dot = document.createElement('div');
        dot.classList.add('testimonial-dot');
        if(index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToTestimonial(index));
        testimonialsDotsContainer.appendChild(dot);
    });
    
    const testimonialDots = document.querySelectorAll('.testimonial-dot');
    
    function goToTestimonial(index) {
        testimonials[currentTestimonial].classList.remove('active');
        testimonialDots[currentTestimonial].classList.remove('active');
        
        currentTestimonial = (index + testimonialCount) % testimonialCount;
        
        testimonials[currentTestimonial].classList.add('active');
        testimonialDots[currentTestimonial].classList.add('active');
    }
    
    function nextTestimonial() {
        goToTestimonial(currentTestimonial + 1);
    }
    
    function prevTestimonial() {
        goToTestimonial(currentTestimonial - 1);
    }
    
    nextTestimonialBtn.addEventListener('click', nextTestimonial);
    prevTestimonialBtn.addEventListener('click', prevTestimonial);
    
    // Testimonials automático
    let testimonialInterval = setInterval(nextTestimonial, 6000);
    
    testimonialsContainer.addEventListener('mouseenter', () => {
        clearInterval(testimonialInterval);
    });
    
    testimonialsContainer.addEventListener('mouseleave', () => {
        testimonialInterval = setInterval(nextTestimonial, 6000);
    });
    
    // Validação do formulário de newsletter
    const newsletterForm = document.querySelector('.newsletter-form');
    if(newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            if(emailInput.value && emailInput.value.includes('@')) {
                alert('Obrigado por assinar nossa newsletter!');
                emailInput.value = '';
            } else {
                alert('Por favor, insira um e-mail válido.');
            }
        });
    }
    
      // Scroll suave para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                const headerOffset = 100;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Adicionar classe ao scroll
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if(window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
});





// Adicione isso ao seu script.js
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenuClose = document.createElement('button');
    const mobileMenuOverlay = document.createElement('div');
    const mainNav = document.querySelector('.main-nav');
    
    // Cria o botão de fechar
    mobileMenuClose.className = 'mobile-menu-close';
    mobileMenuClose.innerHTML = '<i class="fas fa-times"></i>';
    mainNav.prepend(mobileMenuClose);
    
    // Cria o overlay
    mobileMenuOverlay.className = 'mobile-menu-overlay';
    document.body.appendChild(mobileMenuOverlay);
    
    // Abrir menu
    mobileMenuBtn.addEventListener('click', function() {
        mainNav.classList.add('active');
        mobileMenuOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    // Fechar menu
    mobileMenuClose.addEventListener('click', closeMenu);
    mobileMenuOverlay.addEventListener('click', closeMenu);
    
    // Fechar ao clicar em um link
    const navLinks = document.querySelectorAll('.main-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeMenu();
            }
        });
    });
    
    // Dropdown mobile
    const dropdowns = document.querySelectorAll('.dropdown > a');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('active');
                const menu = parent.querySelector('.dropdown-menu');
                menu.classList.toggle('active');
            }
        });
    });
    
    function closeMenu() {
        mainNav.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Fechar menu ao redimensionar para desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMenu();
        }
    });
});

  document.addEventListener('DOMContentLoaded', function() {
        var video = document.getElementById('manifesto-video');
        
        if(video) {
            // Reinicia o vídeo quando terminar
            video.addEventListener('ended', function() {
                this.currentTime = 0;
                this.pause();
            });
            
            // Para iOS: força o play quando o usuário interagir
            document.addEventListener('click', function firstPlay() {
                video.play().catch(e => console.log(e));
                document.removeEventListener('click', firstPlay);
            });
        }
    });
	
	document.addEventListener('DOMContentLoaded', function() {
    // Abrir lightbox
    document.querySelector('.open-methodology').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.methodology-lightbox').classList.add('show');
        document.body.style.overflow = 'hidden'; // Impede rolagem da página principal
    });
    
    // Fechar lightbox
    document.querySelector('.close-lightbox').addEventListener('click', function() {
        document.querySelector('.methodology-lightbox').classList.remove('show');
        document.body.style.overflow = 'auto'; // Restaura rolagem
    });
    
    // Fechar ao clicar fora do conteúdo
    document.querySelector('.methodology-lightbox').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Checa se já aceitou cookies
    if (localStorage.getItem('cookieAccepted') === 'yes') {
        document.getElementById('cookie-banner').style.display = 'none';
    }

    document.getElementById('btnAceitarCookies').onclick = function() {
        localStorage.setItem('cookieAccepted', 'yes');
        document.getElementById('cookie-banner').style.display = 'none';
    }
});