<!DOCTYPE html>


<?php
    include 'config.php';

    // Sanitize user input to prevent SQL injection
    $entered_string = mysqli_real_escape_string($conn, $_GET['q']);
    if(strlen($entered_string) > 255){
        $_SESSION['FLAG'] = 'ERROR_LEN';
        header("Location: search_book.php");
        exit();
    }
    // Parametric query to search a book by title
    $sql = "SELECT * FROM books WHERE title = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // setting parameters of parametric query
        $stmt->bind_param("s", $entered_string);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0){ 
            
            while ($row = $result->fetch_assoc()) {
                echo "<style>
                .book {
                  border: 1px solid #ccc;
                  padding: 10px;
                  margin: 10px;
                  width: 400px;
                  text-align: center;
                  display: inline-block;
                }
              </style>";
                
                //Display the book searched
                $id = htmlspecialchars($row['id']);
                $title = htmlspecialchars($row['title']);
                $price = htmlspecialchars($row['price']);
                $cover = htmlspecialchars($row['cover']);
                $author = htmlspecialchars($row['author']);
                echo '<form method="post" action="index.php" class="book">';
                echo '<img src="'. $cover .'" alt="Book Cover" style="width: 30%;">';
                echo '<input type="hidden" name="item_id" value="' . $id . '">';
                echo '<input type="hidden" name="item_name" value="' . $title . '">';
                echo '<input type="hidden" name="item_price" value="' . $price . '">';
                echo '<input type="hidden" name="item_cover" value="' . $cover . '">';
                echo "<h3>{$title}</h3>";
                echo "<p>Author: {$author}</p>";

                echo "<p>Price: {$price}</p>";
                echo '<button type="submit" name="add_to_cart" >Add to Cart</button>';
                echo '</form>';
            }
            
        } 
        else {
            // Parametric query to search a book by author
            $sql = "SELECT * FROM books WHERE author = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // setting parameters of parametric query
                $stmt->bind_param("s", $entered_string);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0){ 
                    
                    while ($row = $result->fetch_assoc()) {
                        echo "<style>
                            .book {
                            border: 1px solid #ccc;
                            padding: 10px;
                            margin: 10px;
                            width: 400px;
                            text-align: center;
                            display: inline-block;
                            }
                        </style>";
                                //Display the book searched
                        $id = htmlspecialchars($row['id']);
                        $title = htmlspecialchars($row['title']);
                        $price = htmlspecialchars($row['price']);
                        $cover = htmlspecialchars($row['cover']);
                        $author = htmlspecialchars($row['author']);
                        echo '<form method="post" action="search_book.php" class="book">';
                        echo '<img src="'. $row['COVER'] .'" alt="Book Cover" style="width: 30%;">';
                        echo '<input type="hidden" name="item_id" value="' . $row['ID'] . '">';
                        echo '<input type="hidden" name="item_name" value="' . $row['TITLE'] . '">';
                        echo '<input type="hidden" name="item_price" value="' . $row['PRICE'] . '">';
                        echo '<input type="hidden" name="item_cover" value="' . $row['COVER'] . '">';
                        echo "<h3>{$row['TITLE']}</h3>";
                        echo "<p>Author: {$row['AUTHOR']}</p>";

                        echo "<p>Price: {$row['PRICE']}</p>";
                        echo '<button type="submit" name="add_to_cart" >Add to Cart</button>';
                        // Add more fields as needed
                        echo '</form>';
                    }
                }
            }
        }
    }
?>