<?php
namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../frontend/login_register/login.html");
    exit();
}

use App\Backend\Classes\Database;
use App\Backend\Classes\Notifier;;

$db = new Database();

$notifier = new Notifier($db, $_SESSION['facultyNum']);

if (isset($_GET['id'])) {
    $notifier->markAsRead(intval($_GET['id']));
}

// to go back to current page, we need to store the path in the session and update it when we change scripts
header("Location: ../../frontend/manage_homepage/homepage.php");
?>
