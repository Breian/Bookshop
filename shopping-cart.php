<!-- This shopping-cart.php file retrieves the cart items from the session and displays them. 
If the cart is empty, it shows a message indicating that the cart is empty. 
Otherwise, it lists the items in the cart along with the total price.
IMPORTANT: Ensure that you have session_start() at the beginning of both pages to initialize sessions and maintain cart data across pages.-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
  <title>Shopping Cart</title>
</head>
<body>
<?php
include 'config.php';
include 'navbar.php';
?>
    

<h1 style="text-align:center;">Your Shopping Cart</h1>

<?php
// Set shopping cart content
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty.</p>";
} else {
    
    echo "<ul>";
    $total_price = 0;
    foreach ($_SESSION['cart'] as $cart_item) {
        echo "<li>{$cart_item['name']} - \${$cart_item['price']}</li>";
        $total_price += $cart_item['price'];
        
    }
    echo "</ul>";

    echo "<p>Total Price: \$$total_price</p>";

    // Add a form to proceed to payment page
    echo '<form action="order.php" method="post">';
    echo '<input type="hidden" name="total_price" value="' . $total_price . '">';
    if(isset($_SESSION['email']) || !empty($_SESSION['email'])){

        echo '<button type="submit" name="proceed_to_payment">Proceed to Payment</button>';
    }
    else{
        echo '<p>Login to complete purchase</p>';
    }
    
    
    echo '</form>';
    echo '<br>';
    echo '<br>';
    echo '<form action="clear_cart.php" method="post">';
    echo '<button type="submit" name="clear_shopping_cart">Clear shopping cart</button>';
    echo '<br>';
    echo '</form>';
    
    
}

?>

<a href="https://localhost/index.php">Back to Store</a>

</body>

</html>
