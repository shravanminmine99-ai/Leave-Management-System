<?php
session_start();
include 'db.php';

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    // Check if username exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already taken!";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username','$password','$role')";
        if (mysqli_query($conn, $sql)) {
            $success = "Registration successful. You can now <a href='index.html'>login</a>.";
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
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Register for Leave Management System</h1>
</header>

<div class="container">
  <div class="form-box">
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form action="" method="POST">
      <label for="role">Role</label>
      <select id="role" name="role" required>
        <option value="student">Student</option>
        <option value="staff">Staff</option>
      </select>

      <label for="username">Username</label>
      <input type="text" name="username" required>

      <label for="password">Password</label>
      <input type="password" name="password" required>

      <button type="submit">Register</button>
    </form>
  </div>
</div>

<footer>
  <p>&copy; 2024 College Leave Management System</p>
</footer>
</body>
</html>
