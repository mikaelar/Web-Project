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

// Fetch projects
$projects = [];
$query = "SELECT id, name FROM projects";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning Poker</title>
    <link rel="stylesheet" href="style_planning_poker.css">
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
                <li><a href="../planning_poker/planning_poker.php">Planning Poker</a></li>
                <li><a href="../manage_homepage/profile.php">Profile</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <span id="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>

    <h1>Избери проект за естимиране:</h1>
    <form action="planning_poker_estimate.php" method="get">
    <label for="project">Избери проект:</label>
    <select name="project_id" id="project">
        <?php foreach ($projects as $project): ?>
            <option value="<?php echo htmlspecialchars($project['id']); ?>"><?php echo htmlspecialchars($project['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Избери</button>
</form>

<h2>Прегледай естимирани часове по проектите:</h2>
<ul>
    <?php foreach ($projects as $project): ?>
        <li>
            <a href="view_estimations.php?project_id=<?php echo htmlspecialchars($project['id']); ?>">
                Прегледай часове за <?php echo htmlspecialchars($project['name']); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>
