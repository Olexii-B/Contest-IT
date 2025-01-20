<?php
require '5.php'; // Database connection

header('Content-Type: application/json');
session_start();

// Check if the user is logged in and has the 'teacher' role
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'teacher') {
    $teacherID = $_SESSION['user_id'];

    $query = "SELECT id, class_code, name, created_at FROM classes WHERE teacher_id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $teacherID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $classes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row;
    }

    echo json_encode(['status' => 'success', 'classes' => $classes]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
}
?>
