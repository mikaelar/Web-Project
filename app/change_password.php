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

// Prepare and execute the query to fetch the current password
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);

// Set parameter and execute
$username = $_POST['username'];
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    
    // Verify the current password
    if (password_verify($_POST['current_password'], $hashed_password)) {
        // Hash the new password
        $new_password_hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        
        // Prepare and execute the update query
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $new_password_hashed, $username);
        if ($update_stmt->execute()) {
            echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Паролата е успешно променена! Препращане към страницата за вход в системата.</div>';
            header("refresh:2;url=login.html");
            exit();
        } else {
            echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Възникна грешка при промяната на паролата. Препращане към страницата за вход в системата.</div>';
            header("refresh:2;url=login.html");
            exit();
        }
        $update_stmt->close();
    } else {
        echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Грешна текуща парола! Препращане към страницата за вход в системата.</div>';
        header("refresh:2;url=login.html");
        exit();
    }
} else {
    echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Потребителят не е намерен. Препращане към страницата за регистрация в системата.</div>';
    header("refresh:2;url=register.html");
    exit();
}

$stmt->close();
$conn->close();
?>
