<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: index.html");
    exit();
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE leaves SET status='Approved' WHERE id=$id");
}
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    mysqli_query($conn, "UPDATE leaves SET status='Rejected' WHERE id=$id");
}

$result = mysqli_query($conn, "SELECT * FROM leaves ORDER BY id DESC");
?>

<h2>All Leave Requests</h2>
<table border="1" cellpadding="10">
  <tr>
    <th>Name</th>
    <th>Department</th>
    <th>Leave Type</th>
    <th>From</th>
    <th>To</th>
    <th>Reason</th>
    <th>Status</th>
    <th>Action</th>
  </tr>
  <?php while($row = mysqli_fetch_assoc($result)): ?>
  <tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['department'] ?></td>
    <td><?= $row['leave_type'] ?></td>
    <td><?= $row['from_date'] ?></td>
    <td><?= $row['to_date'] ?></td>
    <td><?= $row['reason'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
      <a href="?approve=<?= $row['id'] ?>">Approve</a> | 
      <a href="?reject=<?= $row['id'] ?>">Reject</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
