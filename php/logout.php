<?php
session_start();

session_destroy();

//Видалити файли cookie user_id та role
setcookie('user_id', '', time() - 86400, '/');
setcookie('role', '', time() - 86400, '/');

// Перенаправлення користувача на сторінку входу в систему
header('Location: /html/cover.html');
exit();
?>
