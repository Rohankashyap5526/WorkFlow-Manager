<?php
include 'your_script.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_MANAGER) {
    die("Access denied.");
}

$managerId = $_SESSION['user']['id'];

// Fetch employees under this manager
$employees = getEmployeesForManager($db, $managerId);

// Fetch goals assigned by this manager
$goals = getManagerGoals($db, $managerId);
$performanceData = getEmployeePerformance($db, $managerId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="manager_dashboard.css">
</head>
<body>
    <h1>Manager Dashboard</h1>

    <!-- Assigned Goals Section -->
    <h2>Assigned Goals</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Goal</th>
                <th>Employee id</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($goals as $goal): ?>
            <tr>
                <td><?= htmlspecialchars($goal['title']) ?></td>
                <td><?= htmlspecialchars($goal['employee_id']) ?></td>
                <td><?= htmlspecialchars($goal['status']) ?></td>
                <td><?= htmlspecialchars($goal['timeline']) ?></td>
                <td>
                    <form action="delete_goal.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $goal['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Assign New Goal Section -->
    <h2>Assign New Goal</h2>
    <form action="assign_goal.php" method="post">
    <input type="hidden" name="manager" value="<?= $managerId ?>">
    

    <label for="title">Goal Title:</label>
    <input type="text" id="title" name="title" required><br>

    <label for="description">Goal Description:</label>
    <textarea id="description" name="description" required></textarea><br>

    <label for="employee">Assign To:</label>
    <select id="employee" name="employee_id" required>
        <?php foreach ($employees as $employee): ?>
            <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['username']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <label for="priority">Priority:</label>
    <select id="priority" name="priority" required>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select><br>

    <label for="timeline">Timeline:</label>
    <input type="date" id="timeline" name="timeline" required><br>

    <button type="submit">Create Goal</button>
</form>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h2>Employee Performance Chart</h2>
<canvas id="performanceChart" width="400" height="200"></canvas>






    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
<?php
$performanceData = getEmployeePerformance($db, $managerId);

$employeeNames = [];
$totalGoals = [];
$completedGoals = [];
$pendingGoals = [];
$inprocessGoals = [];

foreach ($performanceData as $data) {
    $employeeNames[] = $data['employee_name'];
    $totalGoals[] = $data['total_goals'];
    $completedGoals[] = $data['completed_goals'];
    $pendingGoals[] = $data['pending_goals'];
    $inprocessGoals[] = $data['inprocess_goals'];
}

?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get data from PHP
        const employeeData = <?= json_encode($performanceData); ?>;

        if (!employeeData || employeeData.length === 0) {
            console.error("No performance data found for chart.");
            return;
        }

        // Prepare data for the chart
        const labels = employeeData.map(data => data.employee_name); // Employee names
        const totalGoals = employeeData.map(data => data.total_goals); // Total goals
        const completedGoals = employeeData.map(data => data.completed_goals); // Completed goals
        const pendingGoals = employeeData.map(data => data.pending_goals); // Pending goals
        const inProcessGoals = employeeData.map(data => data.inprocess_goals); // In-process goals

        // Get the canvas context
        const ctx = document.getElementById('performanceChart').getContext('2d');

        // Render the chart
        new Chart(ctx, {
            type: 'bar', // Type of chart: 'bar', 'line', etc.
            data: {
                labels: labels, // X-axis labels (employee names)
                datasets: [
                    {
                        label: 'Total Goals',
                        data: totalGoals,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Completed Goals',
                        data: completedGoals,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Pending Goals',
                        data: pendingGoals,
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'In-Process Goals',
                        data: inProcessGoals,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Employee Performance Overview',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    });
</script>
