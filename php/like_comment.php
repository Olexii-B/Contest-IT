<?php
require '5.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['post_id'])) {
    $commentId = $_POST['id'];
    $postId = $_POST['post_id'];
    $userId = $_SESSION['user_id'];

    // Перевірити, чи користувач вже вподобав коментар
    $checkQuery = "SELECT * FROM comment_votes WHERE user_id = ? AND comment_id = ?";
    $checkStmt = mysqli_prepare($dbcn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, 'ii', $userId, $commentId);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($result) === 0) {
        // Збільшувати оцінку і записувати
        $updateQuery = "UPDATE comments SET score = score + 1 WHERE id = ?";
        $stmt = mysqli_prepare($dbcn, $updateQuery);
        mysqli_stmt_bind_param($stmt, 'i', $commentId);
        mysqli_stmt_execute($stmt);

        $insertQuery = "INSERT INTO comment_votes (user_id, comment_id, vote_type) VALUES (?, ?, 'like')";
        $insertStmt = mysqli_prepare($dbcn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, 'ii', $userId, $commentId);
        mysqli_stmt_execute($insertStmt);
    }
}

header("Location: view_post.php?id=" . $postId);
exit;
?>
