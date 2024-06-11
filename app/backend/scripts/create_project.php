<?php
namespace App\Backend\Scripts;

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Backend\Classes\Database;
use App\Backend\Classes\Project;
use App\Backend\Classes\Notifier;


$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);

$project = new Project($db);
$notifier = new Notifier($db);

$project->setProjectDetails($_POST['name'], $_POST['description'], $_POST['collaborators'], $_POST['initial_requirements']);

if ($project->create()) {
    $notifier->addNotification("Нов проект е създаден: " . $_POST['name']);
    header("Location: ../../frontend/manage_homepage/homepage.php");
} else {
    header("Location: ../../frontend/create_project/create_project.html");
}
?>
