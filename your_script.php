<?php
// EMPLOYEE PERFORMANCE MANAGEMENT SYSTEM (EPMS)

// Database Configuration
const DB_HOST = 'localhost';
const DB_NAME = 'epms';
const DB_USER = 'root';
const DB_PASS = '********';

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
function registerUser($db, $username, $password, $role, $departmentId = null, $managerId = null) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role, department_id, manager_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$username, $hashedPassword, $role, $departmentId, $managerId]);
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

// Function to create a goal
function createGoal($db, $title, $description, $employeeId, $priority, $timeline, $managerId) {
    $stmt = $db->prepare("INSERT INTO goals (title, description, employee_id, priority, timeline, status, manager_id) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
    return $stmt->execute([$title, $description, $employeeId, $priority, $timeline, $managerId]);
}

// Function to fetch goals for a specific employee
function getGoals($db, $employeeId) {
    $stmt = $db->prepare("SELECT * FROM goals WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    return $stmt->fetchAll();
}

// Function to fetch goals assigned by a manager
function getManagerGoals($db, $managerId) {
    $stmt = $db->prepare("SELECT * FROM goals WHERE manager_id = ?");
    $stmt->execute([$managerId]);
    return $stmt->fetchAll();
}
// function getManagerGoals($db, $managerId) {
//     $stmt = $db->prepare("
//         SELECT g.id, g.title, g.description, g.status
//         FROM goals g
//         INNER JOIN users u ON g.employee_id = u.id
//         WHERE u.manager_id = ?
//     ");
//     $stmt->execute([$managerId]);
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }


// Function to update goal status
function updateGoalStatus($db, $goalId, $status) {
    $stmt = $db->prepare("UPDATE goals SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $goalId]);
}

// Function to assign a manager to an employee
function assignManager($db, $managerId, $employeeId) {
    $stmt = $db->prepare("UPDATE users SET manager_id = ? WHERE id = ?");
    return $stmt->execute([$managerId, $employeeId]);
}

// Function to submit peer feedback
function submitPeerFeedback($db, $reviewerId, $revieweeId, $feedback) {
    $stmt = $db->prepare("INSERT INTO peer_feedback (reviewer_id, reviewee_id, feedback) VALUES (?, ?, ?)");
    return $stmt->execute([$reviewerId, $revieweeId, $feedback]);
}

// Function to fetch peer feedback
function getPeerFeedback($db, $employeeId) {
    $stmt = $db->prepare("SELECT pf.feedback, u.username AS reviewer FROM peer_feedback pf JOIN users u ON pf.reviewer_id = u.id WHERE pf.reviewee_id = ?");
    $stmt->execute([$employeeId]);
    return $stmt->fetchAll();
}

// Function to generate a performance report
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

// Function to fetch goal completion statistics for analytics
// function getGoalStatusCounts($db) {
//     $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM goals GROUP BY status");
//     $stmt->execute();
//     return $stmt->fetchAll();
// }

// function getGoalStatusCounts($db) {
//     $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM goals GROUP BY status");
//     $stmt->execute();
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

// // Fetch data for the chart
// $data = getGoalStatusCounts($db);
// $chartData = json_encode($data);


// Function to fetch employees for a manager
function getEmployeesForManager($db, $managerId) {
    $stmt = $db->prepare("SELECT * FROM users WHERE manager_id = ?");
    $stmt->execute([$managerId]);
    return $stmt->fetchAll();
}

// Function to fetch all departments
function getDepartments($db) {
    $stmt = $db->query("SELECT * FROM departments");
    return $stmt->fetchAll();
}
function createSmartGoal($db, $title, $description, $employeeId, $priority, $timeline, $managerId) {
    $stmt = $db->prepare("INSERT INTO goals (title, description, employee_id, priority, timeline,manager_id, status) VALUES (?, ?, ?, ?, ?,?, 'Pending')");
    return $stmt->execute([$title, $description, $employeeId, $priority, $timeline, $managerId]);
}

// Function to add a department
function addDepartment($db, $departmentName) {
    $stmt = $db->prepare("INSERT INTO departments (name) VALUES (?)");
    return $stmt->execute([$departmentName]);
}


// Get all employees
function getAllEmployees($db) {
    $stmt = $db->prepare("SELECT id, username FROM users WHERE role = 'employee'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all managers
function getAllManagers($db) {
    $stmt = $db->prepare("SELECT id, username FROM users WHERE role = 'manager'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all departments
function getAllDepartments($db) {
    $stmt = $db->prepare("SELECT id, name FROM departments");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEmployeePerformance($db, $managerId) {
    $query = "
        SELECT u.username AS employee_name, 
               COUNT(g.id) AS total_goals,
               SUM(CASE WHEN g.status = 'completed' THEN 1 ELSE 0 END) AS completed_goals,
               SUM(CASE WHEN g.status = 'pending' THEN 1 ELSE 0 END) AS pending_goals,
               SUM(CASE WHEN g.status = 'inprocess' THEN 1 ELSE 0 END) AS inprocess_goals
        FROM users u
        JOIN goals g ON u.id = g.employee_id
        WHERE u.manager_id = ? AND u.role = 'employee'
        GROUP BY u.username";
    $stmt = $db->prepare($query);
    $stmt->execute([$managerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>
