<?php
require '5.php';

$startDateFilter = $_GET['startDateFilter'] ?? null;
$endDateFilter = $_GET['endDateFilter'] ?? null;
$sortBy = $_GET['sortBy'] ?? 'name';
$sortOrder = $_GET['sortOrder'] ?? 'asc';

$query = "SELECT id, name, website, startdate, deadline, description, classes_allowed, 
                 IF(CURDATE() > deadline, 'expired', 'active') AS status
          FROM competitions WHERE 1=1";

if ($startDateFilter) {
    $query .= " AND startdate >= ?";
}
if ($endDateFilter) {
    $query .= " AND deadline <= ?";
}

$query .= " ORDER BY $sortBy $sortOrder";

$stmt = mysqli_prepare($dbcn, $query);

if ($startDateFilter && $endDateFilter) {
    mysqli_stmt_bind_param($stmt, 'ss', $startDateFilter, $endDateFilter);
} elseif ($startDateFilter) {
    mysqli_stmt_bind_param($stmt, 's', $startDateFilter);
} elseif ($endDateFilter) {
    mysqli_stmt_bind_param($stmt, 's', $endDateFilter);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$competitions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $competitions[] = $row;
}

echo json_encode($competitions);
mysqli_stmt_close($stmt);
mysqli_close($dbcn);
?>

