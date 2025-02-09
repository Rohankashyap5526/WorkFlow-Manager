<?php
include 'your_script.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_MANAGER) {
    die("Access denied.");
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $employeeId = $_POST['employee_id'];
//     $title = $_POST['title'];
//     $description = $_POST['description'];
//     $dueDate = $_POST['due_date'];

//     if (createGoal($db, $title, $description, $employeeId, $dueDate)) {
//         echo "Goal successfully assigned to the employee.";
//     } else {
//         echo "Failed to assign goal.";
//     }
// }/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $managerId = $_POST['manager'];
    $description = $_POST['description'];
    $employeeId = $_POST['employee_id'];
    $priority = $_POST['priority'];
    $timeline = $_POST['timeline'];

    if (createSmartGoal($db, $title, $description, $employeeId, $priority, $timeline,  $managerId )) {
        echo "SMART Goal created successfully!";
    } else {
        echo "Failed to create SMART Goal.";
    }
}

?>
