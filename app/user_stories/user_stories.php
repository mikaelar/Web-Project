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

// Fetch user stories
$userStories = [];
$query = "SELECT id, name, title, description FROM user_stories";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userStories[] = $row;
    }
}

// Fetch notifications
$notifier = new Notifier($db);
$notifications = $notifier->getNotifications();

// Handle form submission for adding, editing, and removing user stories
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    
    if ($action == "add") {
        $title = $conn->real_escape_string($_POST['userStoryTitle']);
        $description = $conn->real_escape_string($_POST['userStoryDescription']);
        $sql = "INSERT INTO user_stories (title, description) VALUES ('$title', '$description')";
        if ($conn->query($sql) === TRUE) {
            // Notify about the new user story
            $notifier->addNotification("A new user story has been added!");
            header("Location: UserStories.php");
            exit();
        }
    } elseif ($action == "edit") {
        $id = intval($_POST['editUserStorySelect']);
        $title = $conn->real_escape_string($_POST['editUserStoryTitle']);
        $description = $conn->real_escape_string($_POST['editUserStoryDescription']);
        $sql = "UPDATE user_stories SET title='$title', description='$description' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            // Notify about the user story edit
            $notifier->addNotification("A user story has been edited!");
            header("Location: UserStories.php");
            exit();
        }
    } elseif ($action == "remove") {
        $id = intval($_POST['removeUserStorySelect']);
        $sql = "DELETE FROM user_stories WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            // Notify about the user story removal
            $notifier->addNotification("A user story has been removed!");
            header("Location: UserStories.php");
            exit();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Stories - Software Requirements Management</title>
    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <li><a href="UserStories.php">Manage User Stories</a></li>
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
    <main>
        <section id="manage-user-stories">
            <h2>Manage User Stories</h2>
            <form method="POST" action="UserStories.php">
                <h3>Add User Story</h3>
                <label for="userStoryTitle">Title:</label>
                <input type="text" id="userStoryTitle" name="userStoryTitle" required>
                <label for="userStoryDescription">Description:</label>
                <textarea id="userStoryDescription" name="userStoryDescription" required></textarea>
                <input type="hidden" name="action" value="add">
                <button type="submit">Add User Story</button>
            </form>
            <form method="POST" action="UserStories.php">
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
            <form method="POST" action="UserStories.php">
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
    <footer>
        <p>&copy; 2024 Software Requirements Management</p>
    </footer>
    <script>
    $(document).ready(function() {
        // Показване и скриване на нотификациите
        $('#notificationButton').on('click', function() {
            $('#notificationList').toggle();
        });

        // Скриване на нотификациите при клик извън тях
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.dropdown').length) {
                $('#notificationList').hide();
            }
        });
    });
    </script>
</body>
</html>
