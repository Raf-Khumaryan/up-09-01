// Открытие поп-ап окна
document.querySelector('.open-popup-btn').addEventListener('click', function() {
  document.getElementById('callback-popup').style.display = 'block';
});

// Закрытие поп-ап окна
document.getElementById('close-popup').addEventListener('click', function() {
  document.getElementById('callback-popup').style.display = 'none';
});

// Закрытие поп-ап при клике вне окна
window.addEventListener('click', function(event) {
  let popup = document.getElementById('callback-popup');
  if (event.target == popup) {
      popup.style.display = 'none';
  }
});

