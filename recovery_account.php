<!--
Front end page for account recovery phase.
Account recovery only works within the same session
-->
<!DOCTYPE html>

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
// Manage errors
if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'RECOVERY_ERR') {
  unset($_SESSION['FLAG']);
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Too many email");
  // Delay between mail preventing e-mail spam
  $wait = 300 - (time() - $_SESSION['mail_time']);
  echo '<script>alert("Error: too many mail, wait  '. $wait .' seconds ")</script>'; 
}

?>
<html>
<body style="text-align:center;">
<br>
<h1>Account Recovery</h1>
<form action="rec_account_process.php?action=<?php echo ($action = 'rec'); ?>" method="post">
  <label for="email_rec">E-mail:</label>
  <input type="text" id="email_rec" name="email_rec" required style="width: 150px;"><br><br>
  <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
  <button type="submit" name="recovery">Recovery</button>
</form>
</body>
</html>
