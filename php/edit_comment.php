<?php
require '5.php';
session_start();

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = (int)$_COOKIE['user_id'];
}

if (!isset($_SESSION['user_role']) && isset($_COOKIE['role'])) {
    $_SESSION['user_role'] = $_COOKIE['role'];
}

$commentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if (!$commentId || !$postId) {
    die('Необхідні параметри відсутні.');
}

// Validate comment ownership
$query = "SELECT content FROM comments WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($dbcn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $commentId, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$comment = mysqli_fetch_assoc($result);

if (!$comment) {
    die('Коментар не знайдено або ви не маєте доступу.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newContent = trim($_POST['content']);
    
    if (empty($newContent)) {
        die('Контент не може бути пустим');
    }
    
    $updateQuery = "UPDATE comments SET content = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($dbcn, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'sii', $newContent, $commentId, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);

    header("Location: view_post.php?id=" . htmlspecialchars($postId));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Редагувати коментар</title>
</head>
<body>
    <form action="edit_comment.php?id=<?= htmlspecialchars($commentId) ?>&post_id=<?= htmlspecialchars($postId) ?>" method="POST">
        <textarea name="content" rows="5" required><?= htmlspecialchars($comment['content']) ?></textarea>
        <button type="submit">Оновити</button>
    </form>
</body>
</html>
