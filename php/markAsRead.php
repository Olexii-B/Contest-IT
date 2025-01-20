<?php
require '5.php';

session_start();
header('Content-Type: application/json');

// Перевіряємо, чи користувач увійшов у систему
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
    exit();
}

// Отримуємо ID повідомлення з POST-запиту
$notificationId = $_POST['id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Не вказано ID повідомлення']);
    exit();
}

// Маркуємо повідомлення як прочитане
$query = "
    UPDATE notifications 
    SET is_read = TRUE 
    WHERE id = ? AND user_id = ?
";

if ($stmt = mysqli_prepare($dbcn, $query)) {
    mysqli_stmt_bind_param($stmt, 'ii', $notificationId, $userId);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
        exit();
    }
    mysqli_stmt_close($stmt);
}

// У разі помилки
http_response_code(500); // HTTP-код помилки
echo json_encode(['success' => false, 'message' => 'Не вдалося оновити повідомлення']);
exit();
?>
