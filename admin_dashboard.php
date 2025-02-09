<?php
include 'your_script.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_ADMIN) {
    die("Access denied.");
}
// Fetch employees, managers, and departments from the database
$employees = getAllEmployees($db);
$managers = getAllManagers($db);
$departments = getAllDepartments($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_manager'])) {
    $managerId = $_POST['manager_id'];
    $employeeId = $_POST['employee_id'];
    assignManager($db, $managerId, $employeeId);
    echo "Manager assigned successfully!";
}

$stmt = $db->prepare("SELECT id, username, manager_id, role FROM users");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <h2>Manage Users</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Manager id</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['manager_id']) ?></td>
                <td>
                    <form action="delete_user.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add User</h2>
    <form action="process_register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="employee">Employee</option>
        </select>
        <br>
        <button type="submit">Add User</button>
    </form>

    <h3>Assign Manager to Employee</h3>
    <form method="post">
        <label for="manager">Manager:</label>
        <select id="manager" name="manager_id">
            <?php foreach ($managers as $manager): ?>
                <option value="<?= $manager['id'] ?>"><?= $manager['username'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="employee">Employee:</label>
        <select id="employee" name="employee_id">
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id'] ?>"><?= $employee['username'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="assign_manager">Assign</button>
    </form>

    <!-- <h3>Departments</h3>
    <ul>
        <//?php foreach ($departments as $department): ?>
            <li><//?= $department['name'] ?></li>
        <//?php endforeach; ?>
    </ul> -->

    <a href="logout.php">Logout</a>
</body>
</html>
