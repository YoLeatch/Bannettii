
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
});