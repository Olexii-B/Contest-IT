<?php
require '5.php';
require_once 'generateNotifications.php';


session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
]);
session_start();

ob_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
        $studentId = $_SESSION['user_id'];
        $classId = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        $competitionId = isset($_POST['competition_id']) ? intval($_POST['competition_id']) : 0;

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['file']['name']);
            $fileType = mime_content_type($_FILES['file']['tmp_name']);
            $fileSize = intval($_FILES['file']['size']);

            if ($fileSize <= 0 || empty($fileName)) {
                echo json_encode(['success' => false, 'error' => 'File size is 0 or file name is empty.']);
                exit;
            }

            $allowedTypes = [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'application/x-rar-compressed', // .rar
                'application/pdf', // .pdf
                'application/zip', // .zip
                'application/x-xz', // .tar.xz
                'application/x-tar', // .tar
            ];

            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'error' => 'File type not allowed.']);
                exit;
            }

            $fileData = file_get_contents($_FILES['file']['tmp_name']);

            $query = "INSERT INTO class_files (class_id, student_id, file_name, file_type, file_size, file_data, competition_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($dbcn, $query);

            if (!$stmt) {
                echo json_encode(['success' => false, 'error' => 'Failed to prepare SQL statement.']);
                exit;
            }

            mysqli_stmt_bind_param($stmt, 'iisssbi', $classId, $studentId, $fileName, $fileType, $fileSize, $fileData, $competitionId);

            mysqli_stmt_send_long_data($stmt, 5, $fileData);

            if (mysqli_stmt_execute($stmt)) {
                notifyWorkUploaded($dbcn);

                ob_clean();
                echo json_encode(['success' => true, 'message' => 'File uploaded successfully.']);
                header('Location: class.php?id=' . $classId);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'File upload failed.', 'sql_error' => mysqli_stmt_error($stmt)]);
            }

            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'error' => 'File not provided or upload error.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'User session not valid.']);
    }
}
?>

