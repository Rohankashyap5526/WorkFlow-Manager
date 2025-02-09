<?php
// EMPLOYEE PERFORMANCE MANAGEMENT SYSTEM (EPMS)

// Database Configuration
const DB_HOST = 'localhost';
const DB_NAME = 'epms';
const DB_USER = 'root';
const DB_PASS = 'Rohan@sql5526';

function connectDB() {
    try {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

$db = connectDB();

// User Role Constants
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_EMPLOYEE', 'employee');

// Function to register a user
function registerUser($db, $username, $password, $role) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $hashedPassword, $role]);
}

// Login function
function loginUser($db, $username, $password) {
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user'] = $user;
        return $user['role'];
    }
    return false;
}

// Function to create a goal (for Manager)
function createGoal($db, $title, $description, $employeeId, $dueDate) {
    $stmt = $db->prepare("INSERT INTO goals (title, description, employee_id, due_date, status) VALUES (?, ?, ?, ?, 'Pending')");
    return $stmt->execute([$title, $description, $employeeId, $dueDate]);
}


// Function to fetch goals for a specific employee (for Manager)
function getGoals($db, $employeeId) {
    $stmt = $db->prepare("SELECT * FROM goals WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    return $stmt->fetchAll();
}

// Function to fetch all goals for Manager
// function getManagerGoals($db, $managerId) {
//     $stmt = $db->prepare("SELECT g.*, u.username FROM goals g JOIN users u ON g.employee_id = u.id WHERE g.manager_id = ?");
//     $stmt->execute([$managerId]);
//     return $stmt->fetchAll();
// }
// Function to fetch goals assigned by the manager to their employees
function getManagerGoals($db, $managerId) {
    $stmt = $db->prepare("
        SELECT g.id, g.title, g.status, g.due_date, u.username
        FROM goals g
        JOIN users u ON g.employee_id = u.id
        WHERE u.manager_id = ?");
    $stmt->execute([$managerId]);
    return $stmt->fetchAll();
}


// Function to update goal status (for Employee)
function updateGoalStatus($db, $goalId, $status) {
    $stmt = $db->prepare("UPDATE goals SET status = ? WHERE id = ? AND employee_id = ?");
    return $stmt->execute([$status, $goalId, $_SESSION['user']['id']]);
}

// Function to generate a performance report (for Manager or Employee)
function generatePerformanceReport($db, $employeeId) {
    $stmt = $db->prepare("SELECT g.title, g.status, g.due_date FROM goals g WHERE g.employee_id = ?");
    $stmt->execute([$employeeId]);
    $goals = $stmt->fetchAll();

    $report = "Performance Report for Employee ID: $employeeId\n\n";
    foreach ($goals as $goal) {
        $report .= "Goal: " . $goal['title'] . "\nStatus: " . $goal['status'] . "\nDue Date: " . $goal['due_date'] . "\n\n";
    }

    return $report;
}

// Fetch employees for Manager (assigned employees)
// function getEmployeesForManager($db, $managerId) {
//     $stmt = $db->prepare("SELECT * FROM users WHERE manager_id = ?");
//     $stmt->execute([$managerId]);
//     return $stmt->fetchAll();
// }

// Fetch employees assigned to a specific manager
function getEmployeesForManager($db, $managerId) {
    $stmt = $db->prepare("SELECT u.id, u.username FROM users u WHERE u.manager_id = ?");
    $stmt->execute([$managerId]);
    return $stmt->fetchAll();
}


// Example usage
// Uncomment these lines to test additional functionality
/*
if (updateGoalStatus($db, 1, 'Completed')) {
    echo "Goal status updated successfully!";
}

$report = generatePerformanceReport($db, 1);
echo nl2br($report);
*/

?>
