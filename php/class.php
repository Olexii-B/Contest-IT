<?php
require '5.php';
session_start();

// Отримати ідентифікатор класу з URL-адреси
$classId = $_GET['id'] ?? null;

if (!$classId) {
    echo "Ідентифікатор класу відсутній.";
    exit;
}

// Отримати інформацію про клас
$query = "SELECT * FROM classes WHERE id = ?";
$stmt = mysqli_prepare($dbcn, $query);
mysqli_stmt_bind_param($stmt, 'i', $classId);
mysqli_stmt_execute($stmt);
$classResult = mysqli_stmt_get_result($stmt);

if ($class = mysqli_fetch_assoc($classResult)) {
    // Отримати користувачів (учнів) у цьому класі
    $studentsQuery = "SELECT u.first_name, u.last_name, u.email FROM users u 
                      INNER JOIN class_memberships cm ON u.id = cm.student_id 
                      WHERE cm.class_id = ?";
    $studentsStmt = mysqli_prepare($dbcn, $studentsQuery);
    mysqli_stmt_bind_param($studentsStmt, 'i', $classId);
    mysqli_stmt_execute($studentsStmt);
    $studentsResult = mysqli_stmt_get_result($studentsStmt);
    $students = mysqli_fetch_all($studentsResult, MYSQLI_ASSOC);
} else {
    echo "Class not found.";
    exit;
}

$classId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null; //намагається отримати user_id з сесії або з cookies
$userRole = $_SESSION['role'] ?? $_COOKIE['role'] ?? null; //намагається отримати role з сесії або з cookies

