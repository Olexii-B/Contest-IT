<?php
require '5.php';
session_start();

if ($_SESSION['role'] !== 'teacher') {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['id'];
    $userId = $_SESSION['user_id'];

    // Delete the post
    $query = "DELETE FROM news WHERE id = ? AND author_id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $postId, $userId);
    if (mysqli_stmt_execute($stmt)) {
        header('Location: news.php');
        exit;
    } else {
        echo 'Error deleting post.';
    }
}

mysqli_close($dbcn);
?>
