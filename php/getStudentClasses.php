<?php
require '5.php';

header('Content-Type: application/json');
session_start();

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
    $studentId = $_SESSION['user_id'];
    
    $query = "
        SELECT c.id, c.class_code, c.name, cm.joined_at 
        FROM classes c
        JOIN class_memberships cm ON c.id = cm.class_id
        WHERE cm.student_id = ?
    ";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $classes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'classes' => $classes]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in or not a student']);
}
?>
