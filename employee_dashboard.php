<?php
include 'your_script.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_EMPLOYEE) {
    die("Access denied.");
}

$employeeId = $_SESSION['user']['id'];

// Fetch goals assigned to this employee
$goals = getGoals($db, $employeeId);

// Prepare chart data
$chartData = [];
$goalStatuses = ['Pending', 'In Progress', 'Completed'];
foreach ($goalStatuses as $status) {
    $count = array_reduce($goals, function ($carry, $goal) use ($status) {
        return $carry + ($goal['status'] === $status ? 1 : 0);
    }, 0);
    $chartData[] = ['status' => $status, 'count' => $count];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Employee Dashboard</h1>
    <link rel="stylesheet" href="employee_dashboard.css">

    <!-- Goals Table -->
    <h2>Your Goals</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Goal</th>
                <th>Description</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($goals as $goal): ?>
            <tr>
                <td><?= htmlspecialchars($goal['title']) ?></td>
                <td><?= htmlspecialchars($goal['description']) ?></td>
                <td><?= htmlspecialchars($goal['status']) ?></td>
                <td><?= htmlspecialchars($goal['timeline']) ?></td>
                <td>
                    <form action="update_goal_status.php" method="post">
                        <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                        <select name="status">
                            <option value="Pending" <?= $goal['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= $goal['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= $goal['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit">Update Status</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Chart -->
    <!-- Chart Section -->
<h2>Your Goal Status Overview</h2>
<div style="width: 500px; height: 400px; margin: 0 auto;">
    <canvas id="goalChart"></canvas>
</div>


    <a href="logout.php">Logout</a>
</body>
</html>
<script>
   document.addEventListener("DOMContentLoaded", function () {
    // Parse PHP data into JavaScript
    const chartData = <?= json_encode($chartData); ?>;

    // Prepare data for the chart
    const labels = chartData.map(item => item.status);
    const data = chartData.map(item => item.count);

    // Get the canvas context
    const ctx = document.getElementById('goalChart').getContext('2d');

    // Render the chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Goal Status Counts',
                data: data,
                backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)'],
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Prevents the chart from scaling excessively
            plugins: {
                legend: {
                    display: true,
                },
                tooltip: {
                    enabled: true,
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Goal Status',
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Count',
                    },
                    beginAtZero: true,
                },
            },
        },
    });
});

</script>
