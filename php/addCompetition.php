<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '5.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // Повертає помилку, якщо HTTP-запит не є POST
    echo json_encode(['error' => 'Неправильний метод запиту.']);
    exit();
}

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str);

$name = $json_obj->name ?? '';
$website = $json_obj->website ?? '';
$startdate = $json_obj->startdate ?? '';
$deadline = $json_obj->deadline ?? '';
$description = $json_obj->description ?? '';
$classesAllowed = implode(',', $json_obj->classesAllowed ?? []);


$stmt = mysqli_prepare($dbcn, "INSERT INTO competitions (name, website, startdate, deadline, description, classes_allowed) VALUES (?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    error_log('mysqli_prepare failed: ' . mysqli_error($dbcn));
    echo json_encode(['error' => 'Помилка при підготовці SQL запиту']);
    exit();
}

mysqli_stmt_bind_param($stmt, 'ssssss', $name, $website, $startdate, $deadline, $description, $classesAllowed);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => 'Конкурс успішно додано.']);
} else {
    error_log('Error adding competition: ' . mysqli_stmt_error($stmt));
    echo json_encode(['error' => 'Помилка при додаванні конкурсу.']);
}

mysqli_stmt_close($stmt);
?>