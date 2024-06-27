<?php
namespace App\Backend\Scripts\Login_Register;
require_once __DIR__ . '/../../../../vendor/autoload.php';


ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Backend\Classes\Database;
use App\Backend\Classes\User;

$db = new Database();

$user = new User($db);

$user->setUserDetails($_POST['username'], $_POST['email'], $_POST['password']);
$user->register();
?>
