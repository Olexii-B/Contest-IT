<?php
require '5.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $commentId = $_POST['id'];
    $postId = $_POST['post_id'];

    $dbcn->begin_transaction();

    try {
        $voteQuery = "DELETE FROM comment_votes WHERE comment_id = ?";
        $voteStmt = mysqli_prepare($dbcn, $voteQuery);
        mysqli_stmt_bind_param($voteStmt, 'i', $commentId);
        mysqli_stmt_execute($voteStmt);

        $commentQuery = "DELETE c FROM comments c
                         JOIN news n ON c.post_id = n.id
                         WHERE c.id = ? AND (c.user_id = ? OR n.author_id = ?)";
        $commentStmt = mysqli_prepare($dbcn, $commentQuery);
        mysqli_stmt_bind_param($commentStmt, 'iii', $commentId, $userId, $userId);
        mysqli_stmt_execute($commentStmt);

        $dbcn->commit();
    } catch (Exception $e) {
        $dbcn->rollback();
        die("Error: " . $e->getMessage());
    }
}

header("Location: view_post.php?id=" . $_POST['post_id']);
exit;
?>

