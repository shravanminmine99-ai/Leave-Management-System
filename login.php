<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$role'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['username'] = $row['username'];

        if ($row['role'] == 'student') {
            header("Location: apply_leave.php");
        } elseif ($row['role'] == 'staff') {
            header("Location: apply_leave.php");
        } elseif ($row['role'] == 'hod') {
            header("Location: hod_dashboard.php");
        }
        exit();
    } else {
        echo "Invalid credentials!";
    }
}

?>
