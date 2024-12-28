<?php
include 'koneksi.php';

// Fetch data from leasing table
$sql = "SELECT * FROM leasing ORDER BY id DESC";
$result = $conn->query($sql);

// Function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 2, ',', '.');
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pre-Financing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<div class="container mt-5">
    <h1 class="text-Left mb-4">Leasing</h1>
    <br>
    <a class="btn btn-info" href="create_leasing.php"><i class="bi bi-plus-circle"></i></a>
    <span>&nbsp;</span>
    <a class="btn btn-danger" href="dashboard.php"><i class="bi bi-backspace"></i></a>
    <br><br>
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr class="text-center">
                <th>Pembayaran Atas</th>
                <th>Item</th>
                <th>Total Harga</th>
                <th>Pokok Hutang</th>
                <th>Tenor</th>
                <th>Angsuran/Bulan</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // $items = json_decode($row['item'], true);
                    // $itemNames = array_column($items, 'name');
                    // $itemList = implode(', ', $itemNames);
                    
                    echo "<tr>";
                    echo "<td>{$row['pembayaran_atas']}</td>";
                    echo "<td>{$itemList}</td>";
                    echo "<td class='text-end'>" . formatCurrency($row['total_harga']) . "</td>";
                    echo "<td class='text-end'>" . formatCurrency($row['pokok_hutang']) . "</td>";
                    echo "<td class='text-center'>{$row['tenor']}</td>";
                    echo "<td class='text-end'>" . formatCurrency($row['angsuran_perbulan']) . "</td>";
                    echo "<td class='text-center'>
                            <a href='view_leasing.php?id={$row['id']}' class='btn btn-info btn-sm'><i class='bi bi-eye'></i></a>
                            <a href='edit_leasing.php?id={$row['id']}' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i></a>
                            <button onclick='deleteLease({$row['id']})' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>No data found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <script>
    function deleteLease(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete_leasing.php?id=${id}`;
            }
        });
    }
    </script>
</div>