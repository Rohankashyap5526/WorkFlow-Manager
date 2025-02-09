<?php
session_start();
require 'your_script.php'; // Ensure database connection is included

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Function to fetch employee goals
function getEmployeeGoals($db, $employeeId) {
    $stmt = $db->prepare("SELECT * FROM goals WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch manager's team goals
function getManagerTeamGoals($db, $managerId) {
    $stmt = $db->prepare(
        "SELECT g.*, e.username AS employee_name 
         FROM goals g 
         JOIN users e ON g.employee_id = e.id 
         WHERE e.manager_id = ?"
    );
    $stmt->execute([$managerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch analytics data for admin
function getGoalStatusCounts($db) {
    $stmt = $db->prepare("SELECT status, COUNT(*) AS count FROM goals GROUP BY status");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Render dashboard based on role
if ($role === 'employee') {
    $goals = getEmployeeGoals($db, $userId);
    ?>
    <h1>Employee Dashboard</h1>
    <h2>Your Goals</h2>
    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Timeline</th>
        </tr>
        <?php foreach ($goals as $goal): ?>
            <tr>
                <td><?= htmlspecialchars($goal['title']) ?></td>
                <td><?= htmlspecialchars($goal['description']) ?></td>
                <td><?= htmlspecialchars($goal['status']) ?></td>
                <td><?= htmlspecialchars($goal['timeline']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
} elseif ($role === 'manager') {
    $teamGoals = getManagerTeamGoals($db, $userId);
    ?>
    <h1>Manager Dashboard</h1>
    <h2>Team Goals</h2>
    <table>
        <tr>
            <th>Employee</th>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Timeline</th>
        </tr>
        <?php foreach ($teamGoals as $goal): ?>
            <tr>
                <td><?= htmlspecialchars($goal['employee_name']) ?></td>
                <td><?= htmlspecialchars($goal['title']) ?></td>
                <td><?= htmlspecialchars($goal['description']) ?></td>
                <td><?= htmlspecialchars($goal['status']) ?></td>
                <td><?= htmlspecialchars($goal['timeline']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
} elseif ($role === 'admin') {
    $goalCounts = getGoalStatusCounts($db);
    ?>
    <h1>Admin Dashboard</h1>
    <h2>Analytics</h2>
    <canvas id="goalChart"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = <?= json_encode($goalCounts) ?>;
        const labels = chartData.map(item => item.status);
        const data = chartData.map(item => item.count);

        new Chart(document.getElementById('goalChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Goal Status Counts',
                    data: data,
                    backgroundColor: ['green', 'orange', 'red'],
                }]
            }
        });
    </script>
    <?php
}
?>
