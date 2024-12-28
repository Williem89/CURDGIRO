<?php
include 'koneksi.php';

$stmt = $conn->prepare("SELECT * FROM dhe");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List DHE</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 text-center">
    <div class="table-responsive">
        <table class="table table-bordered mx-auto">
            <thead class="thead-dark">
                <tr>
                    <th>No</th>
                    <th>Entitas</th>
                    <th>No Rekening</th>
                    <th>Tgl UM</th>
                    <th>Nominal UM</th>
                    <th>Tgl 70%</th>
                    <th>Nominal 70%</th>
                    <!-- <th>Nominal 30%</th>
                    <th>Jenis Fasilitas</th>
                    <th>Tgl Pengajuan</th>
                    <th>Rate</th>
                    <th>Nominal Max</th>
                    <th>Nominal Margin</th>
                    <th>Tgl Jth Tempo</th>
                    <th>Tanggal Penarikan Dana</th>
                    <th>Nominal Pencairan</th>
                    <th>Project</th>
                    <th>Keterangan</th> -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['Entitas']; ?></td>
                        <td><?php echo $row['norekening']; ?></td>
                        <td><?php echo $row['tgl_um']; ?></td>
                        <td><?php echo $row['nominal_um']; ?></td>
                        <td><?php echo $row['tgl_70']; ?></td>
                        <td><?php echo $row['nominal_70']; ?></td>
                        <!-- <td><?php echo $row['nominal_30%']; ?></td>
                        <td><?php echo $row['jenis_fasilitas']; ?></td>
                        <td><?php echo $row['tgl_pengajuan']; ?></td>
                        <td><?php echo $row['rate']; ?></td>
                        <td><?php echo $row['nominal_max']; ?></td>
                        <td><?php echo $row['nominal_margin']; ?></td>
                        <td><?php echo $row['tgl_jth_tempo']; ?></td>
                        <td><?php echo $row['tanggal_penarikan_dana']; ?></td>
                        <td><?php echo $row['nominal_pencairan']; ?></td>
                        <td><?php echo $row['project']; ?></td>
                        <td><?php echo $row['keterangan']; ?></td> -->
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>