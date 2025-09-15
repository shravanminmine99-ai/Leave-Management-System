<?php
session_start();
include 'db.php';

// Only logged-in users
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student','staff'])) {
    header("Location: index.html");
    exit();
}

// Handle leave submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_leave'])) {
    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);

    $paid_leave = NULL;
    if ($_SESSION['role'] == 'staff') {
        $paid_leave = mysqli_real_escape_string($conn, $_POST['paid_leave']);
    }

    $sql = "INSERT INTO leaves (user_id, name, department, leave_type, from_date, to_date, reason, phone_number, paid_leave)
            VALUES ('$user_id', '$name', '$department', '$leave_type', '$from_date', '$to_date', '$reason','$phone_number', " . 
            ($paid_leave !== NULL ? "'$paid_leave'" : "NULL") . ")";
    if (mysqli_query($conn, $sql)) {
        $message = "<p style='color:green;'>Leave Applied Successfully</p>";
    } else {
        $message = "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}

// Fetch leave history
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM leaves WHERE user_id='$user_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Leave</title>
<link rel="stylesheet" href="style.css">
<style>
/* ==================== NAVBAR ==================== */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(59,130,246,0.15);
  padding: 15px 25px;
  backdrop-filter: blur(6px);
  border-radius: 0 0 15px 15px;
  flex-wrap: wrap;
}
.navbar .logo {
  font-weight: bold;
  color: #1e3a8a;
  font-size: 1.3rem;
}
.menu {
  display: flex;
  align-items: center;
  gap: 20px;
}
.menu a, .menu span {
  color: #1e3a8a;
  text-decoration: none;
  font-weight: 500;
}
.menu a:hover { color: #3b82f6; }
.hamburger {
  display: none;
  flex-direction: column;
  cursor: pointer;
  gap: 5px;
}
.hamburger div {
  width: 25px;
  height: 3px;
  background-color: #1e3a8a;
  transition: 0.3s;
}
@media (max-width: 768px) {
  .menu { display: none; flex-direction: column; width: 100%; gap: 10px; margin-top: 10px; }
  .menu.active { display: flex; }
  .hamburger { display: flex; }
}

/* Dark mode toggle */
.switch { position: relative; display: inline-block; width: 40px; height: 20px; }
.switch input { display:none; }
.slider {
  position: absolute;
  cursor: pointer;
  top:0; left:0; right:0; bottom:0;
  background-color:#ccc;
  transition: .4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content:"";
  height:16px;
  width:16px;
  left:2px;
  bottom:2px;
  background-color:white;
  transition:.4s;
  border-radius:50%;
}
input:checked + .slider { background-color:#3b82f6; }
input:checked + .slider:before { transform: translateX(20px); }

/* Table border */
table { border:1px solid #cbd5e1; border-collapse: collapse; width:100%; }
table th, table td { border:1px solid #cbd5e1; padding:10px; }

/* Mobile responsive leave history */
@media (max-width:768px){
  table, thead, tbody, th, td, tr { display:block; }
  thead tr { display:none; }
  tr{
    background:#f0f4ff; 
    margin-bottom:15px; 
    border-radius:10px; 
    padding:10px; 
  }
  td{
    display:flex; 
    justify-content:space-between; 
    padding:8px 10px; 
    border:none; 
    border-bottom:1px solid #cbd5e1; /* line between label and value */
    position:relative;
  }
  td:last-child{ border-bottom:none; }
  td:before{
    content:attr(data-label); 
    font-weight:bold; 
    width:50%; 
    flex-shrink:0;
  }
}

/* Dark mode */
body.dark-mode { background: #1f2937; color: #f3f4f6; }
body.dark-mode .form-box { background: #374151; color: #f3f4f6; }
body.dark-mode input, body.dark-mode select, body.dark-mode textarea { background: #4b5563; color:#f3f4f6; border:1px solid #9ca3af; }
body.dark-mode table { background:#4b5563; color:#f3f4f6; }
body.dark-mode table th { background:#6b7280; color:#f3f4f6; }
body.dark-mode .menu a, body.dark-mode .menu span { color:#f3f4f6; }
</style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="logo">LMS Portal</div>
  <div class="hamburger" id="hamburger">
    <div></div>
    <div></div>
    <div></div>
  </div>
  <div class="menu" id="navMenu">
    <span>Hello, <?= htmlspecialchars($_SESSION['username']) ?></span>
    <a href="logout.php">Logout</a>
    <label class="switch">
      <input type="checkbox" id="darkModeSwitch">
      <span class="slider round"></span>
    </label>
  </div>
</header>

<div class="container">
  <div class="form-box" style="width:400px;">
    <?php if (!empty($message)) echo $message; ?>
    <form action="" method="POST" id="leaveForm">
      <label>Name</label>
      <input type="text" name="name" required>

      <label>Department</label>
      <input type="text" name="department" required>

      <label>Leave Type</label>
      <select name="leave_type" id="leaveType" required>
        <option value="">Select Type</option>
        <option value="Sick">Sick Leave</option>
        <option value="Casual">Casual Leave</option>
        <option value="Other">Other</option>
      </select>

      <div id="otherLeaveContainer" style="display:none; margin-top:8px;">
        <label>Please specify</label>
        <input type="text" name="other_leave" id="otherLeaveInput">
      </div>

      <label>From Date</label>
      <input type="date" name="from_date" id="fromDate" required>

      <label>To Date</label>
      <input type="date" name="to_date" id="toDate" required>

      <label>Reason</label>
      <textarea name="reason" id="reason" maxlength="300" required></textarea>
      <div id="charCount" style="font-size:0.8rem; color:#555;">0/300 characters</div>

      <label>Phone Number (with country code)</label>
      <input type="text" name="phone_number" id="phoneNumber" placeholder="+919876543210" required>

      <?php if ($_SESSION['role'] == 'staff'): ?>
      <label>Paid Leave?</label>
      <select name="paid_leave" required>
        <option value="">Select</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
      </select>
      <?php endif; ?>

      <button type="submit" name="submit_leave" id="submitBtn" disabled>Submit Leave</button>
    </form>
  </div>
</div>

<div class="container">
  <div class="form-box" style="width:95%;">
    <h2>My Leave History</h2>
    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Department</th>
        <th>Leave Type</th>
        <th>From</th>
        <th>To</th>
        <th>Reason</th>
        <?php if ($_SESSION['role'] == 'staff'): ?><th>Paid Leave</th><?php endif; ?>
        <th>Status</th>
      </tr>
      </thead>
      <tbody>
      <?php while($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td data-label="ID"><?= $row['id'] ?></td>
        <td data-label="Department"><?= htmlspecialchars($row['department']) ?></td>
        <td data-label="Leave Type"><?= htmlspecialchars($row['leave_type']) ?></td>
        <td data-label="From"><?= htmlspecialchars($row['from_date']) ?></td>
        <td data-label="To"><?= htmlspecialchars($row['to_date']) ?></td>
        <td data-label="Reason"><?= htmlspecialchars($row['reason']) ?></td>
        <?php if ($_SESSION['role'] == 'staff'): ?><td data-label="Paid Leave"><?= htmlspecialchars($row['paid_leave']) ?></td><?php endif; ?>
        <td data-label="Status">
          <?php
          if ($row['status'] == 'Pending') echo "<span style='color:orange;'>Pending</span>";
          elseif ($row['status'] == 'Approved') echo "<span style='color:green;'>Approved</span>";
          else echo "<span style='color:red;'>Rejected</span>";
          ?>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
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
<script>
const leaveForm = document.getElementById('leaveForm');
const submitBtn = document.getElementById('submitBtn');
const leaveType = document.getElementById('leaveType');
const otherLeaveInput = document.getElementById('otherLeaveInput');
const otherLeaveContainer = document.getElementById('otherLeaveContainer');
const fromDate = document.getElementById('fromDate');
const toDate = document.getElementById('toDate');
const reason = document.getElementById('reason');
const phoneNumber = document.getElementById('phoneNumber');
const charCount = document.getElementById('charCount');

// Show/hide Other leave input
leaveType.addEventListener('change', () => {
    otherLeaveContainer.style.display = leaveType.value === "Other" ? "block" : "none";
    validateForm();
});

// Set min date for To date
fromDate.addEventListener('change', () => {
    toDate.min = fromDate.value;
    validateForm();
});

// Live character count
reason.addEventListener('input', () => {
    charCount.textContent = `${reason.value.length}/300 characters`;
    validateForm();
});

// Validate all required fields
function validateForm() {
    let isValid = true;

    const name = leaveForm.elements['name'].value.trim();
    const dept = leaveForm.elements['department'].value.trim();
    const fromVal = fromDate.value;
    const toVal = toDate.value;
    const leaveTypeVal = leaveType.value;
    const reasonVal = reason.value.trim();
    const phoneVal = phoneNumber.value.trim();

    // Check all required fields
    if (!name || !dept || !fromVal || !toVal || !leaveTypeVal || !reasonVal || !phoneVal) {
        isValid = false;
    }

    // Other leave input required if selected
    if (leaveTypeVal === "Other" && otherLeaveInput.value.trim() === "") isValid = false;

    // From date <= To date
    if (fromVal && toVal && fromVal > toVal) isValid = false;

    // Phone format
    const phonePattern = /^\+\d{10,15}$/;
    if (!phonePattern.test(phoneVal)) isValid = false;

    submitBtn.disabled = !isValid;
}

// Listen to all input changes
leaveForm.addEventListener('input', validateForm);
validateForm();
</script>
</body>
</html>
