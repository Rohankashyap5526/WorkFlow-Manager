<?php
include 'your_script.php';
session_start();

// Check if the user is a manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_MANAGER) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $goal = $_POST['goal'];

    // Insert new goal for the employee
    $stmt = $db->prepare("INSERT INTO goals (employee_id, goal, status, manager_id) VALUES (?, ?, 'Pending', ?)");
    $stmt->execute([$employee_id, $goal, $_SESSION['user']['id']]);

    // Redirect back to the manager dashboard
    header("Location: manager_dashboard.php");
    exit();
}
?>
