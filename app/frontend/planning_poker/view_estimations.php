<?php
namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

use App\Backend\Classes\Database;

$db = new Database();
$conn = $db->getConnection();

$projectId = $_GET['project_id'];

// Fetch estimations
$estimations = [];
$stmt = $conn->prepare("SELECT username, hours FROM estimations WHERE project_id = ?");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $estimations[] = $row;
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Estimations</title>
    <link rel="stylesheet" href="style_planning_poker.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <h1>Software Requirements Management</h1>
        </div>
        <nav>
            <ul>
                <li><a href="../manage_homepage/homepage.php">Home</a></li>
                <li><a href="../create_project/create_project.html">Add Project</a></li>
                <li><a href="../planning_poker/planning_poker.php">Planning Poker</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <span id="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>
    <h1>Estimations for Project</h1>
    <table>
        <tr>
            <th>Username</th>
            <th>Hours</th>
        </tr>
        <?php foreach ($estimations as $estimation): ?>
            <tr>
                <td><?php echo htmlspecialchars($estimation['username']); ?></td>
                <td><?php echo htmlspecialchars($estimation['hours']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
