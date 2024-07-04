<?php

namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

use App\Backend\Classes\Database;
use App\Backend\Classes\Project;
use App\Backend\Classes\Notifier;

$db = new Database();

$conn = $db->getConnection();

// Delete project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $project_id = $_POST['id'];
    $project = new Project($db);

    if ($project->delete($project_id)) {
        header("Location: ../manage_homepage/homepage.php");
        exit();
    } else {
        echo "Проектът не може да бъде изтрит.";
    }
}

// Project edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
    $project_id = $_POST['id'];
    $fields = [];
    foreach (['name', 'description'] as $field) {
        if (isset($_POST[$field])) {
            $fields[$field] = $_POST[$field];
        }
    }

    Project::update($db, $project_id, $fields);

    header("Location: project_details.php?id=" . $project_id);
    exit();
}

// Fetch project details if ID is provided in the URL
$project = [];
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $_SESSION['projectID'] = $project_id;

    $query = "SELECT name, description, author, created_at FROM projects WHERE id = ?";
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
} else if (isset($_SESSION['projectID'])) {
    $query = "SELECT name, description, author, created_at FROM projects WHERE id = ?";
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
$notifier = new Notifier($db, $_SESSION['facultyNum']);
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

        .edit-icon {
            cursor: pointer;
            margin-left: 10px;
            font-size: 16px;
        }

        .edit-form {
            display: none;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 6px;
            margin-bottom: 16px;
            resize: vertical;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .edit-form {
            margin-top: 10px;
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
            <h2>Name <span class="edit-icon" onclick="editSection('name')">✏️</span></h2>
            <p id="name"><?php echo htmlspecialchars($project['name']); ?></p>
            <form id="edit-name-form" class="edit-form" method="POST" action="project_details.php">
                <input type="text" name="name" value="<?php echo htmlspecialchars($project['name']); ?>">
                <input type="hidden" name="id" value="<?php echo $project_id; ?>">
                <input type="hidden" name="update_project" value="true">
                <button type="submit">Save</button>
                <button type="button" onclick="cancelEdit('name')">Cancel</button>
            </form>
        </div>

        <div class="balloon">
            <h2>Description <span class="edit-icon" onclick="editSection('description')">✏️</span></h2>
            <p id="description"><?php echo htmlspecialchars($project['description']); ?></p>
            <form id="edit-description-form" class="edit-form" method="POST" action="project_details.php">
                <textarea name="description"><?php echo htmlspecialchars($project['description']); ?></textarea>
                <input type="hidden" name="id" value="<?php echo $project_id; ?>">
                <input type="hidden" name="update_project" value="true">
                <button type="submit">Save</button>
                <button type="button" onclick="cancelEdit('description')">Cancel</button>
            </form>
        </div>

        <div class="balloon">
            <h2>Author</h2>
            <p id="author"><?php 
            $author = $project['author'];
            echo htmlspecialchars($project['author']); 
            ?></p>
        </div>

        <div class="balloon">
            <h2>Creation date </h2>
            <p id="created_at"><?php echo htmlspecialchars($project['created_at']); ?></p>
        </div>
        <div class="balloon">
            <h2>Requirements <span class="edit-icon"><a href="../requirement/requirement.html">✏️</a></span></h2>
        </div>

        <div class="balloon">
            <h2>Collaborators <span class="edit-icon"><a href="../collaborators/collaborators.html">✏️</a></span></h2>
        </div>

        <form method="POST" action="project_details.php">
            <input type="hidden" name="id" value="<?php echo $project_id; ?>">
            <input type="hidden" name="delete_project" value="true">
            <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this project?')">Delete Project</button>
        </form>
    </div>
    <footer>
        <p>&copy; 2024 Software Requirements Management</p>
    </footer>
    <script src="main_page.js"></script>
    <script>
        function editSection(section) {
            document.getElementById(section).style.display = 'none';
            document.getElementById('edit-' + section + '-form').style.display = 'block';
        }

        function cancelEdit(section) {
            document.getElementById(section).style.display = 'block';
            document.getElementById('edit-' + section + '-form').style.display = 'none';
        }
    </script>
</body>
</html>
