<?php
session_start();
require '5.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fileId = intval($_GET['file_id']);

    // Query the file data from the database
    $query = "SELECT file_name, file_type, file_size, file_data FROM class_files WHERE id = ?";
    $stmt = mysqli_prepare($dbcn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $fileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    // Check if file exists
    if (mysqli_stmt_num_rows($stmt) === 1) {
        mysqli_stmt_bind_result($stmt, $fileName, $fileType, $fileSize, $fileData);
        mysqli_stmt_fetch($stmt);

        // Set appropriate headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $fileType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        // Output the file data
        echo $fileData;
    } else {
        echo "File not found.";
    }

    mysqli_stmt_close($stmt);
}
?>
