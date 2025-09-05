// Menú hamburguesa
(function(){
  const navbar = document.querySelector('.navbar');
  const btn = document.querySelector('.hamburger');
  const backdrop = document.querySelector('.menu-backdrop');
  const menu = document.getElementById('menu');

  if(!navbar || !btn || !menu) return;

  const toggle = () => {
    const isOpen = navbar.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', String(isOpen));
    // Evita scroll del body cuando el menú está abierto en móvil
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  };

  btn.addEventListener('click', toggle);
  backdrop && backdrop.addEventListener('click', toggle);

  // Cerrar al hacer click en un link (mejor UX en móvil)
  menu.addEventListener('click', (e) => {
    if(e.target.tagName === 'A'){
      navbar.classList.remove('is-open');
      btn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }
  });

  // Cerrar con tecla ESC
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape' && navbar.classList.contains('is-open')){
      toggle();
    }
  });
})();

// Cerrar con el botón "X" del header del panel
const closeBtn = document.querySelector('.menu-close');
closeBtn && closeBtn.addEventListener('click', () => {
  document.querySelector('.navbar')?.classList.remove('is-open');
  document.body.style.overflow = '';
});
