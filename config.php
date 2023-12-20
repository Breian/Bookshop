<!--Back end utility to manage the database connection-->
<?php
if(basename($_SERVER['PHP_SELF']) === basename('config.php')){
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Invalid page access");
  header("location: index.php");
}
// If the user is already logged, it will be redirect to index page
if (isset($_SESSION['email']) || !empty($_SESSION['email'])) {
  $user = mysqli_real_escape_string($conn, $_SESSION['email']);
  logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), $user, "Warning", "Invalid page access");
  header("location: index.php");  

}
    session_start();
    $now = time();
    
    if (isset($_SESSION['email']) && isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
      // this session has worn out its welcome; kill it and start a brand new one
      //session_unset();
      //session_destroy();
      //session_start();
      header("location: logout.php");
      
    }
    
    // either new or old, it should live at most for another hour
    $_SESSION['discard_after'] = $now + 600; // 10 minutes session expires
    // Database connection configuration
    $servername = "localhost"; 
    $username = "root"; // MySQL username
    $password = ""; // MySQL password
    $dbname = "bookstore"; // Your database name

    // Create connection
    $conn = mysqli_connect($servername, $username,'',$dbname) or die('connection failed');
    //Resume session from cookie content
    if(!isset($_SESSION['email']) && isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])){
        $c = json_decode(stripslashes($_COOKIE['user_token']),true);
        $username = htmlspecialchars($c['email']);
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
            header("Location: registration.php");
            exit();
        }
        // setting parameters of parametric query
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $expire = $c['expire'];
        $control = hash('sha256', $expire . $row['SALT'] . $row['PSW_HASH']);
        if($control === $c['key']){
          session_regenerate_id(true);
          $_SESSION['cart'] = $c['cart'];
          $_SESSION['email'] = $c['email'];
          $_SESSION['user_id'] = $row['ID'];
          $_SESSION['FLAG_LOGIN'] = "SUCCESS_LOGIN";
    
        }
    }
  
  function logEvent($timestamp, $source, $agent, $username, $type, $description){
    
    $sql = "INSERT INTO logs(TIMESTAMP, SOURCE, AGENT, USERNAME, TYPE, DESCRIPTION) VALUES (?,?,?,?,?,?)";
    $stmt = $GLOBALS['conn']->prepare($sql);

        if (!$stmt) {
            logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
            header("Location: login.php");
            exit();
        }
        // setting parameters of parametric query
        $stmt->bind_param('ssssss', $timestamp, $source, $agent, $username, $type, $description);
        $stmt->execute();

  }
  function getUserIP(){

    // Get real visitor IP behind CloudFlare network    
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])){
      $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
      $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }    
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['REMOTE_PORT'];
    if(filter_var($client, FILTER_VALIDATE_IP)){        
      $ip = $client;

    }    
    else if(filter_var($forward, FILTER_VALIDATE_IP)){        
      $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;

}

    
    

?>
