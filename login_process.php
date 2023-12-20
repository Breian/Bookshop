<!-- Back end of login operation -->

<?php
include 'config.php';
include 'csrf.php';
// If the user is already logged, it will be redirect to index page
if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
    $entered_username = mysqli_real_escape_string($conn, $_SESSION['email']);
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Warning", "Invalid page access");
    header("location: index.php");  
  
}




// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check CSRF attack
    if($_REQUEST['action'] === 'login'){
        if(!csrf_verifyNonce($_REQUEST['action'], $_POST['nonce'])){
            $user = "Unknown";
            if(isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                $user = mysqli_real_escape_string($conn, $_SESSION['email']);
            }
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Fatal", "CSRF detected");
            throw new Exception("Error: CSRF detected");
            
        }
    
    }
    $bad_login_limit = 2;
    $lockout_time = 180;
    if(!isset($_SESSION['count_failed_login']) && !isset($_SESSION['first_failed_login'])){
        $_SESSION['count_failed_login'] = 0;
        $_SESSION['first_failed_login'] = 0;
        
        
    }

    if($_SESSION['count_failed_login'] >= $bad_login_limit && (time() - $_SESSION['first_failed_login']) < $lockout_time){
        logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Fatal", "Brute force detected");
            $_SESSION['FLAG_LOGIN'] = "BLOCK_LOGIN";
            
            
            header("Location: login.php");
            exit();
    }
    $_SESSION['BLOCK_TIME'] = time();
    // Check if username and password are set and not empty
    if (isset($_POST['email']) && isset($_POST['password']) && !empty($_POST['email']) 
        && !empty($_POST['password']) 
        && preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
        && preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['password'])){
        // Sanitize user input to prevent SQL injection
        $entered_username = mysqli_real_escape_string($conn, $_POST['email']);
        $entered_password = mysqli_real_escape_string($conn, $_POST['password']);
        
        
        
        
        // Check the length of username and password
        if(strlen($entered_username) < 4 || strlen($entered_username) > 255){
            if((time() - $_SESSION['first_failed_login']) > $lockout_time){
                $_SESSION['first_failed_login'] = time();
                $_SESSION['count_failed_login'] = 1;
               
            }
            else{
                $_SESSION['count_failed_login'] ++;
            }
            $_SESSION['FLAG'] = 'ERROR_LEN';
            header("Location: login.php");
            exit();
        }

        if((strlen($entered_password) < 8 || strlen($entered_password) > 60000) && 
        (strlen($confirm_password) < 8 || strlen($confirm_password) > 60000)){
            if((time() - $_SESSION['first_failed_login']) > $lockout_time){
                $_SESSION['first_failed_login'] = time();
                $_SESSION['count_failed_login'] = 1;
               
            }
            else{
                $_SESSION['count_failed_login'] ++;
            }
            $_SESSION['FLAG'] = 'ERROR_LEN';
            header("Location: login.php");
            exit();
        }
        $_SESSION['BLOCK_TIME'] = time();
        
        // Query to check if the username is correct
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
            header("Location: login.php");
            exit();
        }
        // setting parameters of parametric query
        $stmt->bind_param("s", $entered_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // User found
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            //echo "ID: " . $row["ID"] . " - Email: " . $row["EMAIL"] . " - Password: " . $row["PSW_HASH"] . " - Salt: " . $row["SALT"] . "<br>";
            
            $hash_pw = hash('sha256', $row["SALT"] . $entered_password);
            if($hash_pw === $row["PSW_HASH"]){
                // Username and password are correct, set session variables
                $_SESSION['email'] = $entered_username;
                $_SESSION['user_id'] = $row['ID'];
                $_SESSION['FLAG_LOGIN'] = "SUCCESS_LOGIN";

                //Cookie fields preparation
                $expire = time() + 180;
                
                $_SESSION['nonce'] = $_POST['nonce'];
                $token = array('id'=> $row['ID'],'email'=> $entered_username, 'expire'=> $expire,'cart'=> $_SESSION['cart'], 'key'=> hash('sha256', $expire . $row['SALT'] . $hash_pw));
                
                // Setting cookie
                setcookie('user_token', json_encode($token), $expire,'', '', true, true);
                session_regenerate_id(true);
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Success", "Successful login");
                header("Location: index.php", true, 302); // Redirect to dashboard or any authenticated page
                exit();
            }
            else{
                if((time() - $_SESSION['first_failed_login']) > $lockout_time){
                    $_SESSION['first_failed_login'] = time();
                    $_SESSION['count_failed_login'] = 1;
                   
                }
                else{
                    $_SESSION['count_failed_login'] ++;
                }
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Error", "Credentials error");
                $_SESSION['FLAG_LOGIN'] = "ERROR_LOGIN";
                header("Location: login.php");
            }

        } else {
            if((time() - $_SESSION['first_failed_login']) > $lockout_time){
                $_SESSION['first_failed_login'] = time();
                $_SESSION['count_failed_login'] = 1;
                
            }
            else{
                $_SESSION['count_failed_login'] ++;
            }
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $entered_username, "Error", "Inexistent user");
            // Invalid credentials, redirect back to login page with an error message
            $_SESSION['FLAG_LOGIN'] = "ERROR_LOGIN";
            header("Location: login.php");
            exit();
        }
    } else {
        if((time() - $_SESSION['first_failed_login']) > $lockout_time){
            $_SESSION['first_failed_login'] = time();
            $_SESSION['count_failed_login'] = 1;
            
        }
        else{
            $_SESSION['count_failed_login'] ++;
        }
        
        logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "Malformed input");
            // Invalid credentials, redirect back to login page with an error message
        $_SESSION['FLAG_LOGIN'] = "ERROR_LOGIN";
        header("Location: login.php");
        exit();
    }
} else {
    // Redirect back to login page if form is not submitted
    // UNREACHABLE
    header("Location: login.php");
    exit();
}

 //Close connection

$conn->close();
?>
