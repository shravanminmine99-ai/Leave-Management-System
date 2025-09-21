<?php
session_start();


$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'college_lms';


$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
die('DB connect failed: ' . $mysqli->connect_error);
}


function is_logged_in() {
return isset($_SESSION['user_id']);
}


function require_login() {
if (!is_logged_in()) {
header('Location: index.php');
exit;
}
}


function current_user($mysqli) {
if (!is_logged_in()) return null;
$id = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT id,name,email,role,department_id FROM users WHERE id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
return $res->fetch_assoc();
}