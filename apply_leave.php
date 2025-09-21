<?php
require 'config.php';
require_login();
$user = current_user($mysqli);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$type = $_POST['leave_type'] ?? '';
$start = $_POST['start_date'] ?? '';
$end = $_POST['end_date'] ?? '';
$reason = $_POST['reason'] ?? '';


// basic validation
if (!$type || !$start || !$end) {
$_SESSION['flash'] = 'Fill required fields';
header('Location: student_dashboard.php'); exit;
}


$stmt = $mysqli->prepare('INSERT INTO leave_requests (user_id,leave_type,start_date,end_date,reason) VALUES (?,?,?,?,?)');
$stmt->bind_param('issss',$user['id'],$type,$start,$end,$reason);
if ($stmt->execute()) {
$_SESSION['flash'] = 'Leave applied successfully';
} else {
$_SESSION['flash'] = 'Error: ' . $mysqli->error;
}
}
header('Location: student_dashboard.php'); exit;