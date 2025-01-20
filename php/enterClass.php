<?php
require '5.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['class_code'], $data['class_password']) && isset($_SESSION['user_id'])) {
        $classCode = $data['class_code'];
        $classPassword = $data['class_password'];
        $studentID = $_SESSION['user_id'];

        $dbName = "main_db";
        mysqli_select_db($dbcn, $dbName) or die("Cannot select database: " . mysqli_error($dbcn));

        // Check if the class exists and fetch hashed password
        $query = "SELECT id, password FROM classes WHERE class_code = ?";
        $stmt = mysqli_prepare($dbcn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $classCode);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            // Check if a result was found
            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_bind_result($stmt, $classDatabaseID, $hashedPassword);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                // Verify password
                if (password_verify($classPassword, $hashedPassword)) {
                    // Check if the student is already in the class
                    $checkMembershipQuery = "SELECT COUNT(*) FROM class_memberships WHERE class_id = ? AND student_id = ?";
                    $checkStmt = mysqli_prepare($dbcn, $checkMembershipQuery);
                    mysqli_stmt_bind_param($checkStmt, 'ii', $classDatabaseID, $studentID);
                    mysqli_stmt_execute($checkStmt);
                    mysqli_stmt_bind_result($checkStmt, $membershipCount);
                    mysqli_stmt_fetch($checkStmt);
                    mysqli_stmt_close($checkStmt);

                    if ($membershipCount > 0) {
                        echo json_encode(['success' => false, 'error' => 'You are already a member of this class.']);
                    } else {
                        // Insert the student into the class_memberships table
                        $insertQuery = "INSERT INTO class_memberships (class_id, student_id) VALUES (?, ?)";
                        $insertStmt = mysqli_prepare($dbcn, $insertQuery);
                        mysqli_stmt_bind_param($insertStmt, 'ii', $classDatabaseID, $studentID);

                        if (mysqli_stmt_execute($insertStmt)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Failed to join the class.']);
                        }

                        mysqli_stmt_close($insertStmt);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid class code or password.']);
                }
            } else {
                // No class found with the given class code
                echo json_encode(['success' => false, 'error' => 'Class not found with the given code.']);
                mysqli_stmt_close($stmt);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . mysqli_error($dbcn)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Class code, password, and user session are required.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
