<?php 
require_once __DIR__ . '/../../../vendor/autoload.php';
use App\Backend\Classes\Database;
use App\Backend\Classes\Project;
use App\Backend\Classes\Notifier;

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

$db = new Database();
$project = Project::retrieveProjectInfo($db, $_SESSION['projectID']);
if ($project === null) {
    echo "Невалидно id на проект. Връщаме се обратно!";
    header("Location: ../manage_homepage/homepage.php");
}

$collaborators = $project->getCollaborators(); // We are given fn-s
$formattedCollaborators = implode(",", $collaborators);
// based on this fn-s do a query to get the full user info (we want to know the name at least and row)
$query = "SELECT facultyNum, username FROM users WHERE ID IN ($formattedCollaborators)";
$result = $db->getConnection()->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add collaborator</title>
    <link rel="stylesheet" href="../project_details/project_details.css">
    <link rel="stylesheet" href="specific_requirement_styles.css">
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
            <button id="logoutButton" onclick="location.href='../../backend/scripts/login_register/logout.php';">Logout</button>
        </div>
    </header>
    <div class="content">
        <div class="balloon">
            <?php while ()?>
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
