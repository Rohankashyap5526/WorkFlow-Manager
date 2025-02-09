<?php
include 'your_script.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $_SESSION['user']['role'] === ROLE_MANAGER) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM goals WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user.";
    }
}
//echo $_POST['id'];
header("Location: manager_dashboard.php");
exit;
?>
