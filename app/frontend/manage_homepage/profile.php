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

// Fetch projects where the user is the author
$projects = [];
$query = "
    SELECT p.id, p.name, p.description 
    FROM projects p
    WHERE p.author = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $_SESSION['facultyNum']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

// Fetch projects where the user is a participant
$participatingProjects = [];
$query = "
    SELECT p.id, p.name, p.description 
    FROM projects p
    JOIN participants_in_projects pip ON p.id = pip.project_id
    WHERE pip.user_facultyNum = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $_SESSION['facultyNum']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $participatingProjects[] = $row;
}

// Fetch profile picture
$query = "SELECT profile_picture FROM users WHERE facultyNum = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $_SESSION['facultyNum']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$profilePicture = $user['profile_picture'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style_homepage.css">
    <link rel="stylesheet" href="additional_styles_homepage.css">
</head>
<body>
<header class="header">
    <div class="header-left">
        <h1>User Profile</h1>
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
<div class="content">
    <div class="profile-container">
        <h2>Добре дошли в профила си, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

        <div class="profile-picture">
            <?php if ($profilePicture): ?>
                <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" style="max-width: 200px; max-height: 200px;">
            <?php else: ?>
                <p>Не е качена профилна снимка.</p>
            <?php endif; ?>
            <h3>Промяна на профилната снимка:</h3>
            <form action="upload_profile_picture.php" method="post" enctype="multipart/form-data">
                <label for="facultyNum">Факултетен номер за верификация:</label>
                <input type="text" name="facultyNum" id="facultyNum" required><br><br>
                <label for="profilePicture">Качете нова профилна снимка:</label>
                <input type="file" name="profilePicture" id="profilePicture" accept="image/*" required><br><br>
                <input type="submit" name="submit" value="Качи">
            </form>
        </div>

        <div class="projects">
            <h3>Проекти, на които сте създател:</h3>
            <ul>
                <?php foreach ($projects as $project): ?>
                    <li>
                        <strong>Проект:</strong> <?php echo htmlspecialchars($project['name']); ?><br>
                        <strong>Описание:</strong> <?php echo htmlspecialchars($project['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="projects">
            <h3>Проекти, в които участвате:</h3>
            <ul>
                <?php foreach ($participatingProjects as $project): ?>
                    <li>
                        <strong>Проект:</strong> <?php echo htmlspecialchars($project['name']); ?><br>
                        <strong>Описание:</strong> <?php echo htmlspecialchars($project['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<footer>
    <p>&copy; 2024 Software Requirements Management</p>
</footer>
</body>
</html>
