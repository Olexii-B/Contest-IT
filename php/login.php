<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '5.php';

session_start(); // Запустити сесію на початку скрипта

header('Content-Type: application/json');

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str);

    $email = isset($json_obj->email) ? sanitize_input($json_obj->email) : '';
    $password = isset($json_obj->password) ? sanitize_input($json_obj->password) : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Неправильна адреса електронної пошти або пароль']);
        exit();
    }

    $login_query = "SELECT id, email, password, role FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($dbcn, $login_query)) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                // Успішний вхід: Зберегти ідентифікатор користувача, роль у сеансі та файл cookie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                setcookie('user_id', $user['id'], time() + (86400), '/'); // cookie, термін дії якого закінчується через 1 день
                setcookie('role', $user['role'], time() + (86400), '/'); // cookie, термін дії якого закінчується через 1 день

                echo json_encode([
                    'success' => 'Користувач успішно ввійшов.',
                    'role' => $user['role'] // надіслати інформацію про роль
                ]);
            } else {
                echo json_encode(['error' => 'Неправильний пароль.']);
            }
        } else {
            echo json_encode(['error' => 'Користувач з такою адресою електронної пошти не знайдений.']);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['error' => 'Помилка запиту до бази даних.']);
    }
} else {
    echo json_encode(['error' => 'Неправильний метод запиту.']);
}

mysqli_close($dbcn);
?>
