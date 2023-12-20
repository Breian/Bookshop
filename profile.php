<!DOCTYPE html>
<?php
include 'config.php';
include 'navbar.php';

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Invalid page access");
    header("location: index.php");  
  
}
?>

<script>
    // AJAX to retrieve the PDF of the book from database
    function downloadPDF(title_user_id) {
    var req = new XMLHttpRequest();
    var title = title_user_id.split(",");
    req.open("GET", "download.php?q=" + encodeURIComponent(title_user_id), true);
    req.responseType = "arraybuffer";
    req.onload = function (event) {
        
        if(req.status === 200){

            var blob = new Blob([this.response], { type: 'application/pdf' });

                // Create a link element and trigger the download
            var link=document.createElement('a');
            link.href=window.URL.createObjectURL(blob);
            link.download=title[0];
            link.click();
        }
        else{
            window.location.href = "https://localhost/404.php";
        }
     };

    req.send();
 }
    

    
        
    
</script>

<h1>USER INFORMATIONS</h1>


<?php
// Fetch the purchases books from the database and allow to download it
$id = $_SESSION['user_id'];

$mail = $_SESSION['email'];
echo "<p> WELCOME $mail </p>";
echo "<p>YOUR BOOKS:</p>";
echo "<br>";
$sql = "SELECT * FROM purchases WHERE USER_ID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
    header("Location: registration.php");
    exit();
}
// setting parameters of parametric query
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$id_arr = array();
if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();
    foreach ($result as $r) {
        
        $id_book = $r['BOOK_ID'];
        
        if(!in_array($r['BOOK_ID'], $id_arr)){
            array_push($id_arr, $r['BOOK_ID']);
            $sql = "SELECT title, author, pdf FROM books WHERE ID = ?";
            
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Error", "SQL statement");
                header("Location: registration.php");
                exit();
            }
            // setting parameters of parametric query
            $stmt->bind_param("i", $id_book);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $title = $row["title"];
            $author = $row["author"];
            $pdf = $row['pdf'];
            echo '<br>';
            echo '<li>' . $title. ' - ' . $author. ' <button onclick=\'downloadPDF("' . $title. ',' . $id. '")\'>Download</button></li>';
            
            
        }
        
        
    }
}

?>

<a href="change_password.php">Change Password?</a><br>

<?php
include 'footer.php';
?>
