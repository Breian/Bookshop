<!-- login_process.php -->

<?php
// Import PHPMailer classes into the global namespace 
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

if (isset($_SESSION['email']) || !empty($_SESSION['email'])) {
    $user = mysqli_real_escape_string($conn, $_SESSION['email']);
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Invalid page access");
    header("location: index.php");  
  
}





// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if($_REQUEST['action'] === 'rec'){
        if(!csrf_verifyNonce($_REQUEST['action'], $_POST['nonce'])){
            if(isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                $user = mysqli_real_escape_string($conn, $_SESSION['email']);
            }
            else{
                $user = "Unknown";
            }
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Fatal", "CSRF detected");
            throw new Exception("Error: CSRF detected");
        }
            
        }

    // Check if username and password are set and not empty
    if (isset($_POST['email_rec']) && !empty($_POST['email_rec']) 
        && preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email_rec'])){
    if(isset($_SESSION['mail_time']) && !empty($_SESSION['mail_time'])){
        if(time() - $_SESSION['mail_time']  < 300){
            $_SESSION['FLAG'] = "RECOVERY_ERR";
            header("location: recovery_account.php");
            exit();

        }
    }
    
    
        // Sanitize user input to prevent SQL injection
        $entered_username = mysqli_real_escape_string($conn, $_POST['email_rec']);
        
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
            header("Location: recovery_account.php");
            exit();
        }
        // setting parameters of parametric query
        $stmt->bind_param("s", $entered_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        


        $name = explode("@", $entered_username);
       
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
        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "tls";                       
        //Set TCP port to connect to
        $mail->Port = 587;                    
        $mail->From = "bookshop727@gmail.com";
        $mail->FromName = "Bookshop";
        $mail->addAddress($entered_username, $name[0]);
        $mail->isHTML(true);
        $mail->Subject = "Bookshop recovery account";
        if ($result->num_rows > 0) {
            $salt = bin2hex(random_bytes(32));
            $token = bin2hex(random_bytes(16));
            
            $token_hash = hash('sha256', $salt . $token);
            $expire = date('Y-m-d H:i:s', time() + 300);
            $sql = "UPDATE users SET TOKEN_HASH = ?, TOKEN_SALT = ?, TOKEN_EXPIRE = ? WHERE EMAIL = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
                header("Location: recovery_account.php");
                exit();
            }
            $stmt->bind_param('ssss', $token_hash, $salt, $expire, $entered_username);
            $stmt->execute();
            
            //error_reporting(E_ERROR | E_PARSE);
            $mail->Body = "<p>Here is the link to recover your account: <a href='https://localhost/password_reset.php?token=$token' >https://localhost/password_reset.php?token=$token</a></p>";
            
            //$mail->AltBody = "This is the plain text version of the email content";
            if(!$mail->send()){
                echo "<script>alert('Error');</script>";
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "Mail not sent");
            }
            else{

                $_SESSION['mail_time'] = time();
                $_SESSION['email_rec'] = $entered_username;
                $_SESSION['FLAG'] = "RECOVERY";
                header("location: index.php");
            }
        }
        else{
            $mail->Body = "<p>Error: this email is not associated to any account</p>";
            
            //$mail->AltBody = "This is the plain text version of the email content";
            if(!$mail->send()){
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "Mail not sent");
                echo "<script>alert('Error');</script>";
            }
            else{
                $_SESSION['mail_time'] = time();
                $_SESSION['FLAG'] = "RECOVERY";
                header("location: index.php", true, 302);
            }

        }
        
    }
        
}

// Close connection
$conn->close();
?>