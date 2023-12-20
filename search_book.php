<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Search</title>
</head>
<body>

<?php
include 'config.php';
include 'navbar.php';
include 'search_comp.php';



?>
<?php
if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_LEN') {
    unset($_SESSION['FLAG']);
    echo '<script>alert("Error: invalid input")</script>'; 
  }

// Add items to the cart when clicked
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    // Store item in the cart array
    $_SESSION['cart'][] = [
        'id' => $item_id,
        'name' => $item_name,
        'price' => $item_price
    ];
    //Update cart content in cookie
    if(isset($_SESSION['email']) && isset($_COOKIE['user_token'])){
        $c = json_decode(stripslashes($_COOKIE['user_token']),true);
        $token = array('id'=> $c['id'],'email'=> $_SESSION['email'], 'expire'=> $c['expire'],'cart'=> $_SESSION['cart'], 'key'=> $c['key']);
                
        // Setting cookie
        setcookie('user_token', json_encode($token), $c['expire'],'', '', true, true);
    }

    echo "<p>$item_name added to cart.</p>";
    $total_price = 0;
    foreach ($_SESSION['cart'] as $cart_item) {
    $total_price += $cart_item['price'];
    }
}



?>



</body>

</html>