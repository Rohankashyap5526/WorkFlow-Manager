<?php
include 'your_script.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $_SESSION['user']['role'] === ROLE_ADMIN) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user.";
    }
}
header("Location: admin_dashboard.php");
exit;
?>
