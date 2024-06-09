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
$query = "SELECT id, name FROM projects";
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
    <link rel="stylesheet" href="style_homepage.css">
</head>
<body>
    <div class="header">
        <h1>Добре дошли в системата за управление на изискванията</h1>
        <h2>Здравейте, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <button onclick="window.location.href='../login_register/logout.php'">Изход</button>
    </div>
    <div class="content">
        <button onclick="window.location.href='../create_project/create_project.html'">Създаване на нов проект</button>
        <div class="notifications">
            <button id="notificationButton">🔔 Notifications (<?php echo count($notifications); ?>)</button>
            <div id="notificationList" style="display: none;">
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
        <h3>Списък с проекти</h3>
        <ul>
            <?php foreach ($projects as $project): ?>
                <li><a href="../project_details.php?id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
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
