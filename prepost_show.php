<?php
// Koneksi ke database
include 'koneksi.php';
// Ambil data dari tabel bnl
$sql_plafon = "SELECT * FROM pfb";
$result = $conn->query($sql_plafon);
if (!$result) {
    die("Query error: " . $conn->error);
}
$plafonData = $result->fetch_all(MYSQLI_ASSOC);
$result->data_seek(0);
$sql_bank = "SELECT * FROM pfb  where jenis = 'POST'";
$result2 = $conn->query($sql_bank);
if (!$result2) {
    die("Query error: " . $conn->error);
}
$bankData = $result2->fetch_all(MYSQLI_ASSOC);
$result2->data_seek(0);
$groupedData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $groupedData[$row['bank']][] = $row; // Kelompokkan data berdasarkan nama bank
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plafon Fasilitas Perbankan</title>
    <style>
    </style>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> 
</head>
<body>
    <h1 style="text-align: center;text-shadow:  2px 2px black; color: cyan">Plafon Fasilitas Perbankan</h1>
    <br>
    <table style="margin: 0 auto; border-radius: 10px; border: 1px solid black; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <p style="text-align: left;"><strong>PT. GLOBAL ENERGI LESTARI DAN GROUP</strong> </P>
        <thead>
            <tr style="text-align:center; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                <th style="width: 50px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" rowspan="2">NO</th>
                <th style="width: 300px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" rowspan="2">Bank</th>
                <th style="width: 100px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" rowspan="2">Entitas</th>
                <th style="width: 350px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" rowspan="2">Keterangan</th>
                <th style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" colspan="2">PRE</th>
                <th style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" colspan="2">POST</th>
                <th style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" colspan="2">REVOLVING</th>
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;" rowspan="2">Kelonggaran Tarik</th>
            </tr>
            <tr style="text-align:center; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;">Plafon</th>
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;">Outstanding</th>
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;">Plafon</th>
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;">Outstanding</th>
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;">Plafon</th>
                <th style="width: 250px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);background-color: #3498eb;">Outstanding</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($groupedData)): ?>
    <?php foreach ($groupedData as $bankName => $rows): ?>
        <tr>
            <td colspan="11" style="font-weight: bold; text-align: left; background-color: #f0f0f0;border:1px solid black">
                <span style=" margin-left:10px"><?= htmlspecialchars( $bankName); ?> <!-- Nama Bank --></span>
            </td>
        </tr>
        <?php foreach ($rows as $row): ?>
            <tr style="border: 1px solid black;">
                <td style="text-align:center; border: 1px solid black;"><?= htmlspecialchars($row['id']); ?></td>
                <td style="text-align:left; border: 1px solid black;"><span style=" margin-left:10px"><?= htmlspecialchars($row['bank']); ?></span></td>
                <td style="text-align:center; border: 1px solid black;"><?= htmlspecialchars($row['entitas']); ?></td>
                <td style="text-align:center; border: 1px solid black;"><?= htmlspecialchars($row['ket']); ?></td>
                <?php if ($row['jenis'] == 'PRE'): ?>
                    <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['plafond'], 0, ',', '.')); ?></span></td>
                    <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['outstanding'], 0, ',', '.')); ?></span></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                <?php endif; ?>
                <?php if ($row['jenis'] == 'POST'): ?>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['plafond'], 0, ',', '.')); ?></span></td>
                    <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['outstanding'], 0, ',', '.')); ?></span></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                <?php endif; ?>
                <?php if ($row['jenis'] == 'REVOLVING'): ?>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['plafond'], 0, ',', '.')); ?></span></td>
                    <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['outstanding'], 0, ',', '.')); ?></span></td>
                <?php endif; ?>
                <td style="text-align:right; border: 1px solid black;">Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format($row['plafond'] - $row['outstanding'], 0, ',', '.')); ?></span></td>
            </tr>
        <?php endforeach; ?>
        <tr style="background-color: #ccdeb6;">
                <td colspan="4" style="text-align:center; border: 1px solid black;"><strong>Subtotal :</strong></td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column(array_filter($rows, function($row) { return $row['jenis'] == 'PRE'; }), 'plafond')), 0, ',', '.')); ?></span>
                </td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column(array_filter($rows, function($row) { return $row['jenis'] == 'PRE'; }), 'outstanding')), 0, ',', '.')); ?></span>
                </td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column(array_filter($rows, function($row) { return $row['jenis'] == 'POST'; }), 'plafond')), 0, ',', '.')); ?></span>
                </td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column(array_filter($rows, function($row) { return $row['jenis'] == 'POST'; }), 'outstanding')), 0, ',', '.')); ?></span>
                </td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column(array_filter($rows, function($row) { return $row['jenis'] == 'REVOLVING'; }), 'plafond')), 0, ',', '.')); ?></span>
                </td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column(array_filter($rows, function($row) { return $row['jenis'] == 'REVOLVING'; }), 'outstanding')), 0, ',', '.')); ?></span>
                </td>
                <td style="text-align:right; border: 1px solid black;">
                    Rp. <span style=" margin-right:10px"><?= htmlspecialchars(number_format(array_sum(array_column($rows, 'plafond')) - array_sum(array_column($rows, 'outstanding')), 0, ',', '.')); ?></span>
                </td>
            </tr>
    <?php endforeach; ?>
