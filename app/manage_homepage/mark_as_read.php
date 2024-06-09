<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login_register/login.html");
    exit();
}

require_once '../Database.php';
require_once '../Notifier.php';

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);
$notifier = new Notifier($db);

if (isset($_GET['id'])) {
    $notifier->markAsRead(intval($_GET['id']));
}

header("Location: ../manage_homepage/homepage.php");
?>
