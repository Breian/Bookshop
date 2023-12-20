<!-- reg_process.php -->



<?php
include 'config.php';
include 'csrf.php';

//set_include_path(get_include_path().PATH_SEPARATOR.'C:/xampp/php/PHPMailer-master/PHPMailer-master');
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
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") { 
    if($_REQUEST['action'] === 'reg'){
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
    if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['c_password']) && !empty($_POST['email']) 
        && !empty($_POST['password'] && !empty($_POST['c_password'])) 
        && preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
        && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['password']) && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['c_password'])){
        // Sanitize user input to prevent SQL injection
        $entered_username = mysqli_real_escape_string($conn, $_POST['email']);
        $entered_password = mysqli_real_escape_string($conn, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['c_password']);

        if(strlen($entered_username) < 4 || strlen($entered_username) > 255){
            $_SESSION['FLAG'] = 'ERROR_LEN';
            header("Location: registration.php");
            exit();
        }

        if((strlen($entered_password) < 8 || strlen($entered_password) > 60000) && 
        (strlen($confirm_password) < 8 || strlen($confirm_password) > 60000)){
            $_SESSION['FLAG'] = 'ERROR_LEN';
            header("Location: registration.php");
            exit();
        }
        
        if($entered_password != $confirm_password){
            $_SESSION['FLAG'] = 'ERROR_PW';

            header("Location: registration.php");
            exit();
        }


        if(isset($_SESSION['mail_time']) && !empty($_SESSION['mail_time'])){
            if(time() - $_SESSION['mail_time']  < 20){
                $_SESSION['FLAG'] = "REG_ERR";
                header("location: registration.php");
                exit();
    
            }
        }
        $zxcvbn = new Zxcvbn();
        $userData = [
            "$entered_username"
            
          ];
        $strength = $zxcvbn->passwordStrength($entered_password, $userData);
        if($strength['score'] < 3){
            $_SESSION['FLAG'] = 'ERROR_PW_S';
           
            header("Location: registration.php");
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
        //Sending mail
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
        $mail->Subject = "Bookshop sign in";

        // Account already exist
        if ($result->num_rows > 0){ 
            $mail->Body = "<p>Account already registered!</p>";
            $_SESSION['FLAG'] = "REG";
                //send mail and set time
                if(!$mail->send()){
                    echo "<script>alert('Error');</script>";
                }
                else{
                    $_SESSION['mail_time'] = time();
                    $_SESSION['FLAG'] = "REG";
                    header("location: index.php", true, 302);
                }
            
            
            
            
        } 
        else {
            // Username and password are correct, set session variable saving them in database
            $salt_r = random_bytes(64);
            $salt = bin2hex($salt_r);
            $hash_pw = hash('sha256', $salt . $entered_password);
            echo $salt;
            //$sql = "INSERT INTO users(EMAIL, PSW_HASH, SALT) VALUES ('$entered_username','$hash_pw','$salt')";
            $sql = "INSERT INTO users(EMAIL, PSW_HASH, SALT) VALUES (?,?,?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt){
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
                header("Location: registration.php");
                exit();
            }
            // setting parameters of parametric query
            $stmt->bind_param("s", $entered_username, $hash_pw, $salt);
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $conn->query($sql);
            $_SESSION['FLAG'] = 'REG';
            $_SESSION['MAIL'] = $entered_username;

            
            
            $mail->Body = "<p>Registration succeed!</p>";
            
            //send mail and set time
            if(!$mail->send()){
                echo "<script>alert('Error');</script>";
            }
            else{
                $_SESSION['mail_time'] = time();
                $_SESSION['FLAG'] = "REG";
                
                header("location: index.php", true, 302);
            }
        }
        
            session_regenerate_id(true);
            header("Location: index.php", true, 302); // Redirect to dashboard 
            exit();
        }
        $_SESSION['FLAG'] = 'ERROR_LEN';
        header("Location: registration.php");
    } 
    else {
        // if the operation is not POST, remain in reg page
        header("Location: registration.php", true, );
        exit();
    }


// Close connection
$conn->close();
?>