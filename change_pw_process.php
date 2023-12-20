<!--Back end of the change password process -->
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\Exception;
use League\OAuth2\Client\Provider\Google;
use ZxcvbnPhp\Zxcvbn;
require_once "vendor/autoload.php";
// Include library files 
require 'C:/xampp/htdocs/BookShop/PHPMailer-master/PHPMailer-master/src/Exception.php'; 
require 'C:/xampp/htdocs/BookShop/PHPMailer-master/PHPMailer-master/src/PHPMailer.php'; 
require 'C:/xampp/htdocs/BookShop/PHPMailer-master/PHPMailer-master/src/SMTP.php';
include 'config.php';
include 'csrf.php';


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if($_REQUEST['action'] === 'change'){
        if(!csrf_verifyNonce($_REQUEST['action'], $_POST['nonce'])){
            throw new Exception("Error: CSRF detected");
        }
    
    }
    // Check if username and password are set and not empty
    if (isset($_POST['password']) && isset($_POST['c_password']) && isset($_POST['old_password']) 
        && !empty($_POST['password'] && !empty($_POST['c_password']))  && !empty($_POST['old_password'])
        && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['password']) 
        && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['c_password'])
        && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['old_password'])){
        
        // Sanitize user input to prevent SQL injection
        $entered_username = $_SESSION['email'];
        $entered_password = mysqli_real_escape_string($conn, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['c_password']);
        $old_password = mysqli_real_escape_string($conn, $_POST['old_password']);
        
        if($entered_password != $confirm_password){
            $_SESSION['FLAG'] = 'ERROR_PW';
            
            //header("Location: change_password.php");
            exit();
        }
        // Checking password strength
        if(strlen($entered_password) < 8 || strlen($entered_password) > 60000){
            $_SESSION['FLAG'] = 'ERROR_PW';
            header("Location: change_password.php");
            exit();
        }
        $zxcvbn = new Zxcvbn();
        $userData = [
            "$entered_username"
            
          ];
        $strength = $zxcvbn->passwordStrength($entered_password, $userData);
        if($strength['score'] < 3){
            $_SESSION['FLAG'] = 'ERROR_PW_S';
           
            header("Location: change_password.php");
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
            $saved_hash = $row['PSW_HASH'];
            $salt = $row['SALT'];
            $hash_pw = hash('sha256', $salt . $old_password);
            // Check if the calculated hash is equal to the stored one
            if($saved_hash === $hash_pw){
                // Updating password
                $salt_r = random_bytes(64);
                $salt = bin2hex($salt_r);
                $hash_pw = hash('sha256', $salt . $entered_password);
                $sql = "UPDATE users SET  PSW_HASH =?, SALT = ? WHERE EMAIL = ?";
                
                $stmt = $conn->prepare($sql);
        
                if (!$stmt) {
                    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
                    header("Location: registration.php");
                    exit();
                }
                // setting parameters of parametric query
                $stmt->bind_param("s", $hash_pw, $salt, $entered_username);
                $stmt->execute();
                $result = $stmt->get_result();
                $result = $conn->query($sql);
                
                $name = explode("@", $entered_username);
                 
                // Sending mail to user 
                $mail = new PHPMailer(true);
                //Enable SMTP debugging.
                $mail->SMTPDebug = 3;                           
                //Set PHPMailer to use SMTP.
                $mail->isSMTP();        
                //Set SMTP host name                      
                $mail->Host = "smtp.gmail.com";
                //Set this to true if SMTP host requires authentication to send email
                $mail->SMTPAuth = true;                      
                //Provide username and password
                $mail->Username = "bookshop727@gmail.com";             
                $mail->Password = "ihdv vycf yepb updw";                       
                //TLS encryption 
                $mail->SMTPSecure = "tls";                       
                //Set TCP port to connect to and the other parameters
                $mail->Port = 587;                    
                $mail->From = "bookshop727@gmail.com";
                $mail->FromName = "Bookshop";
                $mail->addAddress($entered_username, $name[0]);
                $mail->isHTML(true);
                $mail->Subject = "Bookshop password change";
                
                $mail->Body = "<p>Your password has been changed successfully!<br> If you don't change the password, here is the link to recover your account: <a href='http://localhost/Bookshop/password_reset.php' >http://localhost/Bookshop/password_reset.php</a></p>";
                
                if(!$mail->send()){
                
                }
                // Regenerate session_id to prevent session fixation attacks
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Trace", "Password change");
                session_regenerate_id(true);
                header("location: index.php", true, 302);
            }
            // In case of error, redirect to change_password page with an alert message
            else{
                $_SESSION['FLAG'] = "ERROR_CHANGE"; 
                header("Location: change_password.php"); 
            }
            
        } else {
            $_SESSION['FLAG'] = "ERROR_CHANGE"; 
            header("Location: change_password.php");
            exit();
        }
    } else {
        $_SESSION['FLAG'] = "ERROR_CHANGE";
        header("Location: change_password.php");
        exit();
    }
} else {
    // Redirect back to login page if form is not submitted
    header("Location: index.php");
    exit();
}


// Close connection
$conn->close();
?>