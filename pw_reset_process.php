<!-- reg_process.php -->



<?php
include 'config.php';
use ZxcvbnPhp\Zxcvbn;
require_once "vendor/autoload.php";

// If the user is already logged, it will be redirect to index page
if ((isset($_SESSION['email']) || !empty($_SESSION['email'])) || (!isset($_SESSION['email_rec']) || empty($_SESSION['email_rec']))) {
    $user = mysqli_real_escape_string($conn, $_SESSION['email']);
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Invalid page access");
    header("location: index.php");  
  
  }

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if username and password are set and not empty
    if (isset($_POST['password']) && isset($_POST['c_password'])  
        && !empty($_POST['password'] && !empty($_POST['c_password'])) 
        && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['password']) && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['c_password'])){
        // Sanitize user input to prevent SQL injection
        $entered_username = mysqli_real_escape_string($conn, $_SESSION['email_rec']);
        $entered_password = mysqli_real_escape_string($conn, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['c_password']);

        if((strlen($entered_password) < 8 || strlen($entered_password) > 60000) && 
        (strlen($confirm_password) < 8 || strlen($confirm_password) > 60000)){
            $_SESSION['FLAG'] = 'ERROR_LEN';
            header("Location: password_reset.php");
            exit();
        }
        if($entered_password !== $confirm_password){
            $_SESSION['FLAG'] = 'ERROR_PW';
           
            header("Location: password_reset.php");
            exit();
        }
            $token = $_GET['token'];
            // Sanitize user input to prevent SQL injection
            if($token === ''){
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Missing token");
                header("Location: index.php");
            }
            $token = mysqli_real_escape_string($conn, $_GET['token']);
            $zxcvbn = new Zxcvbn();
            $userData = [
            "$entered_username"
            
            ];
            $strength = $zxcvbn->passwordStrength($entered_password, $userData);
            if($strength['score'] < 3){
                $_SESSION['FLAG'] = 'ERROR_PW_S';
            
                header("Location: password_reset.php");
                exit();
            } 
        // Query to check if the username match in the database
        $sql = "SELECT * FROM users WHERE email = ?";
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
            $salt_r = random_bytes(64);
            $salt = bin2hex($salt_r);
            $hash_pw = hash('sha256', $salt . $entered_password);
            $salt_db = $row['TOKEN_SALT'];
            $expire = $row['TOKEN_EXPIRE'];
            $token_hash = hash('sha256', $salt_db . $token);

            $d1 = strtotime($expire);
            $d2 = strtotime(date('Y-m-d H:i:s', time()));
            
            if($token_hash !== $row['TOKEN_HASH']){
                // Not valid token
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Fatal", "Invalid URL token");
                header("location: index.php");
                exit();
            }
            if($d2 > $d1){
                // Token expired
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Fatal", "Token expired");
                $sql = "UPDATE users SET TOKEN_HASH=?, TOKEN_SALT=?, TOKEN_EXPIRE=?  WHERE EMAIL = ?";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
                    header("Location: registration.php");
                    exit();
                }
                // setting parameters of parametric query
                $v1 = NULL;
                $v2 = NULL;
                $v3 = NULL;
                $stmt->bind_param('ssss', $v1, $v2, $v3, $entered_username);

                $stmt->execute();
                $result = $stmt->get_result();
                unset($_SESSION['email_rec']);
                header("location: index.php");
                exit();
            }
            // Token not expired and correct, proceed with updating password and other fields
            $sql = "UPDATE users SET  PSW_HASH =?, SALT =?, TOKEN_HASH=?, TOKEN_SALT=?, TOKEN_EXPIRE=?  WHERE EMAIL = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
                header("Location: registration.php");
                exit();
            }
            // setting parameters of parametric query
            $v1 = NULL;
            $v2 = NULL;
            $v3 = NULL;
            $stmt->bind_param('ssssss', $hash_pw, $salt, $v1, $v2, $v3, $entered_username);

            $stmt->execute();
            $result = $stmt->get_result();
            unset($_SESSION['email_rec']);
            
            
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Trace", "Account recovered");
            header("Location: index.php", true, 302); // Redirect to dashboard
            
            
        } else {
            // Account not exist
            $_SESSION['FLAG'] = 'ERROR';
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Account not found");
            header("Location: password_reset.php"); // Redirect to dashboard 
            exit();
        }
    } else {
        
        header("Location: registration.php");
        exit();
    }
} else {
    // Redirect back to login page if form is not submitted
    header("Location: login.php");
    exit();
}

// Close connection
$conn->close();
?>