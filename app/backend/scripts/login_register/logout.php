<?php
namespace App\Backend\Scripts\Login_Register;
require_once __DIR__ . '/../../../../vendor/autoload.php';

session_start();
session_unset();
session_destroy();
header("Location: ../../../frontend/login_register/login.html");
exit();
?>
