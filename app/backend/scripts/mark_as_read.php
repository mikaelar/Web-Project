<?php
namespace App\Backend\Scripts;

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../frontend/login_register/login.html");
    exit();
}

use App\Backend\Classes\Database;
use App\Backend\Classes\Notifier;;

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);
$notifier = new Notifier($db);

if (isset($_GET['id'])) {
    $notifier->markAsRead(intval($_GET['id']));
}

header("Location: ../../frontend/manage_homepage/homepage.php");
?>
