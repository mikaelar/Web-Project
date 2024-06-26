<?php

namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

use App\Backend\Classes\Database;
use App\Backend\Classes\Notifier;

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";
$db = new Database($servername, $dbusername, $dbpassword, $dbname);
$conn = $db->getConnection();

// Fetch project details if ID is provided in the URL
$project = [];
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // Prepare and bind SELECT statement
    $query = "SELECT name, description, collaborators, initial_requirements FROM projects WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {
        echo "Проектът не е намерен.";
        exit();
    }
} else {
    echo "Грешка: Идентификаторът на проекта липсва.";
    exit();
}
// Fetch notifications
$notifier = new Notifier($db);
$notifications = $notifier->getNotifications();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Начална страница</title>
    <link rel="stylesheet" href="project_details.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f7;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .content {
            max-width: 800px;
            margin: 0 auto;
        }

        .balloon {
            position: relative;
            background-color: #61dafb;
            color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .balloon::before {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            border: 12px solid transparent;
            border-top-color: #61dafb;
            transform: translateX(-50%);
        }

        .balloon h2 {
            margin-top: 0;
        }

        .balloon p {
            margin: 0;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <h1>Software Requirments Management</h1>
        </div>
        <nav>
            <ul>
                <li><a href="../manage_homepage/homepage.php">Home</a></li>
                <li><a href="../create_project/create_project.html">Add Project</a></li>
                <li><a href="../settings/settings.php">Settings</a></li>
                <li><a href="../user_stories/user_stories.php">Manage User Stories</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <span id="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <div class="dropdown">
                <button id="notificationButton" class="notification-btn">🔔 Notifications (<?php echo count($notifications); ?>)</button>
                <div id="notificationList" class="dropdown-content" style="display: none;">
                    <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <li>
                                <?php echo htmlspecialchars($notification['message']); ?>
                                <a href="../../backend/scripts/mark_as_read.php?id=<?php echo $notification['id']; ?>">Маркирай като прочетено</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>
    <div class="content">
    <div class="balloon">
        <h2>Name</h2>
        <p><?php echo htmlspecialchars($project['name']); ?></p>
    </div>

    <div class="balloon">
        <h2>Description</h2>
        <p><?php echo htmlspecialchars($project['description']); ?></p>
    </div>

    <div class="balloon">
        <h2>Requirements</h2>
        <p><?php echo htmlspecialchars($project['initial_requirements']); ?></p>
    </div>

    <div class="balloon">
        <h2>Collaborators</h2>
        <p><?php echo htmlspecialchars($project['collaborators']); ?></p>
    </div>
</div>

    <footer>
        <p>&copy; 2024 Software Requirements Management</p>
                        </footer>
    <script src="main_page.js"></script>
    <script>
        document.getElementById('notificationButton').onclick = function() {
            var notificationList = document.getElementById('notificationList');
            if (notificationList.style.display === 'none') {
                notificationList.style.display = 'block';
            } else {
                notificationList.style.display = 'none';
            }
        };
    </script>
</body>
</html>
