<!-- Front end of login process -->

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
// If the user is already logged, it will be redirect to index page
if (isset($_SESSION['email']) || !empty($_SESSION['email'])) {
  $user = mysqli_real_escape_string($conn, $_SESSION['email']);
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Invalid page access");
  header("location: index.php");  

}
// Manage login errors
if (isset($_SESSION['FLAG_LOGIN']) && $_SESSION['FLAG_LOGIN'] === 'ERROR_LOGIN') {
  unset($_SESSION['FLAG_LOGIN']);
  echo '<script>alert("Error: invalid credentials")</script>'; 
}

if (isset($_SESSION['FLAG_LOGIN']) && $_SESSION['FLAG_LOGIN'] === 'BLOCK_LOGIN') {
  $wait = 180 - (time() - $_SESSION['BLOCK_TIME']);
  unset($_SESSION['FLAG_LOGIN']);
  echo '<script>alert("Too many attempts, wait '. $wait .' seconds")</script>'; 
}

if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_LEN') {
  unset($_SESSION['FLAG']);
  echo '<script>alert("Error: invalid credentials")</script>'; 
}
?>
<br>
<h1>Login</h1>

<form action="login_process.php?action=<?php echo ($action = 'login'); ?>" method="post" >
  <label for="email">E-mail:</label>
  <input type="text" id="email" name="email" required/><br><br>
  <label for="password">Password:</label>
  <input type="password" id="password" name="password" required/><br><br>
  <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
  <button type="submit" name="login">Login</button>
</form>



</body>
<br><br>
<a href="registration.php">Don't have an account? Register!</a><br><br>
<a href="recovery_account.php">Account recovery</a><br>

</html>
