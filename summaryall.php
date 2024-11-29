<?php
include 'koneksi.php';

// SQL query to fetch the data for Giro
$sql_giro = "
    SELECT 
        'Giro' AS jenis,  -- Fixed value 'Giro' to categorize the rows
        e.nama_entitas,    -- Entity name from 'list_entitas' table
        d.namabank,        -- Bank name from 'data_giro' table
        d.ac_number,       -- Account number from 'data_giro' table
        
        -- Count of 'Unused' status giro transactions
        SUM(CASE WHEN TRIM(d.statusgiro) = 'Unused' THEN 1 ELSE 0 END) AS jumlah_unused,
        
        -- Count of 'void' status giro transactions
        SUM(CASE WHEN dg.StatGiro = 'void' THEN 1 ELSE 0 END) AS jumlah_void,
        
        -- Count of 'posted' status giro transactions
        SUM(CASE WHEN dg.StatGiro = 'posted' THEN 1 ELSE 0 END) AS jumlah_posted,
        
        -- Count of 'issued' status giro transactions
        SUM(CASE WHEN dg.StatGiro = 'issued' THEN 1 ELSE 0 END) AS jumlah_issued
    FROM 
        data_giro AS d  -- 'data_giro' table containing giro data
    LEFT JOIN 
        detail_giro AS dg ON d.nogiro = dg.nogiro  -- LEFT JOIN 'detail_giro' to get related giro details
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas  -- Join 'list_entitas' on 'id_entitas' to get entity names
    
    -- Group results by entity name, bank name, and account number to ensure one line per account
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number  
    
    -- Order results by entity name, bank name, and account number
    ORDER BY 
        e.nama_entitas, d.namabank, d.ac_number;
";


// SQL query to fetch the data for Cek
$sql_cek = "
    SELECT 
        'cek' AS jenis,  -- Fixed value 'cek' to categorize the rows
        e.nama_entitas,   -- Entity name from 'list_entitas' table
        c.namabank,       -- Bank name from 'data_cek' table
        c.ac_number,      -- Account number from 'data_cek' table
        
        -- Count of 'Unused' status cek transactions
        SUM(CASE WHEN TRIM(c.statuscek) = 'Unused' THEN 1 ELSE 0 END) AS jumlah_unused,
        
        -- Count of 'void' status cek transactions
        SUM(CASE WHEN dg.Statcek = 'void' THEN 1 ELSE 0 END) AS jumlah_void,
        
        -- Count of 'posted' status cek transactions
        SUM(CASE WHEN dg.Statcek = 'posted' THEN 1 ELSE 0 END) AS jumlah_posted,
        
        -- Count of 'issued' status cek transactions
        SUM(CASE WHEN dg.Statcek = 'issued' THEN 1 ELSE 0 END) AS jumlah_issued
    FROM 
        data_cek AS c  -- 'data_cek' table containing cek data
    LEFT JOIN 
        detail_cek AS dg ON c.nocek = dg.nocek  -- LEFT JOIN 'detail_cek' to get related cek details
    INNER JOIN 
        list_entitas AS e ON c.id_entitas = e.id_entitas  -- Join 'list_entitas' on 'id_entitas' to get entity names
    
    -- Group results by entity name, bank name, and account number to ensure one line per account
    GROUP BY 
        e.nama_entitas, c.namabank, c.ac_number  
    
    -- Order results by entity name, bank name, and account number
    ORDER BY 
        e.nama_entitas, c.namabank, c.ac_number;
";


