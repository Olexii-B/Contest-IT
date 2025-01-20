<?php
require '5.php'; // Підключення до бази даних

session_start();

if (!isset($_GET['id'])) {
    die('Допис не знайдено');
}

$postId = $_GET['id'];

// Вибірка інформації про допис
$query = "SELECT news.title, news.content, news.created_at, 
                 news.author_id, users.first_name, users.last_name 
          FROM news 
          JOIN users ON news.author_id = users.id 
          WHERE news.id = ?";
$stmt = mysqli_prepare($dbcn, $query);
mysqli_stmt_bind_param($stmt, 'i', $postId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

// Отримати коментарі до посту
$commentsQuery = "SELECT comments.id, comments.content, comments.created_at, comments.score, 
                         users.first_name, users.last_name, users.id AS user_id 
                  FROM comments 
                  JOIN users ON comments.user_id = users.id 
                  WHERE comments.post_id = ? 
                  ORDER BY comments.created_at DESC";
$stmt = mysqli_prepare($dbcn, $commentsQuery);
mysqli_stmt_bind_param($stmt, 'i', $postId);
mysqli_stmt_execute($stmt);
$commentsResult = mysqli_stmt_get_result($stmt);
$comments = mysqli_fetch_all($commentsResult, MYSQLI_ASSOC);

if (!$post) {
    die('Допис не знайдено');
}

// Get current user info
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['user_role'] ?? null;

mysqli_close($dbcn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<a href="news.php" class="btn btn-secondary mt-3" style="margin:10px;">Повернутися</a>
<div class="container mt-5">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="text-muted">
        Автор: <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?> |
        Дата: <?= htmlspecialchars($post['created_at']) ?>
    </p>
    <hr>
    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <?php if (isset($_SESSION['user_id'])): ?> <!-- Залишити коментар -->
    <form action="add_comment.php" method="POST" class="mt-5">
        <input type="hidden" name="post_id" value="<?= $postId ?>">
        <div class="mb-3">
            <label for="comment" class="form-label">Ваш коментар:</label>
            <textarea id="comment" name="content" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Додати коментар</button>
    </form>
    <?php else: ?>
        <p class="text-muted mt-5">Увійдіть, щоб залишити коментар.</p>
    <?php endif; ?>

    <h3 class="mt-5">Коментарі</h3> <!-- Коментарі інших -->
    <?php if (!empty($comments)): ?>
        <ul class="list-group">
            <?php foreach ($comments as $comment): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?></strong> 
                    <span class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></span>
                    <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="d-flex align-items-center gap-2">    
                            <form action="like_comment.php" method="POST">
                                <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="post_id" value="<?= $postId ?>">
                                <button type="submit" class="btn btn-sm btn-success">👍</button>
                            </form>
                            <form action="dislike_comment.php" method="POST">
                                <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="post_id" value="<?= $postId ?>">
                                <button type="submit" class="btn btn-sm btn-danger">👎</button>
                            </form>
                        </div>
                        <?php else: ?>
                            <span class="text-muted">Увійдіть, щоб оцінити коментар.</span>
                    <?php endif; ?>
                    <span>Оцінка: <?= htmlspecialchars($comment['score']) ?></span>

                    <?php if ($userId === $comment['user_id'] || $userId === $post['author_id']): ?>
                        <div class="d-flex gap-2">
                            <a href="edit_comment.php?id=<?= $comment['id'] ?>&post_id=<?= $postId ?>" class="btn btn-sm btn-warning">Редагувати</a>
                            <form action="delete_comment.php" method="POST" onsubmit="return confirm('Ви впевнені, що хочете видалити цей коментар?');">
                                <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="post_id" value="<?= $postId ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">Немає коментарів. Будьте першим!</p>
    <?php endif; ?>
</div>
<a href="news.php" class="btn btn-secondary mt-3" style="margin:10px;">Повернутися</a>
</body>
</html>
