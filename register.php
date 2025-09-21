<?php
require 'config.php';
$err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$role = $_POST['role'];
$dept = $_POST['department_id'] ?: null;


if (!$name || !$email || !$password) $err = 'Fill required fields';
else {
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO users (name,email,password,role,department_id) VALUES (?,?,?,?,?)');
$stmt->bind_param('sssis',$name,$email,$hash,$role,$dept);
if ($stmt->execute()) {
header('Location: index.php'); exit;
} else $err = 'Error: ' . $mysqli->error;
}
}


// fetch depts
$depts = $mysqli->query('SELECT id,name FROM departments')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="style.css"></head><body>
<div class="box">
<h2>Register</h2>
<?php if($err) echo '<p class="error">'.htmlspecialchars($err).'</p>'; ?>
<form method="post">
<input name="name" placeholder="Full name" required>
<input name="email" type="email" placeholder="Email" required>
<input name="password" type="password" placeholder="Password" required>
<select name="role">
<option value="student">Student</option>
<option value="staff">Staff</option>
<option value="hod">HOD</option>
<option value="admin">Admin</option>
</select>
<select name="department_id">
<option value="">-- Department (optional) --</option>
<?php foreach($depts as $d) echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['name'])."</option>"; ?>
</select>
<button type="submit">Create</button>
</form>
<p><a href="index.php">Login</a></p>
</div>
</body></html>