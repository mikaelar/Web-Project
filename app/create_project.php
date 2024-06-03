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

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO projects (name, description, collaborators, initial_requirements) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $description, $collaborators, $initial_requirements);

// Set parameters and execute
$name = $_POST['name'];
$description = $_POST['description'];
$collaborators = $_POST['collaborators'];
$initial_requirements = $_POST['initial_requirements'];

if ($stmt->execute()) {
    echo "New project created successfully";
    header("refresh:2;url=homepage.html");
} else {
    echo "Error: " . $stmt->error;
    header("refresh:2;url=create_project.html");
}

$stmt->close();
$conn->close();
?>
