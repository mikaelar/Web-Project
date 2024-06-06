<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Database.php';
require_once '../Project.php';

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);

$project = new Project($db);

$project->setProjectDetails($_POST['name'], $_POST['description'], $_POST['collaborators'], $_POST['initial_requirements']);

$project->create();
?>
