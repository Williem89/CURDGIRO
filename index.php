<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama</title>
</head>
<body>
    <h1>Selamat Datang di Aplikasi Giro</h1>
    <p>Silakan pilih salah satu opsi di bawah ini:</p>
    <ul>
        <li><a href="CreateGiro.php">Input Data Giro</a></li>
        <li><a href="TulisGiro.php">Tulis Giro</a></li>
        <!-- Tambahkan link lain jika perlu -->
    </ul>

    <h2>Statistik Giro</h2>
    <p>
        Jumlah Giro Unused: 
        <a href="UnusedGiroList.php"><?php echo $unused_count; ?></a>
    </p>
    <p>
        Jumlah Giro Used: 
        <a href="UsedGiroList.php"><?php echo $used_count; ?></a>
    </p>
    <p>
        Total Nominal Giro Issued: 
        <a href="IssuedGiroList.php"><?php echo number_format($total_Issued_nominal); ?></a>
    </p>
    <p>
        Total Nominal Giro Used: <?php echo number_format($total_used_nominal); ?>
    </p>
    <p>
        Total Nominal Giro Issued yang Jatuh Tempo dalam 3 Hari: 
        <a href="IssuedGiroList.php"><?php echo number_format($total_issued_due_soon_nominal); ?></a>
    </p>
</body>
</html>
