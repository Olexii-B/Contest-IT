<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "1.php";

$dbName = "main_db";

$db = $dbcn;

// Створити базу даних, якщо її не існує 
mysqli_query($dbcn, "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE=utf8mb4_unicode_ci")
        or die("Error creating database: " . mysqli_error($dbcn));
mysqli_select_db($dbcn, $dbName) or die("Cannot select database: " . mysqli_error($dbcn));

$tables = [
    // Таблиця користувачів
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        class VARCHAR(255),  -- 
        role ENUM('student', 'teacher') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Таблиця змагань
    "CREATE TABLE IF NOT EXISTS competitions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        website VARCHAR(255) NOT NULL,
        startdate DATE NOT NULL,
        deadline DATE NOT NULL,
        description TEXT NOT NULL,
        classes_allowed VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Таблиця класів
    "CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_code VARCHAR(6) NOT NULL UNIQUE,  -- Автоматично згенерований унікальний код 
        name VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,  -- Хешований пароль
        teacher_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Таблиця належності до класу (зв'язок між учнем та класом)
    "CREATE TABLE IF NOT EXISTS class_memberships (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        student_id INT NOT NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // зберігання завантажених файлів
    "CREATE TABLE IF NOT EXISTS class_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        student_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_type VARCHAR(255) NOT NULL,
        file_size BIGINT NOT NULL,
        file_data LONGBLOB NOT NULL,
        competition_id INT NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id),
        FOREIGN KEY (student_id) REFERENCES users(id),
        FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    //зберігання постів ІТ-новин
    "CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        author_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        score INT DEFAULT 0,
        FOREIGN KEY (post_id) REFERENCES news(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS comment_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        comment_id INT NOT NULL,
        vote_type ENUM('like', 'dislike'),
        UNIQUE(user_id, comment_id), -- Запобігти повторному голосуванню
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (comment_id) REFERENCES comments(id)
    )",

    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_notification (user_id, content, type),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

// // Змінити таблицю, щоб додати стовпчик балів
// mysqli_query($dbcn, "ALTER TABLE comments ADD COLUMN score INT DEFAULT 0")
//     or die("Error altering table: " . mysqli_error($dbcn));

foreach ($tables as $sql) {
    mysqli_query($dbcn, $sql) or die("Error creating table: " . mysqli_error($dbcn));
}

if ($_SERVER['SERVER_ADDR'] !== $_SERVER['REMOTE_ADDR']){
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
?>