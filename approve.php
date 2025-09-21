<?php
require 'config.php';
require_login();
$user = current_user($mysqli);

// Only HOD or admin can approve/reject
if ($user['role'] !== 'hod' && $user['role'] !== 'admin') {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];            // leave request id
    $action = $_POST['action'];         // 'approve' or 'reject'

    // Fetch the leave request to verify department
    $stmt = $mysqli->prepare('SELECT lr.*, u.department_id 
                              FROM leave_requests lr 
                              JOIN users u ON lr.user_id=u.id 
                              WHERE lr.id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();

    if (!$req) {
        $_SESSION['flash'] = 'Leave request not found';
        header('Location: hod_dashboard.php');
        exit;
    }

    // If user is HOD, ensure it’s their department
    if ($user['role'] !== 'admin' && $req['department_id'] != $user['department_id']) {
        $_SESSION['flash'] = 'You are not allowed to approve this request';
        header('Location: hod_dashboard.php');
        exit;
    }

    // Update status based on action
    $newstatus = ($action === 'approve') ? 'approved' : 'rejected';
    $u = $mysqli->prepare('UPDATE leave_requests 
                           SET status=?, approver_id=?, updated_at=NOW() 
                           WHERE id=?');
    $u->bind_param('sii', $newstatus, $user['id'], $id);
    if ($u->execute()) {
        $_SESSION['flash'] = 'Leave request '.$newstatus.' successfully';
    } else {
        $_SESSION['flash'] = 'Error: '.$mysqli->error;
    }
}

// Go back to dashboard
header('Location: hod_dashboard.php');
exit;
