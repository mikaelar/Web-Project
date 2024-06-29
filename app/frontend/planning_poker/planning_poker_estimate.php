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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hours = $_POST['hours'];
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("INSERT INTO estimations (project_id, username, hours) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $projectId, $username, $hours);

    if ($stmt->execute()) {
        echo "Estimation submitted successfully.";
        header("Location: ../planning_poker/planning_poker.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate Project</title>
    <link rel="stylesheet" href="style_planning_poker.css">
</head>
<style>

    h1 {
        font-size: 33px;
        text-align: center;
        margin-top: 50px; /* Примерно разстояние отгоре */
        font-family: Arial, sans-serif;
    }

    h1 a {
        font-size: 27px;
        color: #0074d9; /* Цвят на линка */
        text-decoration: none;
        border-bottom: 1px dashed #0074d9; /* Пунктирано подчертаване */
        transition: border-bottom 0.3s ease;
        font-family: Arial, sans-serif;
    }
    footer {
        text-align: center;
        padding: 10px 0;
        position: fixed;
        width: 100%;
    }
    body {
        padding-bottom: 60px; /* Добавете 10px допълнително за възможно допълнение */
    }

    h1 a:hover {
        border-bottom: 1px solid #0074d9; /* Пълно подчертаване при ховър */
    }
</style>
<body>

<header class="header">
        <div class="header-left">
            <h1>Software Requirements Management</h1>
        </div>
        <nav>
            <ul>
                <li><a href="../manage_homepage/homepage.php">Home</a></li>
                <li><a href="../create_project/create_project.html">Add Project</a></li>
                <li><a href="../settings/settings.php">Settings</a></li>
                <li><a href="../user_stories/user_stories.php">Manage User Stories</a></li>
                <li><a href="../planning_poker/planning_poker.php">Planning Poker</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <span id="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>
    <h1>Избери часове за избрания проект</h1>
    <form action="planning_poker_estimate.php?project_id=<?php echo htmlspecialchars($projectId); ?>" method="post">
        <label for="hours">Избери часове:</label>
        <select name="hours" id="hours">
            <option value="3">3 часа</option>
            <option value="6">6 часа</option>
            <option value="9">9 часа</option>
            <option value="12">12 часа</option>
            <option value="15">15 часа</option>
            <option value="18">18 часа</option>
            <option value="21">21 часа</option>
            <option value="24">24 часа</option>
            <option value="28">28 часа</option>
            <option value="30">30 часа</option>
        </select>
        <button type="submit">Избери</button>
    </form>
</body>
</html>
