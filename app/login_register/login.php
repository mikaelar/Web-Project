<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Database.php';
require_once '../User.php';

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);

$user = new User($db);

$user->setUsername($_POST['username']);
$user->authenticate($_POST['password']);
?>