// SQL query to fetch the data for Loa
$sql_loa = "
    SELECT 
        'loa' AS jenis,  -- Fixed value 'loa' to categorize the rows
        e.nama_entitas,   -- Entity name from 'list_entitas' table
        l.namabank,       -- Bank name from 'data_loa' table
        l.ac_number,      -- Account number from 'data_loa' table
        
        -- Count of 'Unused' status loa transactions
        SUM(CASE WHEN TRIM(l.statusloa) = 'Unused' THEN 1 ELSE 0 END) AS jumlah_unused,
        
        -- Count of 'void' status loa transactions
        SUM(CASE WHEN dg.Statloa = 'void' THEN 1 ELSE 0 END) AS jumlah_void,
        
        -- Count of 'posted' status loa transactions
        SUM(CASE WHEN dg.Statloa = 'posted' THEN 1 ELSE 0 END) AS jumlah_posted,
        
        -- Count of 'issued' status loa transactions
        SUM(CASE WHEN dg.Statloa = 'issued' THEN 1 ELSE 0 END) AS jumlah_issued
    FROM 
        data_loa AS l  -- 'data_loa' table containing loa data
    LEFT JOIN 
        detail_loa AS dg ON l.noloa = dg.noloa  -- LEFT JOIN 'detail_loa' to get related loa details
    INNER JOIN 
        list_entitas AS e ON l.id_entitas = e.id_entitas  -- Join 'list_entitas' on 'id_entitas' to get entity names
    
    -- Group results by entity name, bank name, and account number to ensure one line per account
    GROUP BY 
        e.nama_entitas, l.namabank, l.ac_number  
    
    -- Order results by entity name, bank name, and account number
    ORDER BY 
        e.nama_entitas, l.namabank, l.ac_number;
";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary All</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            width: 2500px;
            transform: scale(0.8);
            transform-origin: top left;
        }

        @media print {
            @page {
                size: landscape !important;
                /* scale: 80% !important; */
                /* Removes default margins */
            }

            body {
                margin: 0;
                font-size: 14px;
                -webkit-print-color-adjust: exact;
                /* Ensures background graphics are printed */
                print-color-adjust: exact;
                /* Ensures background graphics are printed */
            }

            /* Hides header and footer */
            header,
            footer {
                display: none;
            }
        }
    </style>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/print-js/1.6.0/print.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/print-js/1.6.0/print.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js" integrity="sha512-d5Jr3NflEZmFDdFHZtxeJtBzk0eB+kkRXWFQqEc1EKmolXjHm2IKCA7kTvXBNjIYzjXfD5XzIjaaErpkZHCkBg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</head>


