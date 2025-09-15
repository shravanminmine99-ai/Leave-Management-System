<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='hod'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['username'] = $row['username'];

        header("Location: hod_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HOD Login</title>
<link rel="stylesheet" href="style.css">
<style>
/* Navbar */
.navbar {
  display:flex; justify-content:space-between; align-items:center;
  background: rgba(59,130,246,0.15); padding:15px 25px; backdrop-filter:blur(6px);
  border-radius:0 0 15px 15px; flex-wrap:wrap;
}
.navbar .logo { font-weight:bold; color:#1e3a8a; font-size:1.3rem; }
.menu { display:flex; align-items:center; gap:20px; }
.menu a, .menu span { color:#1e3a8a; text-decoration:none; font-weight:500; }
.menu a:hover { color:#3b82f6; }
.hamburger { display:none; flex-direction:column; cursor:pointer; gap:5px; }
.hamburger div { width:25px; height:3px; background-color:#1e3a8a; transition:0.3s; }
@media(max-width:768px){ .menu{ display:none; flex-direction:column; width:100%; gap:10px; margin-top:10px; } .menu.active{ display:flex; } .hamburger{ display:flex; } }

/* Dark mode toggle */
.switch{position:relative; display:inline-block; width:40px; height:20px;}
.switch input{display:none;}
.slider{position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:.4s; border-radius:34px;}
.slider:before{position:absolute; content:""; height:16px; width:16px; left:2px; bottom:2px; background-color:white; transition:.4s; border-radius:50%;}
input:checked + .slider{ background-color:#3b82f6; }
input:checked + .slider:before{ transform:translateX(20px); }

/* Dark mode for login */
body.dark-mode{ background:#1f2937; color:#f3f4f6; }
body.dark-mode .form-box{ background:#374151; color:#f3f4f6; }
body.dark-mode input, body.dark-mode select, body.dark-mode textarea{ background:#4b5563; color:#f3f4f6; border:1px solid #9ca3af; }
body.dark-mode a{ color:#3b82f6; }
</style>
</head>
<body>

<!-- Navbar -->
<header class="navbar">
  <div class="logo">LMS Portal</div>
  <div class="hamburger" id="hamburger"><div></div><div></div><div></div></div>
  <div class="menu" id="navMenu">
    <span>Welcome!</span>
    <label class="switch">
      <input type="checkbox" id="darkModeSwitch">
      <span class="slider round"></span>
    </label>
  </div>
</header>

<div class="container">
  <div class="form-box">
    <h2>HOD Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form action="" method="POST">
      <label for="username">Username</label>
      <input type="text" name="username" required>
      <label for="password">Password</label>
      <input type="password" name="password" required>
      <button type="submit">Login as HOD</button>
    </form>
    <p>Don’t have an account? <a href="hod_register.php">Register here</a></p>
  </div>
</div>

<script>
// Hamburger toggle
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');
hamburger.addEventListener('click', () => navMenu.classList.toggle('active'));

// Dark mode toggle
const darkModeSwitch = document.getElementById('darkModeSwitch');
if(localStorage.getItem('darkMode') === 'enabled'){
    document.body.classList.add('dark-mode');
    darkModeSwitch.checked = true;
}
darkModeSwitch.addEventListener('change', function() {
    if(this.checked){
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode','enabled');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode','disabled');
    }
});
</script>
</body>
</html>
