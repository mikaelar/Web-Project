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


$date = date('Y-m-d H:i:s');
// author is facultyNum from session
$project = new Project($_POST['name'], $_POST['description'], $date, $_SESSION['facultyNum']);

$db = new Database();
if ($_SESSION['username'] !== "admin") {
    $notifier = new Notifier($db, $_SESSION['facultyNum']);
}
if ($project->create($db)) {
    if ($_SESSION['username'] !== "admin") {
        $project->addCollaborator($db, $_SESSION['facultyNum']);
        $notifier->addNotification("Нов проект е създаден: " . $_POST['name'], $date);
    }
    header("Location: ../../frontend/manage_homepage/homepage.php");
} else {
    header("Location: ../../frontend/create_project/create_project.html");
}
?>
