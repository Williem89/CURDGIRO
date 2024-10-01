<?php
session_start();
$rows = $_SESSION['report_rows'] ?? [];
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Giro Issued</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .subtotal {
            font-weight: bold;
            background-color: #e0f7fa;
        }
        .message {
            text-align: center;
            color: red;
            font-weight: bold;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Giro Issued</h1>

        <?php if (!empty($rows)): ?>
            <table>
                <thead>
                    <tr>
                        <th>No Giro</th>
                        <th>Nama Bank</th>
                        <th>Nomor Akun</th>
                        <th>Nama Akun</th>
                        <th>Tanggal Giro</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Nominal</th>
                        <th>Nama Penerima</th>
                        <th>Bank Penerima</th>
                        <th>Akun Penerima</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php if (isset($row['message'])): ?>
                            <tr>
                                <td colspan="11" class="message"><?php echo $row['message']; ?></td>
                            </tr>
                        <?php elseif (isset($row['subtotal'])): ?>
                            <tr class="subtotal">
                                <td colspan="6"><?php echo $row['subtotal']; ?></td>
                                <td><?php echo $row['value']; ?></td>
                                <td colspan="4"></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?php echo $row['nogiro']; ?></td>
                                <td><?php echo $row['namabank']; ?></td>
                                <td><?php echo $row['ac_number']; ?></td>
                                <td><?php echo $row['ac_name']; ?></td>
                                <td><?php echo $row['tanggal_giro']; ?></td>
                                <td><?php echo $row['tanggal_jatuh_tempo']; ?></td>
                                <td><?php echo $row['nominal']; ?></td>
                                <td><?php echo $row['nama_penerima']; ?></td>
                                <td><?php echo $row['bank_penerima']; ?></td>
                                <td><?php echo $row['ac_penerima']; ?></td>
                                <td><?php echo $row['keterangan']; ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="message">Tidak ada data ditemukan.</div>
        <?php endif; ?>
    </div>
</body>
</html>
