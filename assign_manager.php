<?php
// Include your_script.php
require_once 'your_script.php';

// Fetch all managers
$stmt = $db->prepare("SELECT id, username FROM users WHERE role = ?");
$stmt->execute([ROLE_MANAGER]);
$managers = $stmt->fetchAll();

// Fetch all employees
$stmt = $db->prepare("SELECT id, username FROM users WHERE role = ?");
$stmt->execute([ROLE_EMPLOYEE]);
$employees = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_manager'])) {
    $managerId = $_POST['manager_id'];
    $employeeId = $_POST['employee_id'];

    if (assignManager($db, $managerId, $employeeId)) {
        echo "Manager assigned successfully!";
    } else {
        echo "Failed to assign manager.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Manager</title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional: Add your stylesheet -->
</head>
<body>
    <h1>Assign Manager to Employee</h1>
    <form method="post" action="">
        <label for="manager">Manager:</label>
        <select id="manager" name="manager_id" required>
            <option value="" disabled selected>Select a Manager</option>
            <?php foreach ($managers as $manager): ?>
                <option value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['username']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="employee">Employee:</label>
        <select id="employee" name="employee_id" required>
            <option value="" disabled selected>Select an Employee</option>
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['username']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <button type="submit" name="assign_manager">Assign</button>
    </form>
</body>
</html>
