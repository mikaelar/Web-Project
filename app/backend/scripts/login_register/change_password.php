<?php
namespace App\Backend\Scripts\Login_Register;

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Backend\Classes\Database;
use App\Backend\Classes\User;

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);

$user = new User($db);

$user->setUsername($_POST['username']);

$user->changePassword($_POST['current_password'], $_POST['new_password']);
?>
