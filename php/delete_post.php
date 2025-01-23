<?php
require '5.php';
session_start();

if ($_SESSION['role'] !== 'teacher') {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['id'];
    $userId = $_SESSION['user_id'];

    // Почати транзакцію
    mysqli_begin_transaction($dbcn);

    try {
        // Видалити всі голоси, пов'язані з коментарями цього поста
        $queryDeleteVotes = "
            DELETE v 
            FROM comment_votes v
            JOIN comments c ON v.comment_id = c.id
            WHERE c.post_id = ?";
        $stmtDeleteVotes = mysqli_prepare($dbcn, $queryDeleteVotes);
        mysqli_stmt_bind_param($stmtDeleteVotes, 'i', $postId);
        mysqli_stmt_execute($stmtDeleteVotes);
        mysqli_stmt_close($stmtDeleteVotes);

        // Видалити всі коментарі, пов'язані з цим постом
        $queryDeleteComments = "DELETE FROM comments WHERE post_id = ?";
        $stmtDeleteComments = mysqli_prepare($dbcn, $queryDeleteComments);
        mysqli_stmt_bind_param($stmtDeleteComments, 'i', $postId);
        mysqli_stmt_execute($stmtDeleteComments);
        mysqli_stmt_close($stmtDeleteComments);

        // Видалити сам пост
        $queryDeletePost = "DELETE FROM news WHERE id = ? AND author_id = ?";
        $stmtDeletePost = mysqli_prepare($dbcn, $queryDeletePost);
        mysqli_stmt_bind_param($stmtDeletePost, 'ii', $postId, $userId);
        mysqli_stmt_execute($stmtDeletePost);
        mysqli_stmt_close($stmtDeletePost);

        // Підтвердити транзакцію
        mysqli_commit($dbcn);

        header('Location: news.php');
        exit;
    } catch (Exception $e) {
        // Відкотити транзакцію у разі помилки
        mysqli_rollback($dbcn);
        echo 'Error deleting post: ' . $e->getMessage();
    }
}

mysqli_close($dbcn);
?>

