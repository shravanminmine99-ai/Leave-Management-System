<?php
require 'config.php';
require_login();
$user = current_user($mysqli);

// fetch my leaves with leave_number
$stmt = $mysqli->prepare('
    SELECT lr.*, u.name as applicant 
    FROM leave_requests lr 
    JOIN users u ON lr.user_id=u.id 
    WHERE lr.user_id = ? 
    ORDER BY lr.leave_number ASC
');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$leaves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="nav">Welcome, <?=htmlspecialchars($user['name'])?> | <a href="logout.php">Logout</a></div>
<div class="content">

<h2>Apply for Leave</h2>
<form method="post" action="apply_leave.php">
  <label>Leave Type
    <select name="leave_type">
      <option>Casual</option>
      <option>Medical</option>
      <option>OD</option>
      <option>Other</option>
    </select>
  </label>
  <label>Start Date<input type="date" name="start_date" required></label>
  <label>End Date<input type="date" name="end_date" required></label>
  <label>Reason<textarea name="reason"></textarea></label>
  <button type="submit">Apply</button>
</form>

<h2>Your Leave History</h2>
<table>
<tr>
  <th>ID</th>
  <th>Type</th>
  <th>Start</th>
  <th>End</th>
  <th>Status</th>
  <th>Approver</th>
</tr>
<?php foreach($leaves as $l): ?>
<tr>
  <td><?=htmlspecialchars($l['leave_number'])?></td>
  <td><?=htmlspecialchars($l['leave_type'])?></td>
  <td><?=htmlspecialchars($l['start_date'])?></td>
  <td><?=htmlspecialchars($l['end_date'])?></td>
  <td><?=htmlspecialchars($l['status'])?></td>
  <td>
    <?php 
    if($l['approver_id']){
        $r = $mysqli->query('SELECT name FROM users WHERE id='.(int)$l['approver_id'])->fetch_assoc();
        echo htmlspecialchars($r['name'] ?? '-');
    } else echo '-'; 
    ?>
  </td>
</tr>
<?php endforeach; ?>
</table>

</div>
</body>
</html>
