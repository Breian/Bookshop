<!DOCTYPE html>
<?php
    include 'config.php';
    if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
        logEvent(date('Y-m-d H:i:s', time()), basename($_SERVER['PHP_SELF']), getUserIP(), "Unknown", "Warning", "Invalid page access");
        header("location: index.php");  
      
    }
    // Sanitize user input to prevent SQL injection
    $entered_string = mysqli_real_escape_string($conn, $_GET['q']);
    $params = explode(",", $entered_string);

    // Parametric query to search a book by title
    $sql = "SELECT id, pdf FROM books WHERE title = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // setting parameters of parametric query
        $stmt->bind_param("s", $params[0]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        
        if ($result->num_rows > 0){ 
            $pdf = $row['pdf'];
            $id = $row['id'];
            $sql = "SELECT * FROM purchases WHERE USER_ID = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // setting parameters of parametric query
                $stmt->bind_param("s", $params[1]);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {

                    $row = $result->fetch_assoc();
                    // Verifying that the ID provided is equal to the user ID
                    if($row['USER_ID'] === $_SESSION['user_id']){
                        
                    
                        foreach ($result as $r) {
                            
                            $id_book = $r['BOOK_ID'];
                            
                            if($id_book === $id){
                                
                                echo $pdf;
                                exit;
                            }
                         
                        }
                        http_response_code(404);
                        
                        exit;
                    }
                    http_response_code(404);
                }
                http_response_code(404); 
            }
            http_response_code(404);
        
        }
        http_response_code(404);
    }
?>