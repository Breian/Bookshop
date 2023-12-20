<!-- login.php -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
</head>
<body style="text-align:center;">

<?php
include 'navbar.php';
include 'config.php';
include 'csrf.php';
?>
<br>
<h1>Registration</h1>
<?php
  // If the user is already logged, it will be redirect to index page
  if (isset($_SESSION['email']) || !empty($_SESSION['email'])) {
    $user = mysqli_real_escape_string($conn, $_SESSION['email']);
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Invalid page access");
    header("location: index.php");  
  }
  // Check errors
  if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'REG_ERR') {
    unset($_SESSION['FLAG']);
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Too frequent email");
    $wait = 20 - (time() - $_SESSION['mail_time']);
    echo '<script>alert("Error: too many mail, wait  '. $wait .' seconds ")</script>'; 
  }
  
  
  if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_PW') {
    unset($_SESSION['FLAG']);
    echo '<script>alert("Error: Passwords not matching")</script>'; 
  }

  
  if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_LEN') {
    unset($_SESSION['FLAG']);
    echo '<script>alert("Error: invalid credentials")</script>'; 

  }

  if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_PW_S') {
    echo '<script>alert("Weak password, select a stronger one")</script>'; 
    unset($_SESSION['FLAG']);
  
  }
?>
<form action="reg_process.php?action=<?php echo ($action = 'reg'); ?>" method="post">

  <label for="email">E-mail:</label>
  
  <input type="text" id="email" name="email" placeholder="Enter Email" required/><br><br>
  
  <label for="password">Password:</label>
  
  <input type="password" id="password" name="password" placeholder="Enter Password"required/><br>
  <sub style="font-size: 10px;"><pre>             At least 8 characters</pre></sub>
  

  <label for="password">Confirm password:</label>
  <input type="password" id="c_password" name="c_password" placeholder="Enter Password"required/><br><br>
  <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
  <button type="submit" name="login">Register</button>
  
</form>

</body>
</html>
