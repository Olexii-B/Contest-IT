<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require "5.php"; 

$class_name = mysqli_real_escape_string($dbcn, $_POST['class_name'] ?? '');
$class_password = $_POST['class_password'] ?? '';

if (empty($class_name) || empty($class_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Імя класу та пароль є обовязковими.']);
    exit;
}

$class_password_hashed = password_hash($class_password, PASSWORD_DEFAULT);

// Перевірка, чи клас з таким самим іменем вже існує
$check_query = "SELECT id FROM classes WHERE name = '$class_name'";
$check_result = mysqli_query($dbcn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Клас з таким іменем вже існує.']);
    exit;
}

// Згенеруйте унікальний код класу
do {
    $class_code = substr(md5(uniqid(mt_rand(), true)), 0, 6);
    $result = mysqli_query($dbcn, "SELECT id FROM classes WHERE class_code='$class_code'");
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Помилка в базі даних: ' . mysqli_error($dbcn)]);
        exit;
    }
} while (mysqli_num_rows($result) > 0);

// Вставка нового класу в базу даних
$sql = "INSERT INTO classes (class_code, name, password, teacher_id) VALUES ('$class_code', '$class_name', '$class_password_hashed', 1)";
if (mysqli_query($dbcn, $sql)) {
    echo json_encode(['status' => 'success', 'message' => 'Клас створено успішно', 'class_code' => $class_code]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Не вдалося створити клас: ' . mysqli_error($dbcn)]);
}

mysqli_close($dbcn);
?>
