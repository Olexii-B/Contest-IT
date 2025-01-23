<?php
require '5.php';

session_start();

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

$query = "SELECT news.id, news.title, news.content, news.created_at, 
                 users.id AS author_id, users.first_name, users.last_name 
          FROM news 
          JOIN users ON news.author_id = users.id 
          ORDER BY news.created_at DESC";
$result = mysqli_query($dbcn, $query);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($dbcn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

    <link href="/css/style.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .card {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .card-preview {
            color: #555;
        }
        .card-footer {
            font-size: 0.875rem;
            color: #777;
        }
    </style>
</head>
    <?php if ($userRole === 'teacher'): ?>
        <!--учительсий навбар початок-->
            <div class="cover-container d-flex w-100 p-3 mx-auto flex-column">
                <header class="mb-auto">
                    <div class="cover-container d-flex justify-content-between align-items-center">
                        <h3 class="float-md-start mb-0 text-dark">Contest-IT для Вчителів</h3>
                        <nav class="nav nav-masthead justify-content-center float-md-end">
                            <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/create-comp.html">Додати Конкурс</a>
                            <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/competitions.html">Конкурси</a>
                            <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/create-class.html">Створити клас</a>
                            <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/php/news.php">Дописи</a>
                            <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/teachers.html">Головна</a>
                        </nav>
                    </div>
                    <hr>
                </header>
            </div>
        <?php elseif ($userRole === 'student'): ?>
            <!--учнівський навбар початок-->
            <div class="cover-container d-flex w-100 p-3 mx-auto flex-column">
                <header class="mb-auto">
                    <div class="cover-container d-flex justify-content-between align-items-center">
                        <h3 class="float-md-start mb-0 text-dark">Contest-IT для Учнів</h3>
                        <nav class="nav nav-masthead justify-content-center float-md-end">
                        <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/competitions.html">Конкурси</a>
                        <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/enter-class.html">Вступити в Клас</a>
                        <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/php/news.php">Дописи</a>
                        <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/students.html">Головна</a>
                        </nav>
                    </div>
                    <hr>
                </header>
            </div>
        
        <?php else: ?>
            <!--навбар для гостей початок -->
            <div class="cover-container d-flex w-100 p-3 mx-auto flex-column">
                <header class="mb-auto">
                    <div class="cover-container d-flex justify-content-between align-items-center">
                        <h3 class="float-md-start mb-0 text-dark">Contest-IT</h3>
                        <nav class="nav nav-masthead justify-content-center float-md-end">
                        <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/php/news.php">Дописи</a>
                        <a class="nav-link fw-bold py-1 px-0 custom-nav-link text-dark" href="/html/cover.html">Головна</a>
                        </nav>
                    </div>
                    <hr>
                </header>
            </div>
        <?php endif; ?>


            <style>
                .nav-link:hover,
                .nav-link:focus {
                    color: #007bff;
                    text-decoration: underline;
                }

                .dropdown-menu .dropdown-item:hover,
                .dropdown-menu .dropdown-item:focus {
                    background-color: #e9ecef;
                    color: #000;
                }

                .custom-nav-link {
                    margin: 0 10px 0 10px;
                }

                body{
                    display: flex;
                    flex-direction: column;
                    min-height: 100vh;
                }
            </style>
        </div>
        <!--навбар кінець-->

<body>
<div class="container mt-5">
    <h1>Дописи</h1>

    <?php if ($userRole === 'teacher'): ?>
        <h3>Створити новий допис</h3>
        <form action="create_post.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Заголовок</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Зміст</label>
                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Створити допис</button>
        </form>
    <?php endif; ?>

    <h3 class="mt-5">Нещодавні дописи</h3>
    <div class="card-container">
        <?php foreach ($posts as $post): ?>
            <div class="card">
                <div>
                    <h4 class="card-title"><?= htmlspecialchars($post['title']) ?></h4>
                    <p class="card-preview"><?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</p>
                </div>
                <div class="card-footer">
                    Автор: <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?><br>
                    Дата: <?= htmlspecialchars($post['created_at']) ?><br>
                    <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary mt-2">Переглянути</a>

                    <?php if ($userRole === 'teacher' && $userId == $post['author_id']): ?>
                        <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-warning mt-2">Редагувати</a>
                        <form action="delete_post.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $post['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger mt-2" 
                                    onclick="return confirm('Ви впевнені, що хочете видалити цей допис?');">
                                Видалити
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>

