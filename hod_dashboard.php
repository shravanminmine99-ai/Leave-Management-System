<?php
session_start();
include 'db.php';

// Only HOD can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: hod_login.php");
    exit();
}

// Handle WhatsApp Approve/Reject click
if (isset($_GET['action']) && isset($_GET['leave_id'])) {
    $id = intval($_GET['leave_id']);
    $action = $_GET['action']; // "approve" or "reject"
    $status = ($action === "approve") ? "Approved" : "Rejected";

    // Update leave status
    mysqli_query($conn, "UPDATE leaves SET status='$status' WHERE id=$id");

    // Fetch user's phone number
    $res = mysqli_query($conn, "SELECT phone_number FROM leaves WHERE id='$id'");
    $row = mysqli_fetch_assoc($res);
    $phone = $row['phone_number'] ?? '';

    $msg = urlencode("Hello! Your leave has been $status.");
    if (!empty($phone)) {
        // Redirect to WhatsApp with message
        header("Location: https://wa.me/$phone?text=$msg");
        exit();
    }
}

// Filters
$role_filter = $_GET['role_filter'] ?? 'all';
$status_filter = $_GET['status_filter'] ?? 'all';
$paid_filter = $_GET['paid_filter'] ?? 'all';

$where = "WHERE 1=1";
if ($role_filter != 'all') $where .= " AND u.role='".mysqli_real_escape_string($conn,$role_filter)."'";
if ($status_filter != 'all') $where .= " AND l.status='".mysqli_real_escape_string($conn,$status_filter)."'";
if ($paid_filter != 'all') $where .= " AND l.paid_leave='".mysqli_real_escape_string($conn,$paid_filter)."'";

// Counts
$count_sql = "SELECT 
    SUM(CASE WHEN l.status='Pending' THEN 1 ELSE 0 END) AS pending_count,
    SUM(CASE WHEN l.status='Approved' THEN 1 ELSE 0 END) AS approved_count,
    SUM(CASE WHEN l.status='Rejected' THEN 1 ELSE 0 END) AS rejected_count,
    COUNT(*) AS total_count
FROM leaves l 
JOIN users u ON l.user_id = u.id 
$where";
$count_res = mysqli_query($conn, $count_sql);
$counts = mysqli_fetch_assoc($count_res);

// Fetch leaves data
$data_sql = "SELECT l.*, u.role 
FROM leaves l 
JOIN users u ON l.user_id = u.id 
$where 
ORDER BY l.id DESC";
$result = mysqli_query($conn, $data_sql);

// Add data for charts
$status_chart_sql = "SELECT status, COUNT(*) AS count FROM leaves l JOIN users u ON l.user_id=u.id $where GROUP BY status";
$status_chart_res = mysqli_query($conn, $status_chart_sql);
$status_labels = [];
$status_counts = [];
while($row = mysqli_fetch_assoc($status_chart_res)){
    $status_labels[] = $row['status'];
    $status_counts[] = $row['count'];
}