<?php endif; ?>
            <tr>
                <td colspan="4" style="text-align:center; border: 1px solid black;background-color:orange;"><strong>Total Plafon</strong></td>
               
                <td colspan="6" style="text-align:right; border: 1px solid black; background-color:orange;">
                    Rp. <?= htmlspecialchars(number_format(array_sum(array_column($plafonData, 'plafond')), 0, ',', '.')); ?>
                </td>
                <td style="border:1px solid black; background-color:orange;"></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center; border: 1px solid black;background-color:orange;"><strong>Total Outstanding</strong></td>
               
                <td colspan="6" style="text-align:right; border: 1px solid black; background-color:orange;">
                    Rp. <?= htmlspecialchars(number_format(array_sum(array_column($plafonData, 'outstanding')), 0, ',', '.')); ?>
                </td>
                <td style="border:1px solid black; background-color:orange;"></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center; border: 1px solid black;background-color:orange;"><strong>Total Kelonggaran Tarik</strong></td>
               
                <td colspan="6" style="text-align:right; border: 1px solid black; background-color:orange;">
                   
                </td>
                <td style="text-align:right; border: 1px solid black; background-color:orange;" >
                
                    Rp. <?= htmlspecialchars(number_format(array_sum(array_column($plafonData, 'plafond')) - array_sum(array_column($plafonData, 'outstanding')), 0, ',', '.')); ?>
                
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>

