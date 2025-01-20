<?php
require '1.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);

    // Debugging: Output the data received
    error_log('Received data: ' . print_r($data, true));

    if (isset($data['id'])) {
        $compId = $data['id'];

        // Debugging: Output the ID to be deleted
        error_log('ID to be deleted: ' . $compId);

        $dbName = "main_db";
        mysqli_select_db($dbcn, $dbName) or die("Cannot select database: " . mysqli_error($dbcn));

        $query = "DELETE FROM competitions WHERE id = ?";
        $stmt = mysqli_prepare($dbcn, $query);

        if ($stmt === false) {
            http_response_code(500);
            error_log("Error preparing statement: " . mysqli_error($dbcn));
            echo "Error preparing statement: " . mysqli_error($dbcn);
            exit;
        }

        mysqli_stmt_bind_param($stmt, 'i', $compId);

        if (mysqli_stmt_execute($stmt)) {
            http_response_code(200);
            error_log("Competition deleted successfully: ID $compId");
            echo "Competition deleted successfully";
        } else {
            http_response_code(500);
            error_log("Error executing deletion: " . mysqli_stmt_error($stmt));
            echo "Error executing deletion: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        http_response_code(400);
        error_log("Invalid request: Competition ID not provided.");
        echo "Invalid request: Competition ID not provided.";
    }
} else {
    http_response_code(405); // Method not allowed
    error_log("Invalid request method.");
    echo "Invalid request method.";
}
?>