$type_chart_sql = "SELECT leave_type, COUNT(*) AS count FROM leaves l JOIN users u ON l.user_id=u.id $where GROUP BY leave_type";
$type_chart_res = mysqli_query($conn, $type_chart_sql);
$type_labels = [];
$type_counts = [];
while($row = mysqli_fetch_assoc($type_chart_res)){
    $type_labels[] = $row['leave_type'];
    $type_counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HOD Dashboard</title>
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
.navbar .logo { font-weight: bold; color: #1e3a8a; font-size: 1.3rem; }
.menu { display:flex; align-items:center; gap:20px; }
.menu a, .menu span { color: #1e3a8a; text-decoration:none; font-weight:500; }
.menu a:hover { color:#3b82f6; }
.hamburger { display:none; flex-direction:column; cursor:pointer; gap:5px; }
.hamburger div { width:25px; height:3px; background-color:#1e3a8a; transition:0.3s; }
@media (max-width:768px) {
  .menu { display:none; flex-direction:column; width:100%; gap:10px; margin-top:10px; }
  .menu.active { display:flex; }
  .hamburger { display:flex; }
}

/* Dark mode toggle */
.switch { position:relative; display:inline-block; width:40px; height:20px; }
.switch input { display:none; }
.slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:.4s; border-radius:34px; }
.slider:before { position:absolute; content:""; height:16px; width:16px; left:2px; bottom:2px; background-color:white; transition:.4s; border-radius:50%; }
input:checked + .slider { background-color:#3b82f6; }
input:checked + .slider:before { transform: translateX(20px); }

/* Table border */
table { border:1px solid #cbd5e1; border-collapse: collapse; }
table th, table td { border:1px solid #cbd5e1; padding:10px; }

/* Dark mode body */
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
    <div></div><div></div><div></div>
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
  <div class="form-box" style="width:95%;">
    <h2>All Leave Requests</h2>

    <!-- Filters -->
    <form method="GET" style="margin-bottom:15px;">
      <label>Role:</label>
      <select name="role_filter">
        <option value="all" <?= $role_filter=='all'?'selected':''; ?>>All</option>
        <option value="student" <?= $role_filter=='student'?'selected':''; ?>>Student</option>
        <option value="staff" <?= $role_filter=='staff'?'selected':''; ?>>Staff</option>
      </select>
      <label>Status:</label>
      <select name="status_filter">
        <option value="all" <?= $status_filter=='all'?'selected':''; ?>>All</option>
        <option value="Pending" <?= $status_filter=='Pending'?'selected':''; ?>>Pending</option>
        <option value="Approved" <?= $status_filter=='Approved'?'selected':''; ?>>Approved</option>
        <option value="Rejected" <?= $status_filter=='Rejected'?'selected':''; ?>>Rejected</option>
      </select>
      <label>Paid Leave (Staff):</label>
      <select name="paid_filter">
        <option value="all" <?= $paid_filter=='all'?'selected':''; ?>>All</option>
        <option value="Yes" <?= $paid_filter=='Yes'?'selected':''; ?>>Yes</option>
        <option value="No" <?= $paid_filter=='No'?'selected':''; ?>>No</option>
      </select>
      <button type="submit">Apply Filters</button>
    </form>

    <!-- Counts summary -->
    <div class="counts">
      <span>Total: <?= $counts['total_count'] ?? 0 ?></span>
      <span>Pending: <?= $counts['pending_count'] ?? 0 ?></span>
      <span>Approved: <?= $counts['approved_count'] ?? 0 ?></span>
      <span>Rejected: <?= $counts['rejected_count'] ?? 0 ?></span>
    </div>

    <!-- Charts -->
<div style="width:100%; display:flex; flex-wrap:wrap; gap:30px; margin-bottom:20px;">
  <div style="flex:1; min-width:300px;">
    <canvas id="statusChart"></canvas>
  </div>
  <div style="flex:1; min-width:300px;">
    <canvas id="typeChart"></canvas>
  </div>
</div>

<!-- Search Bar -->
<input type="text" id="leaveSearch" placeholder="Search leave history..." 
style="padding:8px 12px; width:100%; max-width:300px; margin-bottom:15px; border-radius:6px; border:1px solid #cbd5e1;">

<div class="table-responsive">
    <table>
      <tr>
        <th>ID</th><th>Name</th><th>Role</th><th>Department</th><th>Leave Type</th>
        <th>From</th><th>To</th><th>Reason</th><th>Phone</th><th>Paid Leave</th><th>Status</th><th>Actions</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($result)): 
        $phone = $row['phone_number'] ?? '-';
      ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td><?= htmlspecialchars($row['department']) ?></td>
        <td><?= htmlspecialchars($row['leave_type']) ?></td>
        <td><?= htmlspecialchars($row['from_date']) ?></td>
        <td><?= htmlspecialchars($row['to_date']) ?></td>
        <td><?= htmlspecialchars($row['reason']) ?></td>
        <td><?= htmlspecialchars($phone) ?></td>
        <td><?= $row['paid_leave'] ?? '-' ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td class="action-buttons">
          <?php if($phone != '-'): ?>
            <a href="?action=approve&leave_id=<?= $row['id'] ?>" target="_blank" class="approve">Approve</a> 
            <a href="?action=reject&leave_id=<?= $row['id'] ?>" target="_blank" class="reject">Reject</a>
          <?php else: ?> - <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
</div>
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

// Search functionality for leave history table
const leaveSearch = document.getElementById('leaveSearch');
leaveSearch.addEventListener('input', function() {
    const filter = leaveSearch.value.toLowerCase();
    const rows = document.querySelectorAll('.table-responsive tbody tr');
    rows.forEach(row => {
        let text = '';
        row.querySelectorAll('td').forEach(td => { text += td.textContent.toLowerCase() + ' '; });
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<!-- Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($status_labels) ?>,
        datasets: [{
            data: <?= json_encode($status_counts) ?>,
            backgroundColor: ['#f0ad4e','#3bbf6b','#f87171'],
        }]
    },
    options: { responsive:true }
});

const typeCtx = document.getElementById('typeChart').getContext('2d');
const typeChart = new Chart(typeCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($type_labels) ?>,
        datasets: [{ label:'Number of Leaves', data: <?= json_encode($type_counts) ?>, backgroundColor:'#3b82f6' }]
    },
    options: { responsive:true, scales:{ y:{ beginAtZero:true } } }
});
</script>

</body>
</html>