mysqli_close($dbcn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Клас: <?= htmlspecialchars($class['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</head>
<body>
<div class="container mt-5">
    <h2><?= htmlspecialchars($class['name']) ?> (Код: <?= htmlspecialchars($class['class_code']) ?>)</h2>
    <p>Створено: <?= htmlspecialchars($class['created_at']) ?></p>

    <h4>Студенти цього класу:</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Ім'я</th>
                <th>Прізвище</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    require '5.php';
        // Перевірити, чи є користувач студентом
        if ($userRole === 'student') {
            // Отримати змагання з бази даних 
            $competitionQuery = "SELECT id, name FROM competitions";
            $competitionResult = mysqli_query($dbcn, $competitionQuery);
        
            $competitions = [];
            while ($row = mysqli_fetch_assoc($competitionResult)) {
                $competitions[] = $row;
            }
        
            // Відобразити форму 
            echo '
            <h3>Надішліть свою роботу</h3>
            <form action="/php/uploadWork.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="class_id" value="' . htmlspecialchars($classId) . '">
        
                <!-- Competition Dropdown -->
                <label for="competitionSelect">Оберіть конкурс:</label>
                <select id="competitionSelect" name="competition_id" required>
                    <option value="">-- Оберіть конкурс --</option>';
        
            // вибір змагань
            foreach ($competitions as $competition) {
                echo '<option value="' . htmlspecialchars($competition['id']) . '">' . htmlspecialchars($competition['name']) . '</option>';
            }
        
            echo '</select>
        
                <!-- File Upload Input -->
                <label for="file">Завантажити файл:</label>
                <input type="file" name="file" id="file" required>
        
                <!-- Submit Button (Initially Disabled) -->
                <button type="submit" id="uploadButton" class="btn btn-primary mt-2" disabled>Submit</button>
            </form>';
        }
        ?>

        <!-- JavaScript для включення кнопки «Надіслати» після відбору на конкурс  -->
        <script>
            document.getElementById('competitionSelect').addEventListener('change', function () {
                document.getElementById('uploadButton').disabled = !this.value;
            });
        </script>

    <?php
    if ($userRole === 'teacher') {
        // Отримати файли разом з іменами студентів та назвами змагань 
        $query = "
            SELECT cf.id, cf.file_name, cf.file_type, cf.file_size, cf.uploaded_at, 
                u.first_name, u.last_name, comp.name AS competition_name 
            FROM class_files AS cf
            JOIN users AS u ON cf.student_id = u.id
            JOIN competitions AS comp ON cf.competition_id = comp.id
            WHERE cf.class_id = ?";
        $stmt = mysqli_prepare($dbcn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $classId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        echo '<h3>Надіслані файли</h3>';
        echo '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Назва файла</th>
                        <th>Тип</th>
                        <th>Розмір</th>
                        <th>Завантажено</th>
                        <th>Ім’я студента</th>
                        <th>Конкурс</th>
                        <th>Скачати</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['file_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['file_type']) . '</td>';
            echo '<td>' . round($row['file_size'] / 1024, 2) . ' KB</td>';
            echo '<td>' . htmlspecialchars($row['uploaded_at']) . '</td>';
            echo '<td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['competition_name']) . '</td>';
            echo '<td><a href="/php/downloadFile.php?file_id=' . intval($row['id']) . '" class="btn btn-success btn-sm">Download</a></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        mysqli_stmt_close($stmt);
    }
    ?>


    <?php
    require '5.php';
    if ($userRole === 'student') {
        // Відображати лише файли, завантажені учнем, який увійшов до системи
        $query = "
            SELECT cf.id, cf.file_name, cf.file_type, cf.file_size, cf.uploaded_at, 
                   comp.name AS competition_name 
            FROM class_files AS cf
            JOIN competitions AS comp ON cf.competition_id = comp.id
            WHERE cf.class_id = ? AND cf.student_id = ?";
        $stmt = mysqli_prepare($dbcn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $classId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    
        echo '<h3>Мої надіслані файли</h3>';
        echo '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Назва файла</th>
                        <th>Тип</th>
                        <th>Розмір</th>
                        <th>Завантажено</th>
                        <th>Конкурс</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>';
    
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['file_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['file_type']) . '</td>';
            echo '<td>' . round($row['file_size'] / 1024, 2) . ' KB</td>';
            echo '<td>' . htmlspecialchars($row['uploaded_at']) . '</td>';
            echo '<td>' . htmlspecialchars($row['competition_name']) . '</td>';
            echo '<td>
                    <a href="/php/downloadFile.php?file_id=' . intval($row['id']) . '" class="btn btn-success btn-sm">Download</a>
                    <form action="/php/deleteFile.php" method="POST" style="display:inline;">
                        <input type="hidden" name="file_id" value="' . intval($row['id']) . '">
                        <button type="submit" class="btn btn-danger btn-sm">Видалити</button>
                    </form>
                  </td>';
            echo '</tr>';
        }
    
        echo '</tbody></table>';
        mysqli_stmt_close($stmt);
    }
    ?>

<script>
    // Function to show the error modal
    function showErrorModal(message) {
        const errorModalBody = document.getElementById('errorModalBody');
        errorModalBody.textContent = message;
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }

    // Function to show the success modal
    function showSuccessModal(message) {
        const successModalBody = document.getElementById('successModalBody');
        successModalBody.textContent = message;
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    }

    const responseText = await response.text(); // Отримуємо текстову відповідь
    console.log(responseText); // Дивимося, що саме повертає сервер

    // Attach modal handlers to forms or other actions
    document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.querySelector('form[action="/php/uploadWork.php"]');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Забороняємо стандартну відправку форми
            
            const formData = new FormData(uploadForm);
            try {
                const response = await fetch(uploadForm.action, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (result.success) {
                    showSuccessModal(result.message || 'Файл успішно завантажено.');
                } else {
                    showErrorModal(result.error || 'Помилка при завантаженні файлу.');
                }
            } catch (error) {
                showErrorModal('Мережева помилка: ' + error.message);
            }
        });
    }
        <div class="modal fade" id="successModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-success" id="successModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Закрити</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="errorModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-danger" id="errorModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Закрити</button>
                    </div>
                </div>
            </div>
        </div>

        // Example for delete file form
        const deleteForms = document.querySelectorAll('form[action="/php/deleteFile.php"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(form);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                    });
                    const result = await response.text();

                    if (result.includes('File deleted successfully')) {
                        showSuccessModal('Файл успішно видалено.');
                        form.closest('tr').remove(); // Remove the table row
                    } else {
                        showErrorModal('Помилка видалення файлу.');
                    }
                } catch (error) {
                    showErrorModal('Мережева помилка: ' + error.message);
                }
            });
        });
    });
</script>


</div>
</body>
</html>
