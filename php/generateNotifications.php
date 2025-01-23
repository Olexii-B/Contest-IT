<?php
// Підключення до бази даних
require '5.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$db = $dbcn;

// Логування помилок у файл
function logError($message) {
    file_put_contents('debug.log', $message . PHP_EOL, FILE_APPEND);
}

// 1. Повідомлення для учнів про нові конкурси
function notifyNewCompetitions($db) {
    $competitions = $db->query("SELECT name FROM competitions WHERE DATE(startdate) = CURDATE()");
    if ($competitions->num_rows === 0) {
        return;
    }

    $competition_names = [];
    while ($row = $competitions->fetch_assoc()) {
        $competition_names[] = $row['name'];
    }

    // Додавання унікальних повідомлень до таблиці `unique_notifications`
    $stmtInsertUnique = $db->prepare("
        INSERT INTO unique_notifications (content, type)
        SELECT ?, 'info'
        FROM DUAL
        WHERE NOT EXISTS (
            SELECT 1 FROM unique_notifications WHERE content = ?
        )
    ");
    foreach ($competition_names as $competition_name) {
        $content = "Розпочався конкурс: $competition_name. Перейдіть до сторінки змагань і візьміть в ньому участь!";
        $stmtInsertUnique->bind_param('ss', $content, $content);
        $stmtInsertUnique->execute();
    }
    $stmtInsertUnique->close();

    // Зв’язування повідомлень з користувачами
    $students = $db->query("SELECT id FROM users WHERE role = 'student'");
    $stmtLinkNotification = $db->prepare("
        INSERT INTO notifications (user_id, notification_id)
        SELECT ?, n.id
        FROM unique_notifications n
        WHERE n.content = ?
        AND NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND notification_id = n.id
        )
    ");
    foreach ($students as $student) {
        foreach ($competition_names as $competition_name) {
            $content = "Розпочався конкурс: $competition_name. Перейдіть до сторінки змагань і візьміть в ньому участь!";
            $stmtLinkNotification->bind_param('isi', $student['id'], $content, $student['id']);
            $stmtLinkNotification->execute();
        }
    }
    $stmtLinkNotification->close();
}



//2. Повідомлення про нових учнів у класах
function notifyStudentJoinedClass($db) {
    $stmt = $db->prepare("
        SELECT u.first_name AS student_first_name, u.last_name AS student_last_name, c.teacher_id 
        FROM class_memberships cm
        JOIN users u ON cm.student_id = u.id
        JOIN classes c ON cm.class_id = c.id
        WHERE DATE(cm.joined_at) = CURDATE()
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return;
    }

    // Insert into `unique_notifications`
    $stmtInsertUnique = $db->prepare("
        INSERT INTO unique_notifications (content, type)
        VALUES (?, 'info')
        ON DUPLICATE KEY UPDATE id = id
    ");

    // Link notifications to the teacher
    $stmtLinkNotification = $db->prepare("
        INSERT INTO notifications (user_id, notification_id)
        SELECT ?, n.id
        FROM unique_notifications n
        WHERE n.content = ?
        AND NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND notification_id = n.id
        )
    ");

    while ($row = $result->fetch_assoc()) {
        $content = "Учень {$row['student_first_name']} {$row['student_last_name']} вступив до вашого класу.";
        $stmtInsertUnique->bind_param('s', $content);
        $stmtInsertUnique->execute();

        $stmtLinkNotification->bind_param('isi', $row['teacher_id'], $content, $row['teacher_id']);
        $stmtLinkNotification->execute();
    }

    $stmt->close();
    $stmtInsertUnique->close();
    $stmtLinkNotification->close();
}



//3. Повідомлення про завантаження робіт
function notifyWorkUploaded($db) {
    $uploads = $db->query("
        SELECT 
            CONCAT(s.first_name, ' ', s.last_name) AS student_name, 
            cls.teacher_id AS teacher_id, 
            c.name AS competition_name 
        FROM class_files w
        JOIN users s ON w.student_id = s.id
        JOIN competitions c ON w.competition_id = c.id
        JOIN classes cls ON w.class_id = cls.id
        WHERE DATE(w.uploaded_at) = CURDATE()
    ");
    if ($uploads->num_rows === 0) {
        return;
    }

    $stmtInsertUnique = $db->prepare("
        INSERT INTO unique_notifications (content, type)
        VALUES (?, 'success')
        ON DUPLICATE KEY UPDATE id = id
    ");

    $stmtLinkNotification = $db->prepare("
        INSERT INTO notifications (user_id, notification_id)
        SELECT ?, n.id
        FROM unique_notifications n
        WHERE n.content = ?
        AND NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND notification_id = n.id
        )
    ");

    while ($upload = $uploads->fetch_assoc()) {
        $content = "Учень {$upload['student_name']} завантажив роботу на конкурс {$upload['competition_name']}.";
        $stmtInsertUnique->bind_param('s', $content);
        $stmtInsertUnique->execute();

        $stmtLinkNotification->bind_param('isi', $upload['teacher_id'], $content, $upload['teacher_id']);
        $stmtLinkNotification->execute();
    }

    $stmtInsertUnique->close();
    $stmtLinkNotification->close();
}

//4. Повідомлення про завершення конкурсів
function notifyCompetitionDeadlines($db) {
    $deadlines = [
        '1' => 'завтра',
        '7' => 'залишився тиждень'
    ];
    $stmtInsertUnique = $db->prepare("
        INSERT INTO unique_notifications (content, type)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE id = id
    ");

    $stmtLinkNotification = $db->prepare("
        INSERT INTO notifications (user_id, notification_id)
        SELECT ?, n.id
        FROM unique_notifications n
        WHERE n.content = ?
        AND NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND notification_id = n.id
        )
    ");

    foreach ($deadlines as $days => $message) {
        $competitions = $db->query("SELECT name FROM competitions WHERE DATE(deadline) = CURDATE() + INTERVAL $days DAY");
        while ($competition = $competitions->fetch_assoc()) {
            $competition_name = $competition['name'];
            $type = ($days == 1) ? 'error' : 'warning';
            $content = "Конкурс $competition_name завершується $message.";

            $stmtInsertUnique->bind_param('ss', $content, $type);
            $stmtInsertUnique->execute();

            $students = $db->query("SELECT id FROM users WHERE role = 'student'");
            foreach ($students as $student) {
                $stmtLinkNotification->bind_param('isi', $student['id'], $content, $student['id']);
                $stmtLinkNotification->execute();
            }
        }
    }

    $stmtInsertUnique->close();
    $stmtLinkNotification->close();
}

//5. Повідомлення учнів про нові пости
function notifyNewPost($db) {
    $posts = $db->query("
        SELECT n.id, n.title, u.first_name, u.last_name
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE DATE(n.created_at) = CURDATE()
    ");
    if ($posts->num_rows === 0) {
        return;
    }

    $stmtInsertUnique = $db->prepare("
        INSERT INTO unique_notifications (content, type)
        VALUES (?, 'info')
        ON DUPLICATE KEY UPDATE id = id
    ");

    $stmtLinkNotification = $db->prepare("
        INSERT INTO notifications (user_id, notification_id)
        SELECT ?, n.id
        FROM unique_notifications n
        WHERE n.content = ?
        AND NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND notification_id = n.id
        )
    ");

    $students = $db->query("SELECT id FROM users WHERE role = 'student'");

    while ($post = $posts->fetch_assoc()) {
        $content = "Вчитель {$post['first_name']} {$post['last_name']} опублікував пост: '{$post['title']}'.";

        $stmtInsertUnique->bind_param('s', $content);
        $stmtInsertUnique->execute();

        foreach ($students as $student) {
            $stmtLinkNotification->bind_param('isi', $student['id'], $content, $student['id']);
            $stmtLinkNotification->execute();
        }
    }

    $stmtInsertUnique->close();
    $stmtLinkNotification->close();
}

//6. Повідомлення про коментарі поста
function notifyPostComment($db) {
    $comments = $db->query("
        SELECT c.id AS comment_id, c.post_id, c.user_id, n.title AS post_title, u.first_name, u.last_name, n.author_id AS post_author_id
        FROM comments c
        JOIN news n ON c.post_id = n.id
        JOIN users u ON c.user_id = u.id
        WHERE DATE(c.created_at) = CURDATE()
    ");
    if ($comments->num_rows === 0) {
        return;
    }

    $stmtInsertUnique = $db->prepare("
        INSERT INTO unique_notifications (content, type)
        VALUES (?, 'info')
        ON DUPLICATE KEY UPDATE id = id
    ");

    $stmtLinkNotification = $db->prepare("
        INSERT INTO notifications (user_id, notification_id)
        SELECT ?, n.id
        FROM unique_notifications n
        WHERE n.content = ?
        AND NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND notification_id = n.id
        )
    ");

    while ($comment = $comments->fetch_assoc()) {
        $content = "Користувач {$comment['first_name']} {$comment['last_name']} залишив коментар під постом '{$comment['post_title']}'.";

        $stmtInsertUnique->bind_param('s', $content);
        $stmtInsertUnique->execute();

        $stmtLinkNotification->bind_param('isi', $comment['post_author_id'], $content, $comment['post_author_id']);
        $stmtLinkNotification->execute();
    }

    $stmtInsertUnique->close();
    $stmtLinkNotification->close();
}


// Виконання всіх функцій
try {
    notifyNewCompetitions($db);
    notifyStudentJoinedClass($db);
    notifyWorkUploaded($db);
    notifyCompetitionDeadlines($db);

    notifyNewPost($db);
    notifyPostComment($db);
    // echo "Notifications generated successfully.";
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
}

?>