<div class='page' id="page">
    <div class='row content'>
        <script>
            // Fungsi untuk mencetak halaman secara otomatis setelah halaman dimuat
            window.onload = function() {
                $('#page').printThis({
                importCSS: true,
                importStyle: true,
                loadCSS: "",
                pageTitle: "",
                removeInline: false,
                printDelay: 333,
                header: null,
                footer: null,
                base: false,
                formValues: true,
                canvas: false,
                removeScripts: false,
                copyTagClasses: false,
                beforePrintEvent: null,
                beforePrint: null,
                afterPrint: null,
                printContainer: true,
                printIframe: true,
                printBodyOptions: {
                    styleToAdd: 'size: A3 landscape;',
                    classNameToAdd: 'a3-landscape'
                }
            });
            };
        </script>

        <?php

        $result_cek = $conn->query($sql_cek);
        if ($result_cek->num_rows > 0) {
            echo "<div class='col' style='flex-grow: 0;'>";
            echo "<h5 style='padding-left:10px'>SUMMARY CEK</h5>";
            echo "<table style='border: 1px solid black;margin:10px;width:900px'>
            <thead style='background-color:yellow;text-align:center;'>
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

            // Initialize grand total variables
            $grand_unused = 0;
            $grand_void = 0;
            $grand_issued = 0;
            $grand_posted = 0;
            $grand_total = 0;

            // Start looping over the rows
            $last_nama_entitas = ""; // To track when nama_entitas changes
            while ($row = $result_cek->fetch_assoc()) {
                // Check if nama_entitas has changed and reset the totals
                if ($row['nama_entitas'] != $last_nama_entitas) {
                    // If it's not the first entity, print the previous entity totals
                    if ($last_nama_entitas != "") {
                        // Print the total row for the previous entity (above the bank details)
                        echo "<tr>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;font-weight:bold;padding-left:5px;background-color:#fff2cc;'>TOTAL</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#fff2cc;'>" . $total_unused . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#fff2cc;'>" . $total_void . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#fff2cc;'>" . $total_issued . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#fff2cc;'>" . $total_posted . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#fff2cc;'>" . $total_total . "</td>
                    </tr>";
                    }

                    // Reset the entity totals for the new entity
                    $total_unused = 0;
                    $total_void = 0;
                    $total_issued = 0;
                    $total_posted = 0;
                    $total_total = 0;
                    $last_nama_entitas = $row['nama_entitas'];

                    // Display the new entity (with totals for this entity)
                    echo "<tr>
                    <td style='border: 1px solid black;font-weight:bold;padding-left:5px;background-color:#fff2cc;'>" . $row['nama_entitas'] . "</td>
                    <td style='border: 1px solid black;text-align:center;background-color:#fff2cc;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#fff2cc;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#fff2cc;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#fff2cc;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#fff2cc;'></td>
                </tr>";
                }

                // Accumulate values for the current entity
                $jumlah_total = $row['jumlah_unused'] + $row['jumlah_void'] + $row['jumlah_issued'] + $row['jumlah_posted'];

                $total_unused += $row['jumlah_unused'];
                $total_void += $row['jumlah_void'];
                $total_issued += $row['jumlah_issued'];
                $total_posted += $row['jumlah_posted'];
                $total_total += $jumlah_total;

                // Accumulate grand totals
                $grand_unused += $row['jumlah_unused'];
                $grand_void += $row['jumlah_void'];
                $grand_issued += $row['jumlah_issued'];
                $grand_posted += $row['jumlah_posted'];
                $grand_total += $jumlah_total;

                // Display bank and account number details under the entity
                echo "<tr>
                <td style='border: 1px solid black;font-weight:bold;padding-left:5px;'>" . $row['namabank'] . "</td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
            </tr>
            <tr>
                <td style='border: 1px solid black;padding-left:5px;'>" . $row['ac_number'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_unused'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_void'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_issued'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_posted'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>  $jumlah_total</td>
            </tr>";
            }

            // After the loop, print the last entity's total row
            echo "<tr>
            <td style='border: 1px solid black;font-weight:bold;padding-left:5px;'>TOTAL</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_unused . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_void . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_issued . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_posted . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_total . "</td>
        </tr>";

            // Add the Grand Total row
            echo "<tr>
            <td style='border: 2px solid black;background-color:#fff2cc;text-align:center;font-weight:bold'>Grand Total</td>
            <td style='border: 2px solid black;background-color:#fff2cc;text-align:center;font-weight:bold'>" . $grand_unused . "</td>
            <td style='border: 2px solid black;background-color:#fff2cc;text-align:center;font-weight:bold'>" . $grand_void . "</td>
            <td style='border: 2px solid black;background-color:#fff2cc;text-align:center;font-weight:bold'>" . $grand_issued . "</td>
            <td style='border: 2px solid black;background-color:#fff2cc;text-align:center;font-weight:bold'>" . $grand_posted . "</td>
            <td style='border: 2px solid black;background-color:#fff2cc;text-align:center;font-weight:bold'>" . $grand_total . "</td>
        </tr>";

            echo "</tbody></table></div>";
        } else {
            echo "<h2>Cek</h2>No results found.";
        }

        $result_giro = $conn->query($sql_giro);
        if ($result_giro->num_rows > 0) {
            echo "<div class='col'>";
            echo "<h5 style='padding-left:10px'>SUMMARY GIRO</h5>";
            echo "<table style='border: 1px solid black;margin:10px;width:900px'>
            <thead style='background-color:#c6e0b4 ;text-align:center;'>
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

            // Initialize grand total variables
            $grand_unused = 0;
            $grand_void = 0;
            $grand_issued = 0;
            $grand_posted = 0;
            $grand_total = 0;

            // Start looping over the rows
            $last_nama_entitas = ""; // To track when nama_entitas changes
            while ($row = $result_giro->fetch_assoc()) {
                // Check if nama_entitas has changed and reset the totals
                if ($row['nama_entitas'] != $last_nama_entitas) {
                    // If it's not the first entity, print the previous entity totals
                    if ($last_nama_entitas != "") {
                        // Print the total row for the previous entity (above the bank details)
                        echo "<tr>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;font-weight:bold;padding-left:5px;background-color:#e2efda;'>TOTAL</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#e2efda;'>" . $total_unused . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#e2efda;'>" . $total_void . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#e2efda;'>" . $total_issued . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#e2efda;'>" . $total_posted . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#e2efda;'>" . $total_total . "</td>
                    </tr>";
                    }

                    // Reset the entity totals for the new entity
                    $total_unused = 0;
                    $total_void = 0;
                    $total_issued = 0;
                    $total_posted = 0;
                    $total_total = 0;
                    $last_nama_entitas = $row['nama_entitas'];

                    // Display the new entity (with totals for this entity)
                    echo "<tr>
                    <td style='border: 1px solid black;font-weight:bold;padding-left:5px;background-color:#e2efda;'>" . $row['nama_entitas'] . "</td>
                    <td style='border: 1px solid black;text-align:center;background-color:#e2efda;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#e2efda;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#e2efda;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#e2efda;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#e2efda;'></td>
                </tr>";
                }

                // Accumulate values for the current entity
                $jumlah_total = $row['jumlah_unused'] + $row['jumlah_void'] + $row['jumlah_issued'] + $row['jumlah_posted'];

                $total_unused += $row['jumlah_unused'];
                $total_void += $row['jumlah_void'];
                $total_issued += $row['jumlah_issued'];
                $total_posted += $row['jumlah_posted'];
                $total_total += $jumlah_total;

                // Accumulate grand totals
                $grand_unused += $row['jumlah_unused'];
                $grand_void += $row['jumlah_void'];
                $grand_issued += $row['jumlah_issued'];
                $grand_posted += $row['jumlah_posted'];
                $grand_total += $jumlah_total;

                // Display bank and account number details under the entity
                echo "<tr>
                <td style='border: 1px solid black;font-weight:bold;padding-left:5px;'>" . $row['namabank'] . "</td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
            </tr>
            <tr>
                <td style='border: 1px solid black;padding-left:5px;'>" . $row['ac_number'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_unused'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_void'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_issued'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_posted'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>  $jumlah_total</td>
            </tr>";
            }

            // After the loop, print the last entity's total row
            echo "<tr>
            <td style='border: 1px solid black;font-weight:bold;padding-left:5px;'>TOTAL</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_unused . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_void . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_issued . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_posted . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_total . "</td>
        </tr>";

            // Add the Grand Total row
            echo "<tr>
            <td style='border: 2px solid black;background-color:#e2efda;text-align:center;font-weight:bold'>Grand Total</td>
            <td style='border: 2px solid black;background-color:#e2efda;text-align:center;font-weight:bold'>" . $grand_unused . "</td>
            <td style='border: 2px solid black;background-color:#e2efda;text-align:center;font-weight:bold'>" . $grand_void . "</td>
            <td style='border: 2px solid black;background-color:#e2efda;text-align:center;font-weight:bold'>" . $grand_issued . "</td>
            <td style='border: 2px solid black;background-color:#e2efda;text-align:center;font-weight:bold'>" . $grand_posted . "</td>
            <td style='border: 2px solid black;background-color:#e2efda;text-align:center;font-weight:bold'>" . $grand_total . "</td>
        </tr>";

            echo "</tbody></table>";
        } else {
            echo "<h2>GIRO</h2>No results found.";
        }

        $result_loa = $conn->query($sql_loa);
        if ($result_loa->num_rows > 0) {
            echo "<h5 style='padding-left:10px'>SUMMARY LOA</h5>";
            echo "<table style='border: 1px solid black;margin:10px;width:900px'>
            <thead style='background-color:#b4c6e7 ;text-align:center;'>
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

            // Initialize grand total variables
            $grand_unused = 0;
            $grand_void = 0;
            $grand_issued = 0;
            $grand_posted = 0;
            $grand_total = 0;

            // Start looping over the rows
            $last_nama_entitas = ""; // To track when nama_entitas changes
            while ($row = $result_loa->fetch_assoc()) {
                // Check if nama_entitas has changed and reset the totals
                if ($row['nama_entitas'] != $last_nama_entitas) {
                    // If it's not the first entity, print the previous entity totals
                    if ($last_nama_entitas != "") {
                        // Print the total row for the previous entity (above the bank details)
                        echo "<tr>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;font-weight:bold;padding-left:5px;background-color:#ddebf7;'>TOTAL</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#ddebf7;'>" . $total_unused . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#ddebf7;'>" . $total_void . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#ddebf7;'>" . $total_issued . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#ddebf7;'>" . $total_posted . "</td>
                        <td style='border: 1px solid black;border-bottom: 2px solid black;text-align:center;font-weight:bold;background-color:#ddebf7;'>" . $total_total . "</td>
                    </tr>";
                    }

                    // Reset the entity totals for the new entity
                    $total_unused = 0;
                    $total_void = 0;
                    $total_issued = 0;
                    $total_posted = 0;
                    $total_total = 0;
                    $last_nama_entitas = $row['nama_entitas'];

                    // Display the new entity (with totals for this entity)
                    echo "<tr>
                    <td style='border: 1px solid black;font-weight:bold;padding-left:5px;background-color:#ddebf7;'>" . $row['nama_entitas'] . "</td>
                    <td style='border: 1px solid black;text-align:center;background-color:#ddebf7;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#ddebf7;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#ddebf7;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#ddebf7;'></td>
                    <td style='border: 1px solid black;text-align:center;background-color:#ddebf7;'></td>
                </tr>";
                }

                // Accumulate values for the current entity
                $jumlah_total = $row['jumlah_unused'] + $row['jumlah_void'] + $row['jumlah_issued'] + $row['jumlah_posted'];

                $total_unused += $row['jumlah_unused'];
                $total_void += $row['jumlah_void'];
                $total_issued += $row['jumlah_issued'];
                $total_posted += $row['jumlah_posted'];
                $total_total += $jumlah_total;

                // Accumulate grand totals
                $grand_unused += $row['jumlah_unused'];
                $grand_void += $row['jumlah_void'];
                $grand_issued += $row['jumlah_issued'];
                $grand_posted += $row['jumlah_posted'];
                $grand_total += $jumlah_total;

                // Display bank and account number details under the entity
                echo "<tr>
                <td style='border: 1px solid black;font-weight:bold;padding-left:5px;'>" . $row['namabank'] . "</td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
                <td style='border: 1px solid black;'></td>
            </tr>
            <tr>
                <td style='border: 1px solid black;padding-left:5px;'>" . $row['ac_number'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_unused'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_void'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_issued'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>" . $row['jumlah_posted'] . "</td>
                <td style='border: 1px solid black;text-align:center;'>  $jumlah_total</td>
            </tr>";
            }

            // After the loop, print the last entity's total row
            echo "<tr>
            <td style='border: 1px solid black;font-weight:bold;padding-left:5px;'>TOTAL</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_unused . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_void . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_issued . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_posted . "</td>
            <td style='border: 1px solid black;text-align:center;font-weight:bold;'>" . $total_total . "</td>
        </tr>";

            // Add the Grand Total row
            echo "<tr>
            <td style='border: 2px solid black;background-color:#ddebf7;text-align:center;font-weight:bold'>Grand Total</td>
            <td style='border: 2px solid black;background-color:#ddebf7;text-align:center;font-weight:bold'>" . $grand_unused . "</td>
            <td style='border: 2px solid black;background-color:#ddebf7;text-align:center;font-weight:bold'>" . $grand_void . "</td>
            <td style='border: 2px solid black;background-color:#ddebf7;text-align:center;font-weight:bold'>" . $grand_issued . "</td>
            <td style='border: 2px solid black;background-color:#ddebf7;text-align:center;font-weight:bold'>" . $grand_posted . "</td>
            <td style='border: 2px solid black;background-color:#ddebf7;text-align:center;font-weight:bold'>" . $grand_total . "</td>
        </tr>";

            echo "</tbody></table></div>";
        } else {
            echo "<h2>LOA</h2>No results found.";
        }

        echo "</div>";


        // Close the database connection
        $conn->close();
        ?>
    </div>
</div>