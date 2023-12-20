<!--Main site page-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <title>Home</title>
    
</head>
<body>


<?php
include 'config.php';
include 'navbar.php';
?>

  
    <h1 style="text-align:center; font-family:Libre Baskerville; font-size: 40px;" >Welcome to Bookshop</h1>
    <hr style="border: 2px solid black;">

    <style>
    /* Basic styling for demonstration purposes */
    .book {
      border: 3px solid;
      border-color: black;
      padding: 20px;
      margin: 52px;
      width: 350px;
      display: inline-block;
      text-align: center;

    }
  </style>
</head>

<?php




// Add items to the cart when clicked
if (isset($_POST['add_to_cart'])) {

    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];
    

    // Store item in the cart array
    $_SESSION['cart'][] = [
        'id' => $item_id,
        'name' => $item_name,
        'price' => $item_price,
        
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


<?php
    // Manage registration and recovery alert
    if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'REG') {
      echo '<script>alert("Check your email for registration status!")</script>';  
      unset($_SESSION['FLAG']);

    }
    if(isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'RECOVERY'){
      echo '<script>alert("Recovery mail sent!")</script>';  
      unset($_SESSION['FLAG']);
    }
    
?>

<body>

<div class="books-container">
<?php

// Query to retrieve books
$sql = "SELECT id, cover, title, author, price FROM books"; // table is named 'books'
$result = $conn->query($sql);

  if ($result->num_rows > 0) {
      // Output data of each row as HTML elements
      // Show the books retrieved
      while ($row = $result->fetch_assoc()) {
          $id = htmlspecialchars($row['id']);
          $title = htmlspecialchars($row['title']);
          $price = htmlspecialchars($row['price']);
          $cover = htmlspecialchars($row['cover']);
          $author = htmlspecialchars($row['author']);
          echo '<form method="post" action="index.php" class="book">';
          echo '<img src="data:image/jpg;base64,'.base64_encode($row['cover']).'" alt="Book Cover" style="width: 30%;">';
          echo '<input type="hidden" name="item_id" value="' . $id . '">';
          echo '<input type="hidden" name="item_name" value="' . $title . '">';
          echo '<input type="hidden" name="item_price" value="' . $price . '">';
          echo '<input type="hidden" name="item_cover" value="' . $cover . '">';
          echo "<h3>{$title}</h3>";
          echo "<p>Author: {$author}</p>";
          
          echo "<p>Price: {$price}</p>";
          echo '<button type="submit" name="add_to_cart" >Add to Cart</button>';
          echo '</form>';
      }
  } else {
      echo "0 results";
  }

  $conn->close();
  ?>
</div>



<?php
include 'footer.php';
?>

</body>
</html>