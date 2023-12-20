<!--Logout page-->
<?php
// Unset shopping cart, flags and user paramaters
include 'config.php';
unset($_SESSION['FLAG']);
unset($_SESSION['cart']);
setcookie('user_token', '', time() - 3600);
session_unset();
session_destroy();
session_start();
header('location: index.php');

?>