<script>
    // Convert PHP data to JavaScript
    var groupedData = <?php echo json_encode($groupedData); ?>;

    function generatePDFPrePost() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Define the table header
        const head = [
            [
                { content: "NO", rowSpan: 2 },
                { content: "Bank", rowSpan: 2 },
                { content: "Entitas", rowSpan: 2 },
                { content: "Keterangan", rowSpan: 2 },
                { content: "PRE", colSpan: 2 },
                { content: "POST", colSpan: 2 },
                { content: "REVOLVING", colSpan: 2 },
                { content: "Kelonggaran Tarik", rowSpan: 2 },
            ],
            [
                { content: "Plafon" },
                { content: "Outstanding" },
                { content: "Plafon" },
                { content: "Outstanding" },
                { content: "Plafon" },
                { content: "Outstanding" },
            ],
        ];

        // Initialize table body
        const body = [];
        let totalPrePlafon = 0;
        let totalPreOutstanding = 0;
        let totalPostPlafon = 0;
        let totalPostOutstanding = 0;
        let totalRevolvingPlafon = 0;
        let totalRevolvingOutstanding = 0;
        let totalKelonggaran = 0;
        let totalAllPlafon = 0;
        let totalAllOutstanding = 0;
        let no = 1;

        // Process each bank group
        for (const bankName in groupedData) {
            const bankRows = groupedData[bankName];

            // Add group header
            body.push([{ content: bankName, colSpan: 11, styles: { fontStyle: "bold", halign: "left", fillColor: [230, 230, 230] } }]);

            let groupPrePlafon = 0;
            let groupPreOutstanding = 0;
            let groupPostPlafon = 0;
            let groupPostOutstanding = 0;
            let groupRevolvingPlafon = 0;
            let groupRevolvingOutstanding = 0;
            let groupKelonggaran = 0;

            // Process rows within the group
            bankRows.forEach(row => {
                const kelonggaran = parseFloat(row.plafond) - parseFloat(row.outstanding);
                body.push([
                    no++,
                    row.bank,
                    row.entitas,
                    row.ket,
                    row.jenis === "PRE" ? `Rp. ${parseFloat(row.plafond).toLocaleString()}` : "",
                    row.jenis === "PRE" ? `Rp. ${parseFloat(row.outstanding).toLocaleString()}` : "",
                    row.jenis === "POST" ? `Rp. ${parseFloat(row.plafond).toLocaleString()}` : "",
                    row.jenis === "POST" ? `Rp. ${parseFloat(row.outstanding).toLocaleString()}` : "",
                    row.jenis === "REVOLVING" ? `Rp. ${parseFloat(row.plafond).toLocaleString()}` : "",
                    row.jenis === "REVOLVING" ? `Rp. ${parseFloat(row.outstanding).toLocaleString()}` : "",
                    `Rp. ${kelonggaran.toLocaleString()}`,
                ]);

                // Accumulate group totals
                if (row.jenis === "PRE") {
                    groupPrePlafon += parseFloat(row.plafond);
                    groupPreOutstanding += parseFloat(row.outstanding);
                } else if (row.jenis === "POST") {
                    groupPostPlafon += parseFloat(row.plafond);
                    groupPostOutstanding += parseFloat(row.outstanding);
                } else if (row.jenis === "REVOLVING") {
                    groupRevolvingPlafon += parseFloat(row.plafond);
                    groupRevolvingOutstanding += parseFloat(row.outstanding);
                }
                groupKelonggaran += kelonggaran;
            });

            // Add group subtotal
            body.push([
                { content: "Subtotal :", colSpan: 4, styles: { fontStyle: "bold", fillColor: [245, 245, 245] } },
                `Rp. ${groupPrePlafon.toLocaleString()}`,
                `Rp. ${groupPreOutstanding.toLocaleString()}`,
                `Rp. ${groupPostPlafon.toLocaleString()}`,
                `Rp. ${groupPostOutstanding.toLocaleString()}`,
                `Rp. ${groupRevolvingPlafon.toLocaleString()}`,
                `Rp. ${groupRevolvingOutstanding.toLocaleString()}`,
                `Rp. ${groupKelonggaran.toLocaleString()}`,
            ]);

            // Accumulate overall totals
            totalPrePlafon += groupPrePlafon;
            totalPreOutstanding += groupPreOutstanding;
            totalPostPlafon += groupPostPlafon;
            totalPostOutstanding += groupPostOutstanding;
            totalRevolvingPlafon += groupRevolvingPlafon;
            totalRevolvingOutstanding += groupRevolvingOutstanding;
            totalKelonggaran += groupKelonggaran;

            totalAllPlafon = totalPrePlafon + totalPostPlafon + totalRevolvingPlafon;
            totalAllOutstanding = totalPreOutstanding + totalPostOutstanding + totalRevolvingOutstanding;
        }

        // Add overall totals
        body.push(
            [
                { content: "Total Plafon", colSpan: 4, styles: { fontStyle: "bold", fillColor: [255, 153, 0] } },
                { content: `Rp. ${totalAllPlafon.toLocaleString()}`, colSpan: 6, styles: { halign: 'right', fillColor: [255, 153, 0] } },
                { content: "", styles: { fillColor: [255, 153, 0] } },
            ],
            [
                { content: "Total Outstanding", colSpan: 4, styles: { fontStyle: "bold", fillColor: [255, 153, 0] } },
                { content: `Rp. ${totalAllOutstanding.toLocaleString()}`, colSpan: 6, styles: { halign: 'right', fillColor: [255, 153, 0] } },
                { content: "", styles: { fillColor: [255, 153, 0] } },
            ],
            [
                { content: "Total Kelonggaran Tarik", colSpan: 4, styles: { fontStyle: "bold", fillColor: [255, 153, 0] } },
                { content: "", colSpan: 6, styles: { fontStyle: "bold", fillColor: [255, 153, 0] } },
                { content: `Rp. ${totalKelonggaran.toLocaleString()}`, styles: { halign: 'right', fillColor: [255, 153, 0] } },
            ]
        );

        // Generate the PDF table
        doc.autoTable({
            head: head,
            body: body,
            startY: 10,
            theme: 'grid',
            styles: { fontSize: 6, halign: 'center', valign: 'middle' },
            headStyles: { fillColor: [52, 152, 219], textColor: [255, 255, 255], lineColor: [0, 0, 0], lineWidth: 0.1, }, // Header styling
            bodyStyles: { fillColor: [245, 245, 245], lineColor: [0, 0, 0], lineWidth: 0.1, }, // Body styling
            alternateRowStyles: { fillColor: [255, 255, 255], lineColor: [0, 0, 0], lineWidth: 0.1, }, // Alternating row colors
        });

        var today = new Date();
        var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
        doc.save('Rekap Plafond Pre Post - ' + date + '.pdf');
        // Open the PDF in a new window
        // doc.output('dataurlnewwindow');
    }

    function formatCurrency(value) {
        return 'Rp. ' + parseFloat(value).toLocaleString('id-ID', { minimumFractionDigits: 0 });
    }

    function calculateSubtotal(rows, jenis, field) {
        return rows
            .filter(row => row.jenis === jenis)
            .reduce((sum, row) => sum + parseFloat(row[field]), 0);
    }
</script>
</html>
<?php
// Tutup koneksi
$conn->close();
?>