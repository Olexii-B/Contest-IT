<?php
require '5.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $postId = $_POST['post_id'];
    $content = trim($_POST['content']);

    if (!empty($content)) {
        $query = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($dbcn, $query);
        mysqli_stmt_bind_param($stmt, 'iis', $postId, $userId, $content);
        mysqli_stmt_execute($stmt);
    }
}

header("Location: view_post.php?id=$postId");
exit;
?>
