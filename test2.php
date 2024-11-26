<?php
include 'koneksi.php';

// SQL query for Giro with breakdown and total per entity
$sql_giro = "
    -- Breakdown per account number
    SELECT 
        'Giro' AS jenis,
        e.nama_entitas,
        d.namabank,
        d.ac_number,
        SUM(CASE WHEN TRIM(d.statusgiro) = 'Unused' THEN 1 ELSE 0 END) AS jumlah_unused,
        SUM(CASE WHEN dg.StatGiro = 'void' THEN 1 ELSE 0 END) AS jumlah_void,
        SUM(CASE WHEN dg.StatGiro = 'posted' THEN 1 ELSE 0 END) AS jumlah_posted,
        SUM(CASE WHEN dg.StatGiro = 'issued' THEN 1 ELSE 0 END) AS jumlah_issued
    FROM 
        data_giro AS d
    LEFT JOIN 
        detail_giro AS dg ON d.nogiro = dg.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number

    UNION ALL

    -- Total per entity
    SELECT 
        'Giro' AS jenis,
        e.nama_entitas,
        NULL AS namabank,
        NULL AS ac_number,
        SUM(CASE WHEN TRIM(d.statusgiro) = 'Unused' THEN 1 ELSE 0 END) AS jumlah_unused,
        SUM(CASE WHEN dg.StatGiro = 'void' THEN 1 ELSE 0 END) AS jumlah_void,
        SUM(CASE WHEN dg.StatGiro = 'posted' THEN 1 ELSE 0 END) AS jumlah_posted,
        SUM(CASE WHEN dg.StatGiro = 'issued' THEN 1 ELSE 0 END) AS jumlah_issued
    FROM 
        data_giro AS d
    LEFT JOIN 
        detail_giro AS dg ON d.nogiro = dg.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    GROUP BY 
        e.nama_entitas
    ORDER BY 
        e.nama_entitas, namabank, ac_number;
";

// Include Bootstrap CSS for styling
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">';
echo "<div class='container'>";
echo "<div class='row'>";

// Function to display Giro data
function displayGiroTable($result, $title, $bgColor) {
    if ($result->num_rows > 0) {
        echo "<div class='col'>";
        echo "<h5 style='padding-left:10px'>SUMMARY $title</h5>";
        echo "<table style='border: 1px solid black;margin:10px;width:900px'>
                <thead style='background-color:$bgColor;text-align:center;'>
                    <tr>
                        <th rowspan='2' style='border-bottom: 1px solid black;'><strong>KETERANGAN</strong></th>
                        <th colspan='5' style='border-bottom: 1px solid black;border-left: 1px solid black;'><strong>STATUS</strong></th>
                    </tr>
                    <tr>
                        <th style='border-bottom: 1px solid black;border-left: 1px solid black;'>Available</th>
                        <th style='border-bottom: 1px solid black;border-left: 1px solid black;'>Batal</th>
                        <th style='border-bottom: 1px solid black;border-left: 1px solid black;'>Belum Cair</th>
                        <th style='border-bottom: 1px solid black;border-left: 1px solid black;'>Cair</th>
                        <th style='border-bottom: 1px solid black;border-left: 1px solid black;'>Grand Total</th>
                    </tr>
                </thead>
                <tbody>";

        $last_nama_entitas = null;
        while ($row = $result->fetch_assoc()) {
            $jumlah_total = $row['jumlah_unused'] + $row['jumlah_void'] + $row['jumlah_posted'] + $row['jumlah_issued'];

            if ($row['ac_number'] === null && $row['namabank'] === null) {
                // Total per entity
                echo "<tr style='text-align:right;'>
                        <td style='text-align:left;border-right: 1px solid black;border-bottom:1px solid black;background-color:$bgColor;font-weight:bold;padding-left:5px;'>Total " . $row['nama_entitas'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_unused'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_void'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_posted'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_issued'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $jumlah_total . "</td>
                    </tr>";
                $last_nama_entitas = $row['nama_entitas'];
            } elseif ($last_nama_entitas === $row['nama_entitas']) {
                // Breakdown per account number
                echo "<tr style='text-align:right;'>
                        <td style='text-align:left;border-right: 1px solid black;border-bottom:1px solid black;background-color:#f9f9f9;font-weight:normal;padding-left:5px;'> - " . $row['namabank'] . " (" . $row['ac_number'] . ")</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_unused'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_void'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_posted'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_issued'] . "</td>
                        <td style='border: 1px solid black;text-align:center;'>" . $jumlah_total . "</td>
                    </tr>";
            }
        }

        echo "</tbody></table></div>";
    } else {
        echo "<div class='col'><h5>SUMMARY $title</h5>No results found.</div>";
    }
}

// Execute the Giro query and display results
$result_giro = $conn->query($sql_giro);
displayGiroTable($result_giro, "GIRO", "#c6e0b4");

echo "</div></div>";

// Close the database connection
$conn->close();
?>
