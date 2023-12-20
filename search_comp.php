<!DOCTYPE html>
<div id="searchContainer" style="margin-top: 5%; text-align: center;">
  <input type="text" id="searchInput" placeholder="Enter book title or author" style="width: 300px;">
  <button onclick="searchBooks()" style="text-align:center; width: 100px">Search</button>
</div>
<div id="bookList"></div>

<script>
  // AJAX to send the name of the book
  function searchBooks() {
    var searchValue = document.getElementById("searchInput").value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState === 4 && this.status === 200) {
        var bookList = document.getElementById("bookList");
        bookList.innerHTML = this.responseText;
        
      }
    };
    xhttp.open("GET", "search_process.php?q=" + encodeURIComponent(searchValue), true);
    xhttp.send();
    
  }
</script>