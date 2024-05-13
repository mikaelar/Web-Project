<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "form";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute the query
$stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);

// Set parameter and execute
$username = $_POST['username'];
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($username, $hashed_password);
    $stmt->fetch();
    
    // Verify the password
    if (password_verify($_POST['password'], $hashed_password)) {
        echo "Успешен вход!";
        // Start session or redirect user to another page as needed
    } else {
        echo "Грешна парола!";
    }
} else {
    echo "Потребителят не е намерен.";
}

$stmt->close();
$conn->close();
?>
