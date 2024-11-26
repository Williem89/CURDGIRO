<?php
include 'koneksi.php';

if(isset($_POST['jenis_post'])) {
    $jenis_post = $_POST['jenis_post'];
    
    // Get the maximum tahapan for the selected jenis_post
    $query = "SELECT COALESCE(MAX(tahapan_post), 0) + 1 as next_tahapan 
              FROM post 
              WHERE jenis_post = '$jenis_post'";
              
    $result = mysqli_query($conn, $query);
    
    if($result) {
        $row = mysqli_fetch_assoc($result);
        echo $row['next_tahapan'];
    } else {
        echo "1"; // Default start value if no records exist
    }
}

mysqli_close($conn);
?>