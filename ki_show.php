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

$sql_btob = "SELECT * FROM bnl where jenis_bnl = 'Back to Back'";
$result3 = $conn->query($sql_btob);
if (!$result3) {
    die("Query error: " . $conn->error);
}
// $bankData += $result3->fetch_all(MYSQLI_ASSOC);
$result3->data_seek(0);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Koneksi gagal. Silakan coba lagi.");
}

// Query to retrieve all accounts
$sql_saldo = "
SELECT 
    lr.no_akun, 
    lr.nama_bank, 
    lr.nama_akun, 
    lr.saldo,
    lr.updtgl,
    lr.id_entitas, 
    le.nama_entitas 
FROM 
    list_rekening AS lr
INNER JOIN 
    list_entitas AS le 
ON 
    lr.id_entitas = le.id_entitas
order by lr.id_entitas, lr.nama_bank
    ";
$result_saldo = $conn->query($sql_saldo);

// Check for query execution errors
if (!$result_saldo) {
    error_log("Query failed: " . $conn->error);
    die("Terjadi kesalahan saat mengambil data.");
}

// Function to format numbers as Rupiah
function formatRupiah($value)
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}
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

        thead {
            padding: 8px;
            font-size: 12pt;
            text-align: center;
        }


        td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 12pt;
        }

        @media only screen and (max-width: 600px) {

            th,
            td {
                padding: 4px;
                font-size: 7pt;
            }

            h1,
            h2 {
                font-size: 18px;
            }

            p {
                font-size: 14px;
            }

            button,
            a {
                font-size: 20px;
            }
        }

        @media only screen and (max-width: 440px) {

            th,
            td {
                padding: 4px;
                font-size: 5pt;
            }

            h1,
            h2 {
                font-size: 18px;
            }

            p {
                font-size: 14px;
            }

            button,
            a {
                font-size: 20px;
            }
        }

        @media only screen and (max-width: 350px) {

            th,
            td {
                padding: 3px;
                font-size: 4pt;
            }

            h1,
            h2 {
                font-size: 8pt !important;
            }

            p {
                font-size: 14px;
            }

            button,
            a {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <h1 style="margin: 0 auto;">Bunga Leasing & Bank</h1>
    <br>
    <div>
    <?php if (isset($_SESSION['UsrLevel']) && ($_SESSION['UsrLevel'] == '1' || $_SESSION['UsrLevel'] == '2')): ?>
        <button class="btn btn-danger" title="export to pdf" onclick="generatePDFKI()" style="font-size: 28px;margin : 0 auto;"><i class="bi bi-file-pdf"></i></button>
        <?php endif; ?>
        <?php if ($_SESSION['username'] == 'financeview'): ?>
            <a id="verifiedButton" onclick="updateVerifiedStatus_bnl()" class="btn btn-success" style="margin-left:10px;font-size:28px;">
                <i class="bi bi-patch-check"></i>
            </a>
        <?php endif; ?>
        <script>
            function updateVerifiedStatus_bnl() {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert("Verified status updated successfully!");
                        location.reload(); // Reload the page to reflect changes
                    }
                };
                xhr.send("update_verified_status_bnl=1");
            }
        </script>
        <div>
            <p></p>
            <?php
            if ($verifiedResult_bnl === false) {
                echo "<span style='color: red; font-weight: bold;'>Error executing query: " . $conn->error . "</span>";
            } else if ($verifiedResult_bnl->num_rows > 0) {
                // Fetch the result as an associative array
                $row = $verifiedResult_bnl->fetch_assoc();
                $verified = $row['Verified'];  // Get the value of 'Verified'

                // Check the value of 'Verified' and display accordingly
                if ($verified == 1) {
                    echo "<br><span style='color: green; font-weight: bold; margin : 0 auto'>Verified At: " . date('d-M-Y H:i:s', strtotime($row['verified_at'])) . "</span>";
                } else if ($verified == 0) {
                    echo "<span style='color: red; font-weight: bold; margin : 0 auto'>Unverified</span>";
                } else {
                    echo "<span style='color: gray; font-weight: bold;margin : 0 auto'>Unknown Status</span>";
                }
            } else {
                // Handle the case where no rows were returned
                echo "<span style='color: gray; font-weight: bold;'>No data found</span>";
            }
            ?>

            <P style="margin : 0 auto">Last Update Saldo At :
                <?php
                echo date('d-M-Y H:i:s', strtotime($lastupd_bnl_row['updtgl']));
                ?>
            </p>
        </div>

        <table style="margin: 0 auto; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden;">
            <br>
            <p style="margin: 0 auto;"><strong>Leasing</strong> </P>
            <thead>
                <tr style="text-align:center;">
                    <th style="width: 50px;">No.</th>
                    <th style="width: 300px;">Keterangan</th>
                    <th style="width: 100px;">Suku Bunga</th>
                    <th style="width: 100px;">Jenis Bunga</th>
                    <th style="width: 100px;">DP</th>
                    <th style="width: 150px;">Plafond Full</th>
                    <th style="width: 150px;">Plafond Terpakai</th>
                    <th style="width: 150px;">Sisa Plafond</th>
                    <th style="width: 300px;">Tujuan</th>
                </tr>
            </thead>
            <tbody>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= $no++; ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['Ket']); ?></td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['suku_bunga']); ?> %</td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['jenisbunga']); ?></td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['dp']); ?> %</td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['Plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['Plafond'] - $row['sisa_plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['sisa_plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['tujuan']); ?></td>
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

        <table style="margin: 0 auto; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden;">
            <p style="margin: 0 auto;"><strong>Bank</strong> </p>
            
            <thead>
                <tr>
                    <th style="width: 50px; ">No.</th>
                    <th style="width: 300px;">Keterangan</th>
                    <th style="width: 50px;">Entitas</th>
                    <th style="width: 50px;">Jenis</th>
                    <th style="width: 100px;">Suku Bunga</th>
                    <th style="width: 100px;">Jenis Bunga</th>
                    <th style="width: 100px;">DP</th>
                    <th style="width: 150px;">Plafond Full</th>
                    <th style="width: 150px;">Plafond Terpakai</th>
                    <th style="width: 150px;">Sisa Plafond</th>
                    <th style="width: 300px;">Tujuan</th>
                </tr>
            </thead>
            <tbody>
                
                <?php if ($result2->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $result2->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= $no++; ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['Ket']); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['entitas']); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['jenis']); ?></td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['suku_bunga']); ?> %</td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['jenisbunga']); ?></td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['dp']); ?> %</td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['Plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['Plafond'] - $row['sisa_plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['sisa_plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['tujuan']); ?></td>
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
        <!-- <table style="margin: 0 auto; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden;">
            <p style="margin: 0 auto;"><strong>Back to Back</strong> </p>
            
            <thead>
                <tr>
                    <th style="width: 50px; ">No.</th>
                    <th style="width: 300px;">Keterangan</th>
                    <th style="width: 50px;">Entitas</th>
                    <th style="width: 50px;">Jenis</th>
                    <th style="width: 100px;">Suku Bunga</th>
                    <th style="width: 100px;">Jenis Bunga</th>
                    <th style="width: 100px;">DP</th>
                    <th style="width: 150px;">Plafond Full</th>
                    <th style="width: 150px;">Plafond Terpakai</th>
                    <th style="width: 150px;">Sisa Plafond</th>
                    <th style="width: 300px;">Tujuan</th>
                </tr>
            </thead>
            <tbody>
                
                <?php if ($result3->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $result3->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= $no++; ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['Ket']); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['entitas']); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['jenis']); ?></td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['suku_bunga']); ?> %</td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['jenisbunga']); ?></td>
                            <td style="text-align:center; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['dp']); ?> %</td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['Plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['Plafond'] - $row['sisa_plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:right; font-family: 'Times New Roman', serif;">Rp. <?= htmlspecialchars(number_format($row['sisa_plafond'], 0, ',', '.')); ?></td>
                            <td style="text-align:left; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($row['tujuan']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Tidak ada data yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table> -->
        <br>
        <br>
        <hr style="border: 1px solid black;">

        <h2 style="margin: 0 auto;">Saldo Bank</h2>

        <?php if ($_SESSION['username'] == 'financeview'): ?>
            <a id="verifiedButton" onclick="updateVerifiedStatus()" class="btn btn-success" style="margin: 0 auto;font-size:28px;">
                <i class="bi bi-patch-check"></i>
            </a>
        <?php endif; ?>
        <script>
            function updateVerifiedStatus() {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert("Verified status updated successfully!");
                        location.reload(); // Reload the page to reflect changes
                    }
                };
                xhr.send("update_verified_status=1");
            }
        </script>
        <br>
        <?php
        if ($verifiedResult === false) {
            echo "<span style='color: red; font-weight: bold;margin : 0 auto'>Error executing query: " . $conn->error . "</span>";
        } else if ($verifiedResult->num_rows > 0) {
            // Fetch the result as an associative array
            $row = $verifiedResult->fetch_assoc();
            $verified = $row['Verified'];  // Get the value of 'Verified'

            // Check the value of 'Verified' and display accordingly
            if ($verified == 1) {
                echo "<br><span style='color: green; font-weight: bold;margin : 0 auto'>Verified At: " . date('d-M-Y H:i:s', strtotime($row['verified_at'])) . "</span>";
            } else if ($verified == 0) {
                echo "<span style='color: red; font-weight: bold;margin : 0 auto'>Unverified</span>";
            } else {
                echo "<span style='color: gray; font-weight: bold;margin : 0 auto'>Unknown Status</span>";
            }
        } else {
            // Handle the case where no rows were returned
            echo "<span style='color: gray; font-weight: bold;margin : 0 auto'>No data found</span>";
        }
        ?>

        <div>
            <P style="margin : 0 auto">Last Update Saldo At :
                <?php
                echo date('d-M-Y H:i:s', strtotime($lastupd_row['updtgl']));
                ?>
            </p>
        </div>
        <table style="margin: 0 auto; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden;">
            <thead>
            <tr>
                <th>No.</th>
                <th>Nomor Akun</th>
                <th>Nama Bank</th>
                <th>Nama Akun</th>
                <th style="text-align:center;">Saldo ERP</th>
                <th style="text-align:center;">Saldo RK</th>
                <th style="text-align:center;">Selisih Saldo</th>
            </tr>
            </thead>
            <?php
            if ($result_saldo->num_rows > 0) {
                $counter = 1; // Initialize counter for serial number
                while ($row2 = $result_saldo->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td style='font-family: Times New Roman, serif;text-align:center;'>" . $counter++ . "</td>"; // Display the serial number
                    echo "<td style='font-family: Times New Roman, serif;'>" . htmlspecialchars($row2["no_akun"]) . "</td>";
                    echo "<td style='font-family: Times New Roman, serif;'>" . htmlspecialchars($row2["nama_bank"]) . "</td>";
                    echo "<td style='font-family: Times New Roman, serif;'>" . htmlspecialchars($row2["nama_akun"]) . "</td>";
                    echo "<td style='text-align:justify; font-family: Times New Roman, serif;'>" . formatRupiah($row2["saldoerp"]) . "</td>";
                    echo "<td style='text-align:justify; font-family: Times New Roman, serif;'>" . formatRupiah($row2["saldo"]) . "</td>";
                    echo "<td style='text-align:justify; font-family: Times New Roman, serif;'>" . formatRupiah($row2["saldoerp"] - $row2["saldo"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='font-family: Times New Roman, serif; font-size: 14px;'>Tidak ada data rekening ditemukan.</td></tr>";
            }

            ?>
        </table>
        <br><br>
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
                ['ID', 'Keterangan', 'Suku Bunga', 'Jenis Bunga', 'Porsi DP', 'Plafond Full', 'Plafond Terpakai', 'Sisa Plafond', 'Tujuan']
            ],
            body: leasingData.map(row => [
                row.id,
                row.Ket,
                row.suku_bunga + ' %',
                row.jenisbunga,
                row.dp + ' %',
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.Plafond),
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.Plafond - row.sisa_plafond),
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
                    cellWidth: 50
                },
                2: {
                    cellWidth: 20
                },
                3: {
                    cellWidth: 20
                },
                4: {
                    cellWidth: 20
                },
                5: {
                    cellWidth: 35
                },
                6: {
                    cellWidth: 35
                },
                7: {
                    cellWidth: 35
                },
                8: {
                    cellWidth: 50
                }
            },
            startY: 20,
        });
        // Bank Table
        doc.text("Bank", 14, doc.lastAutoTable.finalY + 10);
        doc.autoTable({
            head: [
                ['ID', 'Keterangan', 'Entitas', 'Jenis', 'Suku Bunga', 'Jenis Bunga', 'Porsi DP', 'Plafond Full', 'Plafond Terpakai', 'Sisa Plafond', 'Tujuan']
            ],
            body: bankData.map(row => [
                row.id,
                row.Ket,
                row.entitas,
                row.jenis,
                row.suku_bunga + ' %',
                row.jenisbunga,
                row.dp + ' %',
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.Plafond),
                'Rp. ' + new Intl.NumberFormat('id-ID').format(row.Plafond - row.sisa_plafond),
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
                    cellWidth: 35
                },
                2: {
                    cellWidth: 15
                },
                3: {
                    cellWidth: 15
                },
                4: {
                    cellWidth: 15
                },
                5: {
                    cellWidth: 20
                },
                6: {
                    cellWidth: 15
                },
                7: {
                    cellWidth: 35
                },
                8: {
                    cellWidth: 35
                },
                9: {
                    cellWidth: 35
                },
                10: {
                    cellWidth: 45
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