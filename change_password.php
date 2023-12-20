<!--Front end password change page-->
<?php
include 'config.php';
include 'navbar.php';
include 'csrf.php';
// Manage unauthorized access to the page
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Invalid page access");
  header("location: index.php", true, 401);  

}

if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_CHANGE') {
  $user = mysqli_real_escape_string($conn, $_SESSION['email']);
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Error", "Password mismatch");
  echo '<script>alert("Error: Change password aborted")</script>';  
  unset($_SESSION['FLAG']);

}
if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_PW') {
  echo '<script>alert("Error: Change password aborted")</script>';  
  unset($_SESSION['FLAG']);

}
if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_PW_S') {
  echo '<script>alert("Weak password, select a stronger one")</script>'; 
  unset($_SESSION['FLAG']);

}

?>

<!DOCTYPE html>
<br><br>
<form action="change_pw_process.php?action=<?php echo ($action = 'change'); ?>" method="post" style="text-align: center;">
  
  <label for="password">Old Password:</label>
  <input type="password" id="old_password" name="old_password" placeholder="Enter Old Password"required><br><br>

  <label for="password">New Password:</label>
  <input type="password" id="password" name="password" placeholder="Enter New Password"required><br>
  <sub style="font-size: 10px;"><pre>          At least 8 characters</pre></sub>

  <label for="password">Confirm new password:</label>
  <input type="password" id="c_password" name="c_password" placeholder="Enter New Password"required><br><br>
  <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
  <button type="submit" name="change_recovery">Change password</button>
</form>