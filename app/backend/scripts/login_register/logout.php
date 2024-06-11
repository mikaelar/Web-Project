<?php
namespace App\Backend\Scripts\Login_Register;

session_start();
session_unset();
session_destroy();
header("Location: ../../../frontend/login_register/login.html");
exit();
?>
