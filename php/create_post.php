<?php
require '5.php'; // Database connection

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userRole = $_SESSION['role'] ?? null;
    $authorId = $_SESSION['user_id'] ?? null;

    if ($userRole !== 'teacher' || !$authorId) {
        echo "Unauthorized action.";
        exit;
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        echo "Title and content are required.";
        exit;
    }

    $query = "INSERT INTO news (title, content, author_id) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'ssi', $title, $content, $authorId);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: news.php');
        exit;
    } else {
        echo "Failed to create post: " . mysqli_error($dbcn);
    }
}
?>
