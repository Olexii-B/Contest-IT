<?php
// Підключення до бази даних
require '5.php';

$db = $dbcn;

// Логування помилок у файл
function logError($message) {
    file_put_contents('debug.log', $message . PHP_EOL, FILE_APPEND);
}

// 1. Повідомлення для учнів про нові конкурси
function notifyNewCompetitions($db) {
    $competitions = $db->query("SELECT name FROM competitions WHERE DATE(startdate) = CURDATE()");
    if ($competitions->num_rows === 0) {
        return; // Немає нових конкурсів
    }
    
    $competition_names = [];
    while ($row = $competitions->fetch_assoc()) {
        $competition_names[] = $row['name'];
    }

    $students = $db->query("SELECT id FROM users WHERE role = 'student'");
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, content, type)
        SELECT ?, ?, 'info' 
        FROM DUAL 
        WHERE NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND content = ? AND type = 'info'
        )
    ");

    foreach ($students as $student) {
        foreach ($competition_names as $competition_name) {
            $content = "Новий конкурс: $competition_name. Перейдіть до сторінки конкурсів.";
            $stmt->bind_param('isis', $student['id'], $content, $student['id'], $content);
            $stmt->execute();
        }
    }
    $stmt->close();
}


// 2. Повідомлення про завершення конкурсів
function notifyCompetitionDeadlines($db) {
    $deadlines = [
        '1' => 'завтра',
        '7' => 'залишився тиждень'
    ];
    $students = $db->query("SELECT id FROM users WHERE role = 'student'");
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, content, type)
        SELECT ?, ?, ? 
        FROM DUAL 
        WHERE NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND content = ? AND type = ?
        )
    ");

    foreach ($deadlines as $days => $message) {
        $competitions = $db->query("SELECT name FROM competitions WHERE DATE(deadline) = CURDATE() + INTERVAL $days DAY");
        while ($competition = $competitions->fetch_assoc()) {
            $competition_name = $competition['name'];
            $type = ($days == 1) ? 'error' : 'warning';
            foreach ($students as $student) {
                $content = "Конкурс $competition_name завершується $message.";
                $stmt->bind_param('ississ', $student['id'], $content, $type, $student['id'], $content, $type);
                $stmt->execute();
            }
        }
    }
    $stmt->close();
}

// 3. Повідомлення про нових учнів у класах
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

    $notificationStmt = $db->prepare("
        INSERT INTO notifications (user_id, content, type)
        SELECT ?, ?, 'info' 
        FROM DUAL 
        WHERE NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND content = ? AND type = 'info'
        )
    ");
    while ($row = $result->fetch_assoc()) {
        $content = "Учень {$row['student_first_name']} {$row['student_last_name']} вступив до вашого класу.";
        $notificationStmt->bind_param('isis', $row['teacher_id'], $content, $row['teacher_id'], $content);
        $notificationStmt->execute();
    }
    $stmt->close();
    $notificationStmt->close();
}


// 4. Повідомлення про завантаження робіт
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

    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, content, type)
        SELECT ?, ?, 'success' 
        FROM DUAL 
        WHERE NOT EXISTS (
            SELECT 1 FROM notifications 
            WHERE user_id = ? AND content = ? AND type = 'success'
        )
    ");
    while ($upload = $uploads->fetch_assoc()) {
        $content = "Учень {$upload['student_name']} завантажив роботу на конкурс {$upload['competition_name']}.";
        $stmt->bind_param('isis', $upload['teacher_id'], $content, $upload['teacher_id'], $content);
        $stmt->execute();
    }
    $stmt->close();
}

// Виконання всіх функцій
try {
    notifyNewCompetitions($db);
    notifyCompetitionDeadlines($db);
    notifyStudentJoinedClass($db);
    notifyWorkUploaded($db);
    // echo "Notifications generated successfully.";
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
}

?>
