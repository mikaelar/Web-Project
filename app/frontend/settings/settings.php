<?php
namespace App\Backend\Scripts;

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Backend\Classes\Database;
use App\Backend\Classes\Notifier;

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

$db = new Database();

$conn = $db->getConnection();
$notifications = [];


// Извличане на уведомления
$notifier = new Notifier($db, $_SESSION['facultyNum']);
$notifications = $notifier->getNotifications();
// Проверка за валидност на връзката
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photos - Software Requirements Management</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        section label {
    display: block;
    padding: 8px;
    background-color: #61dafb; /* Син цвят на фона */
    color: white; /* Бял цвят на текста */
    border-radius: 5px; /* Закръглени ъгли */
    margin-bottom: 10px; /* Разтояние между балоните */
    font-weight: bold; /* Удебелен текст */
    cursor: pointer; /* Курсор при навлизане */
}

section label:hover {
    background-color: #0056b3; /* Тъмно син цвят на фона при ховър */
    .balloon-input:focus {
    outline: none; /* Премахване на плъзгането */
    border-color: #0056b3; /* Син цвят на бордюра, когато е фокусирано */
}

.balloon-input {
    display: block;
    width: calc(100% - 16px); /* Ширина минус padding */
    padding: 8px;
    margin-bottom: 10px;
    border: 2px solid #0074d9; /* Син бордюр */
    border-radius: 5px; /* Закръглени ъгли */
    box-sizing: border-box; /* Включване на padding и border в размера на елемента */
    font-size: 14px;
    color: #333; /* Черен цвят на текста */
}
nav ul li a {
    color: #fff; /* Бял цвят на текста */
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    transition: background-color 0.3s, transform 0.3s;
    position: relative; /* Необходимо за позициониране на псевдоелемента */
}




}</style>
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
                                <span class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></span>
                                <a href="../../frontend/project_details/project_details.php?id=<?php echo $notification['project_id']; ?>">View Project</a>
                                <a class="mark-as-read" href="../../backend/scripts/mark_as_read.php?id=<?php echo $notification['id']; ?>">Mark as read</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>

    <main>
        <section>
    <div class="balloon-label">
        <label for="photoUpload">Choose Photo:</label>
    </div>
    <input type="file" id="photoUpload" name="photoUpload" accept="image/*" required>
</section>
<section>
    <div class="balloon-label">
        <label for="userName">User Name:</label>
    </div>
    <input type="text" id="userName" name="userName" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
</section>
<section>
    <div class="balloon-label">
        <label for="userDescription">Description:</label>
    
    <textarea id="userDescription" name="userDescription" required></textarea>
    </div>
</section>
<section>
    <div class="balloon-label">
        <label for="projectSelect">Select Project:</label>
    </div>
    
    <select id="projectSelect" name="projectSelect" required>
        <option value="">Select a project...</option>
        <option value="project1">Project 1</option>
        <option value="project2">Project 2</option>
        <!-- Add more project options if needed -->
    </select>
</section>

    </main>
     
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
    <footer>
        <p>&copy; 2024 Software Requirements Management</p>
    </footer>
</body>
</html>
