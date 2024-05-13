<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get query by this username. Then hash the current password and check if it matches the hashed

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


    // Retrieve username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        // Get the result
        $result = $stmt->get_result();
    
        // Fetch the row
        $row = $result->fetch_assoc();

        // Check if a row is fetched and compare the password
        if ($row && password_verify($password, $row['password'])) {
            echo "Login successful!";
            // TODO transfer him to another page (his home) with all his accessible tables, projects and so on
        } else {
            echo "Грешно потребителско име или парола. Моля, опитайте отново.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>