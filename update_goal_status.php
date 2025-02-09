<?php
include 'your_script.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_EMPLOYEE) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goalId = $_POST['goal_id'];
    $status = $_POST['status'];

    if (updateGoalStatus($db, $goalId, $status)) {
        echo "Goal status updated successfully!";
    } else {
        echo "Failed to update goal status.";
    }
}
?>
