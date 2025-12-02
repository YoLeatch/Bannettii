
function mostrarBarraLateral() {
  const sidebar = document.querySelector('.sidebar');
  sidebar.style.display = 'flex';
  sidebar.style.animation = 'appear 0.2s'
}

function esconderBarraLateral() {
  const sidebar = document.querySelector('.sidebar');
  sidebar.style.animation = 'disappear 0.2s';
  sidebar.style.display = 'none';
}

/*document.getElementById('celular').addEventListener('input', function (e) {
  var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
  e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
});*/

document.addEventListener('DOMContentLoaded', function () {
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