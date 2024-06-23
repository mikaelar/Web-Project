<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

require_once '../Database.php';
require_once '../Notifier.php';

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";
$db = new Database($servername, $dbusername, $dbpassword, $dbname);
$conn = $db->getConnection();

// Fetch projects
$projects = [];
$query = "SELECT id, name, description FROM projects";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Fetch notifications
$notifier = new Notifier($db);
$notifications = $notifier->getNotifications();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="main.css">
 
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
                <li><a href="../projects/Projects.html" target="_blank">Projects</a></li>
                <li><a href="../settings/settings.html">Settings</a></li>
                <li><a href="../user_stories/UserStories.html">Manage User Stories</a></li>
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
                                <a href="mark_as_read.php?id=<?php echo $notification['id']; ?>">Mark as read</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button id="logoutButton" onclick="location.href='../login_register/logout.php';">Logout</button>
        </div>
    </header>
    <div class="welcome-message">
        <h1>Добре дошли в системата за управление на изискванията</h1>
    

</div>
    <div class="content">
        <div class="board">
            <?php foreach ($projects as $project): ?>
            <div class="list">
                <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                <div class="cards">
                    
                <div class="card" draggable="true" data-id="<?php echo $project['id']; ?>">
                        <p><?php echo htmlspecialchars($project['description']); ?></p>
                        <button onclick="location.href='../project_details.php?id=<?php echo $project['id']; ?>';">Open Project</button>
                    </div>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2024 Software Requirements Management</p>
    </footer>
    <script src="main.js"></script>
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


