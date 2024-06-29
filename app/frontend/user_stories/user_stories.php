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

// Проверка за валидност на връзката
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Инициализация на масиви за потребителски истории и уведомления
$userStories = [];
$notifications = [];

// Извличане на потребителски истории
$sqlUserStories = "SELECT id, title, description FROM userstories";
$resultUserStories = $conn->query($sqlUserStories);

if ($resultUserStories->num_rows > 0) {
    while ($row = $resultUserStories->fetch_assoc()) {
        $userStories[] = $row;
    }
}

// Извличане на уведомления
$notifier = new Notifier($db);
$notifications = $notifier->getNotifications();

// Обработка на формуляра за добавяне, редактиране или премахване на потребителска история
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == "add") {
            $title = mysqli_real_escape_string($conn, $_POST['userStoryTitle']);
            $description = mysqli_real_escape_string($conn, $_POST['userStoryDescription']);
            $sqlAdd = "INSERT INTO userstories (title, description) VALUES ('$title', '$description')";
           
        } elseif ($action == "edit") {
            $id = intval($_POST['editUserStorySelect']);
            $title = mysqli_real_escape_string($conn, $_POST['editUserStoryTitle']);
            $description = mysqli_real_escape_string($conn, $_POST['editUserStoryDescription']);
            $sqlEdit = "UPDATE userstories SET title='$title', description='$description' WHERE id=$id";
            
        } elseif ($action == "remove") {
            $id = intval($_POST['removeUserStorySelect']);
            $sqlRemove = "DELETE FROM userstories WHERE id=$id";
            
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Stories - Software Requirements Management</title>
    <link rel="stylesheet" href="styles_user_stories.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content.show {
            display: block;
        }
        footer {
            text-align: center;
            padding: 10px 0;
            position: center;
            width: 100%;
        }
    </style>
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
        <section id="manage-user-stories">
            <h2>Manage User Stories</h2>
            <form method="POST" action="user_stories.php">
                <h3>Add User Story</h3>
                <label for="userStoryTitle">Title:</label>
                <input type="text" id="userStoryTitle" name="userStoryTitle" required>
                <label for="userStoryDescription">Description:</label>
                <textarea id="userStoryDescription" name="userStoryDescription" required></textarea>
                <input type="hidden" name="action" value="add">
                <button type="submit">Add User Story</button>
            </form>
            
            <form method="POST" action="user_stories.php">
                <h3>Edit User Story</h3>
                <label for="editUserStorySelect">Select User Story:</label>
                <select id="editUserStorySelect" name="editUserStorySelect" required>
                    <?php foreach ($userStories as $story): ?>
                        <option value="<?php echo $story['id']; ?>"><?php echo htmlspecialchars($story['title']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="editUserStoryTitle">New Title:</label>
                <input type="text" id="editUserStoryTitle" name="editUserStoryTitle" required>
                <label for="editUserStoryDescription">New Description:</label>
                <textarea id="editUserStoryDescription" name="editUserStoryDescription" required></textarea>
                <input type="hidden" name="action" value="edit">
                <button type="submit">Edit User Story</button>
            </form>

            <form method="POST" action="user_stories.php">
                <h3>Remove User Story</h3>
                <label for="removeUserStorySelect">Select User Story:</label>
                <select id="removeUserStorySelect" name="removeUserStorySelect" required>
                    <?php foreach ($userStories as $story): ?>
                        <option value="<?php echo $story['id']; ?>"><?php echo htmlspecialchars($story['title']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="action" value="remove">
                <button type="submit">Remove User Story</button>
            </form>
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
