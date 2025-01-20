<?php
require '5.php';
session_start();

if ($_SESSION['role'] !== 'teacher') {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $postId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    // Fetch the post
    $query = "SELECT * FROM news WHERE id = ? AND author_id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $postId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);

    if (!$post) {
        die('Post not found or you are not the author');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $userId = $_SESSION['user_id'];

    // Update the post
    $query = "UPDATE news SET title = ?, content = ? WHERE id = ? AND author_id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'ssii', $title, $content, $postId, $userId);
    if (mysqli_stmt_execute($stmt)) {
        header('Location: news.php');
        exit;
    } else {
        echo 'Error updating post.';
    }
}

mysqli_close($dbcn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
</head>
<body>
<form action="edit_post.php" method="POST">
    <input type="hidden" name="id" value="<?= $post['id'] ?>">
    <label for="title">Title:</label>
    <input type="text" name="title" id="title" value="<?= htmlspecialchars($post['title']) ?>" required>
    <br>
    <label for="content">Content:</label>
    <textarea name="content" id="content" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
    <br>
    <button type="submit">Update Post</button>
</form>
</body>
</html>
