<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

require '5.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str);

    $email = $json_obj->email ?? '';
    $password = $json_obj->password ?? '';
    $last_name = $json_obj->lastName ?? '';
    $first_name = $json_obj->firstName ?? '';
    $role = $json_obj->role ?? '';
    $class = isset($json_obj->class) ? $json_obj->class : null; //Тільки для студентів

    if (empty($email) || empty($password) || empty($last_name) || empty($first_name) || empty($role)) {
        echo json_encode(['error' => 'All fields are required.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Неправильна адреса електронної пошти.']);
        exit();
    }

    // Перевірити таблицю користувачів на наявність email-адреси
    $checkUserStmt = mysqli_prepare($dbcn, "SELECT email FROM users WHERE email = ?");
    mysqli_stmt_bind_param($checkUserStmt, 's', $email);
    mysqli_stmt_execute($checkUserStmt);
    $resultUser = mysqli_stmt_get_result($checkUserStmt);

    // Email вже використовується.
    if (mysqli_num_rows($resultUser) > 0) {
        echo json_encode(['error' => 'Цю електронну адресу вже використовують.']);
        mysqli_stmt_close($checkUserStmt);
        exit();
    }

    // Закрити обробник інструкцій після перевірок  
    mysqli_stmt_close($checkUserStmt);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'student') {
        $stmt = mysqli_prepare($dbcn, "INSERT INTO users (email, password, last_name, first_name, class, role) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssss', $email, $hashed_password, $last_name, $first_name, $class, $role);
    } else {
        $stmt = mysqli_prepare($dbcn, "INSERT INTO users (email, password, last_name, first_name, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $email, $hashed_password, $last_name, $first_name, $role);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => 'Користувача успішно зареєстровано.', 'email' => $email]);
    } else {
        error_log('User registration failed: ' . mysqli_stmt_error($stmt));
        echo json_encode(['error' => 'Не вдалося зареєструвати користувача.']);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'Неправильний метод запиту.']);
}
?>