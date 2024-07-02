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

$db = new Database();

$conn = $db->getConnection();

$projects = [];
// Fetch projects (all if he is admin)
// TODO - split the project on 2 groups - authorized and just participating
$query = "SELECT id, name, description FROM projects";
if ($_SESSION['username'] !== "admin") {
    $query = "SELECT id, name, description FROM projects WHERE id IN (SELECT project_id FROM participants_in_projects WHERE user_facultyNum = ?)";   
}
$stmt = $conn->prepare($query);
if ($_SESSION['username'] !== "admin") {
    $stmt->bind_param('s', $_SESSION['facultyNum']);
}
$stmt->execute();
$stmt->store_result();


// Check if it fetches only my projects
if ($stmt->num_rows > 0) {
    while ($row = $stmt->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Fetch notifications (Check if it fetches only my notifications)
$notifier = new Notifier($db, $_SESSION['facultyNum']);
$notification = [];
if ($_SESSION['username'] !== "admin") {
    $notifications = $notifier->getNotifications();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="style_homepage.css">
    <link rel="stylesheet" href="additional_styles_homepage.css">
    <script src="notifications_button.js" defer></script>
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
            <div class="dropdown">
                <button id="notificationButton" class="notification-btn">🔔 Notifications (<?php echo count($notifications); ?>)</button>
                <div id="notificationList" class="dropdown-content">
                    <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <li>
                                <?php echo htmlspecialchars($notification['message']); ?>
                                <a href="../../backend/scripts/mark_as_read.php?id=<?php echo $notification['id']; ?>">Mark as read</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>
    <div class="welcome-message">
        <h1>Добре дошли в системата за управление на изискванията! </h1>
        <h1><a href="../../backend/scripts/export_projects.php">Експортирай проектите?</a></h1>
    </div>

    <div class="content">
        <div class="board">
            <?php foreach ($projects as $project): ?>
            <div class="list">
                <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                <div class="cards">
                    
                <div class="card" draggable="true" data-id="<?php echo $project['id']; ?>">
                        <p><?php echo htmlspecialchars($project['description']); ?></p>
                        <button onclick="location.href='../../frontend/project_details/project_details.php?id=<?php echo $project['id']; ?>';">Open Project</button>
                    </div>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2024 Software Requirements Management</p>
    </footer>
</body>
</html>