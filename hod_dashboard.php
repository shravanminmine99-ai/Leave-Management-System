<?php
require 'config.php';
require_login();
$user = current_user($mysqli);

if ($user['role'] !== 'hod' && $user['role'] !== 'admin') {
    die('Access denied');
}

// Optional filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build SQL
if ($user['role'] === 'admin') {
    $sql = "SELECT lr.*, u.name AS applicant, d.name AS dept 
            FROM leave_requests lr 
            JOIN users u ON lr.user_id=u.id 
            LEFT JOIN departments d ON u.department_id=d.id ";
    $conds = [];
    if ($status_filter !== 'all') $conds[] = "lr.status=?";
    if ($conds) $sql .= "WHERE ".implode(" AND ",$conds)." ";
    $sql .= "ORDER BY lr.created_at DESC";
    $stmt = $mysqli->prepare($sql);
    if ($status_filter !== 'all') $stmt->bind_param('s',$status_filter);
} else {
    $sql = "SELECT lr.*, u.name AS applicant, d.name AS dept 
            FROM leave_requests lr 
            JOIN users u ON lr.user_id=u.id 
            LEFT JOIN departments d ON u.department_id=d.id 
            WHERE u.department_id=? ";
    if ($status_filter !== 'all') $sql .= "AND lr.status=? ";
    $sql .= "ORDER BY lr.created_at DESC";
    $stmt = $mysqli->prepare($sql);
    if ($status_filter !== 'all') $stmt->bind_param('is',$user['department_id'],$status_filter);
    else $stmt->bind_param('i',$user['department_id']);
}
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>HOD Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="nav">
  Logged in as <?=htmlspecialchars($user['name'])?> (<?=htmlspecialchars(strtoupper($user['role']))?>) 
  | <a href="logout.php">Logout</a>
</div>

<div class="content"><h2>Leave Requests</h2>

<form method="get" style="margin-bottom:10px;">
  <label>Filter by status:
    <select name="status" onchange="this.form.submit()">
      <option value="all" <?=($status_filter==='all'?'selected':'')?>>All</option>
      <option value="pending" <?=($status_filter==='pending'?'selected':'')?>>Pending</option>
      <option value="approved" <?=($status_filter==='approved'?'selected':'')?>>Approved</option>
      <option value="rejected" <?=($status_filter==='rejected'?'selected':'')?>>Rejected</option>
    </select>
  </label>
</form>

<table>
  <tr>
    <th>ID</th>
    <th>Applicant</th>
    <th>Department</th>
    <th>Type</th>
    <th>Start</th>
    <th>End</th>
    <th>Reason</th>
    <th>Status</th>
    <th>Action</th>
  </tr>
  <?php foreach ($requests as $p): ?>
    <tr>
      <td><?=htmlspecialchars($p['leave_number'])?></td>
      <td><?=htmlspecialchars($p['applicant'])?></td>
      <td><?=htmlspecialchars($p['dept'])?></td>
      <td><?=htmlspecialchars($p['leave_type'])?></td>
      <td><?=htmlspecialchars($p['start_date'])?></td>
      <td><?=htmlspecialchars($p['end_date'])?></td>
      <td><?=nl2br(htmlspecialchars($p['reason']))?></td>
      <td><?=htmlspecialchars(ucfirst($p['status']))?></td>
      <td>
        <?php if ($p['status']==='pending'): ?>
          <form method="post" action="approve.php" style="display:inline">
            <input type="hidden" name="id" value="<?=htmlspecialchars($p['id'])?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit">Approve</button>
          </form>
          <form method="post" action="approve.php" style="display:inline">
            <input type="hidden" name="id" value="<?=htmlspecialchars($p['id'])?>">
            <input type="hidden" name="action" value="reject">
            <button type="submit">Reject</button>
          </form>
        <?php else: ?>
          <!-- no action buttons for already decided requests -->
          -
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
</body>
</html>