document.addEventListener('DOMContentLoaded', function () {
  // Sidebar Logic
  const menuTrigger = document.getElementById('menuTrigger');
  const closeSidebar = document.getElementById('closeSidebar');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  if (menuTrigger && sidebar) {
    menuTrigger.addEventListener('click', function () {
      sidebar.classList.add('active');
      if (overlay) overlay.classList.add('active');
    });
  }

  if (closeSidebar && sidebar) {
    closeSidebar.addEventListener('click', function () {
      sidebar.classList.remove('active');
      if (overlay) overlay.classList.remove('active');
    });
  }

  if (overlay && sidebar) {
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    });
  }

  // CPF Input Mask
  const cpfInput = document.getElementById('cpf');
  if (cpfInput) {
    cpfInput.addEventListener('input', function (e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 11) value = value.slice(0, 11);

      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

      e.target.value = value;
    });
  }

  // Product Preview Gallery Logic
  const thumbs = document.querySelectorAll('.product-gallery .thumb');
  const mainImage = document.querySelector('.product-gallery .main-image img');

  if (thumbs.length > 0 && mainImage) {
    thumbs.forEach(thumb => {
      thumb.addEventListener('click', function () {
        // Update main image
        const newSrc = this.querySelector('img').src;

        // Add simple fade effect
        mainImage.style.opacity = '0.5';
        setTimeout(() => {
          mainImage.src = newSrc;
          mainImage.style.opacity = '1';
        }, 150);

        // Update active state
        thumbs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });
  }

  // Option Buttons Logic (Colors and Sizes)
  const optionSections = document.querySelectorAll('.options-section');
  optionSections.forEach(section => {
    const buttons = section.querySelectorAll('.btn-option');
    buttons.forEach(btn => {
      btn.addEventListener('click', function () {
        // Remove active class from siblings
        buttons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Update hidden input if exists
        const value = this.getAttribute('data-value');
        if (section.id === 'section-cor') {
          const input = document.getElementById('input-cor');
          if (input) input.value = value;
        } else if (section.id === 'section-tamanho') {
          const input = document.getElementById('input-tamanho');
          if (input) input.value = value;
        }
      });
    });
  });
});