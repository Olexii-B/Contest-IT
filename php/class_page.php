<?php
include 'header.php';
session_start();

// Validate if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You are not logged in.";
    exit;
}

$classID = $_GET['class_id'];
$userRole = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Class Page</title>
</head>
<body>
    <div class="container mt-5">
        <h1>Class Details</h1>

        <?php if ($userRole === 'student'): ?>
            <h3>Submit Assignment</h3>
            <form action="upload_assignment.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($classID); ?>">
                <div class="mb-3">
                    <label for="file" class="form-label">Choose file</label>
                    <input type="file" class="form-control" id="file" name="file">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php elseif ($userRole === 'teacher'): ?>
            <h3>Manage Class</h3>
            <p><a href="view_submissions.php?class_id=<?php echo htmlspecialchars($classID); ?>" class="btn btn-secondary">View Submissions</a></p>
            <p><a href="manage_students.php?class_id=<?php echo htmlspecialchars($classID); ?>" class="btn btn-secondary">Manage Students</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
