<?php
require '5.php';

session_start();

$userId = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userRole === 'student') {
    $fileId = $_POST['file_id'] ?? null;

    if (!$fileId || !is_numeric($fileId)) {
        echo "Invalid file ID.";
        exit;
    }

    $query = "SELECT class_id FROM class_files WHERE id = ? AND student_id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $fileId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $classId = $row['class_id'];

        $deleteQuery = "DELETE FROM class_files WHERE id = ?";
        $deleteStmt = mysqli_prepare($dbcn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, 'i', $fileId);
        mysqli_stmt_execute($deleteStmt);

        if (mysqli_stmt_affected_rows($deleteStmt) > 0) {
            echo "File deleted successfully.";
        } else {
            echo "Failed to delete the file.";
        }
        mysqli_stmt_close($deleteStmt);
    } else {
        echo "You are not authorized to delete this file.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($dbcn);

    if (isset($classId)) {
        header("Location: /php/class.php?id=" . urlencode($classId));
        exit;
    } else {
        header("Location: /php/class.php");
        exit;
    }
} else {
    echo "Unauthorized request.";
    exit;
}
