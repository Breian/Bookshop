<!--Navbar-->
<!DOCTYPE html>
<div class="navbar">
        <a href="https://localhost/index.php" style="font-family:Libre Baskerville;">Home</a>
        <a href="https://localhost/search_book.php" style="font-family:Libre Baskerville;">Search</a>
        <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
        <div class="login-container">
        <style>

.navbar {
overflow: hidden;
background-color: #333;
}
.navbar a {
float: left;
display: block;
color: #f2f2f2;
text-align: center;
padding: 14px 16px;
text-decoration: none;
font-size: 17px;
}
.navbar a:hover {
background: #ddd;
color: black;
}
.login-container {
float: right;
}
.navbar .login-container button {
float: right;
padding: 14px 16px;
background-color: #4CAF50;
color: white;
border: none;
cursor: pointer;
font-size: 17px;
}
.navbar .login-container button:hover {
background: #ddd;
color: black;
}

</style>
<?php
    // Change buttons displayed based on session value (login)
    if (isset($_SESSION['FLAG_LOGIN']) && $_SESSION['FLAG_LOGIN'] === 'SUCCESS_LOGIN') {
        echo '<a href="https://localhost/logout.php" style="font-family:Libre Baskerville;">Logout</a>'; 
        echo '<a href="https://localhost/profile.php"> <i class="fa fa-user"></i></a>';


    }
    else{
        unset($_SESSION['FLAG_LOGIN']);
        echo '<a href="https://localhost/login.php" style="font-family:Libre Baskerville;">Login</a>'; 
    }
    
?>

        <a href="https://localhost/shopping-cart.php"> <i  class="fa fa-shopping-cart" aria-hidden="true"></i></a>
        </div>
    </div>