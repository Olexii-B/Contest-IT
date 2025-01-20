<?php
require '5.php';

$classId = $_POST['class_id'] ?? null;
$userId = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null;

if ($classId && $userId) {
    // Remove the student from the class
    $query = "DELETE FROM class_memberships WHERE class_id = ? AND student_id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $classId, $userId);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "You have successfully left the class.";
    } else {
        echo "Error leaving the class or you were not a member of this class.";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Invalid class ID or user ID.";
}

mysqli_close($dbcn);
?>
