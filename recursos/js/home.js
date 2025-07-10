document.addEventListener('DOMContentLoaded', () => {
    const botonScrollArriba = document.querySelector('.scroll-to-top');

    // Mostrar/ocultar el botón de scroll
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            botonScrollArriba.classList.add('visible');
        } else {
            botonScrollArriba.classList.remove('visible');
        }
    });

    // Scroll suave al hacer clic en el botón
    botonScrollArriba.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Funcionalidad para el fondo dinámico
    const elementosFondo = document.querySelectorAll('.background-item');
    const deslizadorFondo = document.querySelector('.background-slider');
    
    // Función para animar la opacidad de las imágenes
    const animarOpacidad = () => {
        const desplazado = window.pageYOffset;
        const altoVentana = window.innerHeight;
        const altoDocumento = Math.max(
            document.body.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.clientHeight,
            document.documentElement.scrollHeight,
            document.documentElement.offsetHeight
        );

        elementosFondo.forEach(elemento => {
            const rect = elemento.getBoundingClientRect();
            const estaEnVista = rect.top < altoVentana + 100 && rect.bottom > -100;
            
            if (estaEnVista) {
                elemento.style.opacity = '0.12';
            } else {
                elemento.style.opacity = '0.08';
            }
        });

        // Parallax suave que tiene en cuenta la altura total del documento
        const progresoScroll = desplazado / (altoDocumento - altoVentana);
        const trasladarY = desplazado * 0.1;
        deslizadorFondo.style.transform = `translateY(${trasladarY}px)`;
    };

    // Animar las imágenes cuando se hace scroll
    window.addEventListener('scroll', animarOpacidad);
    window.addEventListener('resize', animarOpacidad);
    
    // Animar las imágenes inicialmente
    animarOpacidad();

    // Animación suave para los elementos de la cuadrícula
    const elementosCuadricula = document.querySelectorAll('.grid-item');
    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach(entrada => {
            if (entrada.isIntersecting) {
                entrada.target.style.opacity = '1';
                entrada.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    elementosCuadricula.forEach(elemento => {
        elemento.style.opacity = '0';
        elemento.style.transform = 'translateY(20px)';
        elemento.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observador.observe(elemento);
    });
}); 