<?php
session_start();
include 'db.php';

// When form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // same hashing as login.php

    // Check if username exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already taken!";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username','$password','hod')";
        if (mysqli_query($conn, $sql)) {
            $success = "HOD Registration successful. You can now <a href='hod_login.php'>login</a>.";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HOD Registration</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>HOD Registration</h1>
</header>

<div class="container">
  <div class="form-box">
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form action="" method="POST">
      <label for="username">Username</label>
      <input type="text" name="username" required>

      <label for="password">Password</label>
      <input type="password" name="password" required>

      <button type="submit">Register as HOD</button>
    </form>
  </div>
</div>
</body>
</html>
