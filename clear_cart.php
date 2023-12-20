<!--Clear the current shopping cart-->
<?php
include 'config.php';

session_start();
unset($_SESSION['cart']);
//Update cart content in cookie
if(isset($_SESSION['email']) && isset($_COOKIE['user_token'])){
    $c = json_decode(stripslashes($_COOKIE['user_token']),true);
    $token = array('id'=> $c['id'],'email'=> $_SESSION['email'], 'expire'=> $c['expire'],'cart'=> [], 'key'=> $c['key']);
            
    // Setting cookie
    setcookie('user_token', json_encode($token), $c['expire'],'', '', true, true);
}

header("location: shopping-cart.php", true, 302);
?>