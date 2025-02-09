<?php
ob_start(); // Start output buffering
include 'your_script.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $role = loginUser($db, $username, $password);
    if ($role) {
        if ($role === ROLE_ADMIN) {
            echo "Redirecting to Admin Dashboard...";
            header("Location: admin_dashboard.php");
        } elseif ($role === ROLE_MANAGER) {
            echo "Redirecting to Manager Dashboard...";
            header("Location: manager_dashboard.php");
        } elseif ($role === ROLE_EMPLOYEE) {
            echo "Redirecting to Employee Dashboard...";
            header("Location: employee_dashboard.php");
        }
        else {
            echo "Invalid credentials. Please try again.";
        }
        exit;
    } else {
        echo "Invalid credentials. Please try again.";
    }
}
ob_end_flush(); // Flush the output buffer
?>
