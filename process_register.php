<!-- Process Register (process_register.php) -->
<?php
include 'your_script.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (registerUser($db, $username, $password, $role)) {
        echo "User registered successfully!";
    } else {
        echo "Failed to register user. Please try again.";
    }
}
?>
