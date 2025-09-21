<?php
require 'config.php';
$error = '';

// simple login handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $stmt = $mysqli->prepare('SELECT id,password,role FROM users WHERE email = ? AND role = ?');
    $stmt->bind_param('ss',$email,$role);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($user = $res->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            // redirect according to role
            if ($user['role'] === 'hod') header('Location: hod_dashboard.php');
            elseif ($user['role'] === 'staff') header('Location: staff_dashboard.php');
            else header('Location: student_dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - College LMS</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f5f7fa; margin:0; padding:0; color:#333;">
<div style="max-width:360px; margin:60px auto; padding:30px; background:#fff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); text-align:center;">
<h2 style="margin-bottom:20px; font-size:24px; color:#333;">Login</h2>

<?php if (!empty($error)) echo '<p style="color:#b30000; background:#ffe0e0; padding:8px; border-radius:4px; margin-bottom:10px;">'.htmlspecialchars($error).'</p>'; ?>

<form method="post">
  <select name="role" required style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fdfdfd;">
    <option value="">-- Select Role --</option>
    <option value="student">Student</option>
    <option value="staff">Staff</option>
    <option value="hod">HOD</option>
  </select>
  <input name="email" type="email" placeholder="Email" required
         style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fdfdfd;">
  <input name="password" type="password" placeholder="Password" required
         style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fdfdfd;">
  <button type="submit"
          style="width:100%; background-color:#4CAF50; color:white; padding:12px; margin-top:10px; border:none; border-radius:4px; font-size:16px; cursor:pointer;">
    Login
  </button>
</form>

<p style="font-size:13px; margin-top:15px;">
  Need an account?  
  <a href="register_student.php" style="color:#4CAF50; text-decoration:none;">Register Student</a> | 
  <a href="register_staff.php" style="color:#4CAF50; text-decoration:none;">Register Staff</a> | 
  <a href="register_hod.php" style="color:#4CAF50; text-decoration:none;">Register HOD</a>
</p>
</div>
</body>
</html>
