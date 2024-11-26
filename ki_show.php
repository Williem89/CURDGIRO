<?php
// Koneksi ke database
include 'koneksi.php';

// Ambil data dari tabel bnl
$sql_leasing = "SELECT * FROM bnl where jenis_bnl = 'Leasing'";
$result = $conn->query($sql_leasing);
if (!$result) {
    die("Query error: " . $conn->error);
}
$leasingData = $result->fetch_all(MYSQLI_ASSOC);
$result->data_seek(0);

$sql_bank = "SELECT * FROM bnl where jenis_bnl = 'Bank'";
$result2 = $conn->query($sql_bank);
if (!$result2) {
    die("Query error: " . $conn->error);
}
$bankData = $result2->fetch_all(MYSQLI_ASSOC);
$result2->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bunga Leasing & Bank</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }
    </style>
</head>

<body>
    <h1 style="text-align: center;">Bunga Leasing & Bank</h1>
    <br>

    <table style="width: 1150px; margin: 0 auto;">
        <p style="text-align: center;"><strong>Leasing</strong> </P>
        <thead>
            <tr style="text-align:center;">
                <th style="width: 50px; ">ID</th>
                <th style="width: 300px;">Keterangan</th>
                <th style="width: 100px;">Suku Bunga</th>
                <th style="width: 100px;">DP</th>
                <th style="width: 150px;">Plafond</th>
                <th style="width: 150px;">Sisa Plafond</th>
                <th style="width: 300px;">Tujuan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align:center;"><?= htmlspecialchars($row['id']); ?></td>
                        <td style="text-align:left;"><?= htmlspecialchars($row['Ket']); ?></td>
                        <td style="text-align:center;"><?= htmlspecialchars($row['suku_bunga']); ?> %</td>
                        <td style="text-align:center;"><?= htmlspecialchars($row['dp']); ?> %</td>
                        <td style="text-align:right;">Rp. <?= htmlspecialchars(number_format($row['Plafond'], 0, ',', '.')); ?></td>
                        <td style="text-align:right;">Rp. <?= htmlspecialchars(number_format($row['sisa_plafond'], 0, ',', '.')); ?></td>
                        <td style="text-align:left;"><?= htmlspecialchars($row['tujuan']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <p style="text-align: justify;"><strong>Bank</strong> </p>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
    <table style="width: 1150px; margin: 0 auto;">
        <p style="text-align: center;"><strong>Bank</strong> </p>
        <thead>
            <tr style="text-align:center;">
                <th style="width: 50px; ">ID</th>
                <th style="width: 300px;">Keterangan</th>
                <th style="width: 100px;">Suku Bunga</th>
                <th style="width: 100px;">DP</th>
                <th style="width: 150px;">Plafond</th>
                <th style="width: 150px;">Sisa Plafond</th>
                <th style="width: 300px;">Tujuan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result2->num_rows > 0): ?>
                <?php while ($row = $result2->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align:center;"><?= htmlspecialchars($row['id']); ?></td>
                        <td style="text-align:left;"><?= htmlspecialchars($row['Ket']); ?></td>
                        <td style="text-align:center;"><?= htmlspecialchars($row['suku_bunga']); ?> %</td>
                        <td style="text-align:center;"><?= htmlspecialchars($row['dp']); ?> %</td>
                        <td style="text-align:right;">Rp. <?= htmlspecialchars(number_format($row['Plafond'], 0, ',', '.')); ?></td>
                        <td style="text-align:right;">Rp. <?= htmlspecialchars(number_format($row['sisa_plafond'], 0, ',', '.')); ?></td>
                        <td style="text-align:left;"><?= htmlspecialchars($row['tujuan']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Tidak ada data yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
<script>
    // Convert PHP data to JavaScript
    var leasingData = <?php echo json_encode($leasingData); ?>;
    var bankData = <?php echo json_encode($bankData); ?>;

    // Function to generate PDF
    function generatePDFKI() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Leasing Table
        doc.text("Leasing", 14, 10);
        doc.autoTable({
            head: [
                ['ID', 'Keterangan', 'Suku Bunga', 'DP', 'Plafond', 'Sisa Plafond', 'Tujuan']
            ],
            body: leasingData.map(row => [
                row.id,
                row.Ket,
                row.suku_bunga + ' %',
                row.dp + ' %',
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.Plafond),
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.sisa_plafond),
                row.tujuan
            ]),
            styles: {
                fontSize: 8,
                lineColor: [0, 0, 0],
                lineWidth: 0.1
            },
            columnStyles: {
                0: {
                    cellWidth: 10
                },
                1: {
                    cellWidth: 60
                },
                2: {
                    cellWidth: 30
                },
                3: {
                    cellWidth: 30
                },
                4: {
                    cellWidth: 40
                },
                5: {
                    cellWidth: 40
                },
                6: {
                    cellWidth: 60
                }
            },
            startY: 20,
        });
        // Bank Table
        doc.text("Bank", 14, doc.lastAutoTable.finalY + 10);
        doc.autoTable({
            head: [
                ['ID', 'Keterangan', 'Suku Bunga', 'DP', 'Plafond', 'Sisa Plafond', 'Tujuan']
            ],
            body: bankData.map(row => [
                row.id,
                row.Ket,
                row.suku_bunga + ' %',
                row.dp + ' %',
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.Plafond),
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.sisa_plafond),
                row.tujuan
            ]),
            styles: {
                fontSize: 8,
                lineColor: [0, 0, 0],
                lineWidth: 0.1
            },
            columnStyles: {
                0: {
                    cellWidth: 10
                },
                1: {
                    cellWidth: 60
                },
                2: {
                    cellWidth: 30
                },
                3: {
                    cellWidth: 30
                },
                4: {
                    cellWidth: 40
                },
                5: {
                    cellWidth: 40
                },
                6: {
                    cellWidth: 60
                }
            },
            startY: doc.lastAutoTable.finalY + 20,
        });

        var today = new Date();
        var date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
        doc.save('Rekapan Plafond KI - ' + date + '.pdf');
        // doc.output('dataurlnewwindow');
    }
</script>

</html>
<?php
// Tutup koneksi
$conn->close();
?>