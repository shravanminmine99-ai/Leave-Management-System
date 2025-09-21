<?php
require 'config.php';
$err = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $dept = $_POST['department_id'];
  if(!$name || !$email || !$password || !$dept){
    $err='Fill all fields';
  } else {
    $hash = password_hash($password,PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (name,email,password,role,department_id) VALUES (?,?,?,?,?)');
    $role='student';
    $stmt->bind_param('ssssi',$name,$email,$hash,$role,$dept);
    if($stmt->execute()) $success='Student created. <a href="index.php">Login</a>';
    else $err='Error '.$mysqli->error;
  }
}
$depts=$mysqli->query('SELECT id,name FROM departments')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register Student</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f5f7fa; margin:0; padding:0; color:#333;">
<div style="max-width:400px; margin:60px auto; padding:30px; background:#fff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); text-align:center;">
<h2 style="margin-bottom:20px; font-size:24px; color:#333;">Register Student</h2>

<?php 
if($err) echo '<p style="color:#b30000; background:#ffe0e0; padding:8px; border-radius:4px; margin-bottom:10px;">'.htmlspecialchars($err).'</p>';
if($success) echo '<p style="color:#006600; background:#e0ffe0; padding:8px; border-radius:4px; margin-bottom:10px;">'.$success.'</p>';
?>

<form method="post">
  <input name="name" placeholder="Full name" required 
         style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #4CAF50; border-radius:4px; box-sizing:border-box; font-weight:bold; background:#f9fff9;">
  <input name="email" type="email" placeholder="Email" required 
         style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fdfdfd;">
  <input name="password" type="password" placeholder="Password" required 
         style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fdfdfd;">
  <select name="department_id" required 
          style="width:100%; padding:12px 10px; margin:8px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fdfdfd;">
    <option value="">-- Select Department --</option>
    <?php foreach($depts as $d): ?>
      <option value="<?=htmlspecialchars($d['id'])?>"><?=htmlspecialchars($d['name'])?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" 
          style="width:100%; background-color:#4CAF50; color:white; padding:12px; margin-top:10px; border:none; border-radius:4px; font-size:16px; cursor:pointer;">
    Create Student Account
  </button>
</form>

<p style="font-size:13px; margin-top:15px;"><a href="index.php" style="color:#4CAF50; text-decoration:none;">Back to Login</a></p>
</div>
</body>
</html>
