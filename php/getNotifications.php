<?php
require '5.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['notifications' => []]);
    exit();
}

$query = "
    SELECT n.id, u.content, u.type, n.is_read, n.created_at 
    FROM notifications n
    JOIN unique_notifications u ON n.notification_id = u.id
    WHERE n.user_id = ? AND n.is_read = FALSE 
    ORDER BY n.created_at DESC
";

if ($stmt = mysqli_prepare($dbcn, $query)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    echo json_encode(['notifications' => $notifications]);
    exit();
}

http_response_code(500);
echo json_encode(['notifications' => []]);
exit();
?>

