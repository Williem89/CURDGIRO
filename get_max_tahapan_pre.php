<?php
include 'koneksi.php';

if(isset($_POST['jenis_prepost'])) {
    $jenis_prepost = $_POST['jenis_prepost'];
    
    // Get the maximum tahapan for the selected jenis_prepost
    $query = "SELECT COALESCE(MAX(tahapan), 0) + 1 as next_tahapan 
              FROM pre 
              WHERE jenis_prepost = '$jenis_prepost'";
              
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