<!--Front end password reset-->
<!DOCTYPE html>

<?php
include 'navbar.php';
include 'config.php';
// If the user is already logged or it didn't receive the email, it will be redirect to index page
if ((isset($_SESSION['email']) || !empty($_SESSION['email'])) || (!isset($_SESSION['email_rec']) || empty($_SESSION['email_rec']))) {
  $user = mysqli_real_escape_string($conn, $_SESSION['email']);
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Invalid page access");
  header("location: index.php");  

}
/*
if(isset($_SESSION['email_rec']) || !empty($_SESSION['email_rec'])){
  $entered_username = mysqli_real_escape_string($conn, $_SESSION['email_rec']);
  $sql = "SELECT TOKEN_HASH FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
            header("Location: registration.php");
            exit();
        }
        // setting parameters of parametric query
        $stmt->bind_param("s", $entered_username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Account exist
        if ($result->num_rows > 0){ 
          $row = $result->fetch_assoc();
          if($row['TOKEN_HASH'] === NULL){
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Double use token");
            header("location: index.php");
          }
        }
        else{
          header("location: index.php");
        }
      }
    
*/

if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_LEN') {
  unset($_SESSION['FLAG']);
  echo '<script>alert("Error: invalid credentials")</script>'; 
}    
if (isset($_SESSION['FLAG']) && $_SESSION['FLAG'] === 'ERROR_PW_S') {
  echo '<script>alert("Weak password, select a stronger one")</script>'; 
  unset($_SESSION['FLAG']);

}

?>

<h1 style="text-align:center;">REDIRECT FROM EMAIL LINK</h1>

<form action="pw_reset_process.php?token=<?php echo ($_GET['token']); ?>" method="post">
  
  <label for="password">New Password:</label>
  <input type="password" id="password" name="password" placeholder="Enter Password"required><br><br>

  <label for="password">Confirm New password:</label>
  <input type="password" id="c_password" name="c_password" placeholder="Enter Password"required><br><br>
  
  <button type="submit" name="reset">Reset</button>
</form>

<?php
include 'footer.php';
?>