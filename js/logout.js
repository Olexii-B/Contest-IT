console.log("logout.js loaded");

function logout() {
  // Надсилає AJAX-запит до скрипту logout.php
  fetch('/php/logout.php')
  .then(response => {
  // Перенаправляє користувача на сторінку входу або будь-яку іншу бажану сторінку
  window.location.href = '/html/cover.html';
  })
  .catch(error => {
  console.error('Помилка при виході з системи:', error);
  });
}