<?php
require '5.php';

session_start();
header('Content-Type: application/json');

// Перевіряємо, чи користувач увійшов у систему
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['notifications' => []]);
    exit();
}

// Отримуємо непрочитані повідомлення
$query = "
    SELECT id, content, type, is_read, created_at 
    FROM notifications 
    WHERE user_id = ? AND is_read = FALSE 
    ORDER BY created_at DESC
";

if ($stmt = mysqli_prepare($dbcn, $query)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Перетворюємо результат у масив
    $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Повертаємо повідомлення у вигляді JSON
    echo json_encode(['notifications' => $notifications]);
    exit();
}

// У разі помилки запиту повертаємо порожній масив
http_response_code(500); // Встановлюємо HTTP-код помилки
echo json_encode(['notifications' => []]);
exit();
?>
