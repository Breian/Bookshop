<?php
include 'config.php';
include 'csrf.php';

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Invalid page access");
    header("location: index.php", true, 401);  
      
}

if($_REQUEST['action'] === 'pay'){
    if(!csrf_verifyNonce($_REQUEST['action'], $_POST['nonce'])){
        $user = mysqli_real_escape_string($conn, $_SESSION['email']);
        logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Fatal", "CSRF detected");
        throw new Exception("Error: CSRF detected");
    }



}
// Check if the card_number and CVV are only numbers
if(strlen($_POST['card_number']) != 16 || !preg_match("/^[0-9]*$/", $_POST['card_number'])){
    $_SESSION['FLAG'] = "ERROR_CARD";
    header("location: order.php");
    exit();
}
if(strlen($_POST['cvv']) != 3 || !preg_match("/^[0-9]*$/", $_POST['cvv'])){
    $_SESSION['FLAG'] = "ERROR_CVV";
    header("location: order.php");
    exit();
}
// Check if shipping code is only 5 numbers
if(strlen($_POST['shipping']) != 5 || !preg_match("/^[0-9]*$/", $_POST['shipping'])){
    $_SESSION['FLAG'] = "ERROR_SHIPPING";
    header("location: order.php");
    exit();
}


// Payment process 
foreach ($_SESSION['cart'] as $cart_item) {
    
    $id_book = $cart_item['id'];
    $id_user = $_SESSION['user_id'];
    $date = date('Y-m-d');
    $sql = "INSERT INTO purchases(USER_ID, BOOK_ID, PURCHASE_DATE) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
        header("Location: registration.php");
        exit();
    }
    // setting parameters of parametric query
    $stmt->bind_param('iis', $id_user, $id_book, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['FLAG'] = "PURCHASE_COMPLETE";
    unset($_SESSION['cart']);
    session_regenerate_id(true);
    header("location: index.php", true, 302);

}
?>