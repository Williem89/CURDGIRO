    <?php
    include 'koneksi.php';
    session_start(); // Start the session to access user information

    // Get the selected month and year from the GET request, or set default values
    $selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n'); // Default to current month
    $selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Default to current year
    $data = json_decode(file_get_contents('php://input'), true);

    // Assuming the user's information is stored in session
    $user_logged_in = $_SESSION['username']; // Adjust this based on your session variable

    // Get the JSON data from the request
    $data = json_decode(file_get_contents('php://input'), true);



    // Get the search term from the GET request
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Prepare the statement
    $sql = "SELECT e.nama_entitas, d.namabank, d.ac_number,dg.StatGiro, dg.nogiro, SUM(dg.Nominal) AS total_nominal, 
                dg.tanggal_jatuh_tempo, dg.TglVoid 
            FROM detail_giro AS dg
            INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
            INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
            WHERE dg.StatGiro != 'Posted' 
            AND MONTH(dg.tanggal_jatuh_tempo) = ? 
            AND YEAR(dg.tanggal_jatuh_tempo) = ? 
            AND (dg.nogiro LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?) 
            GROUP BY dg.tanggal_jatuh_tempo, e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.TglVoid
            ORDER BY dg.tanggal_jatuh_tempo ASC;";

    $stmt = $conn->prepare($sql);

    // Check if preparation was successful
    if ($stmt === false) {
        die("Preparation failed: " . $conn->error);
    }

    // Bind parameters
    $search_like = '%' . $search_term . '%';
    $stmt->bind_param("iisss", $selected_month, $selected_year, $search_like, $search_like, $search_like);

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to hold Void giro records
    $Void_giro_records = [];
    while ($row = $result->fetch_assoc()) {
        $Void_giro_records[] = $row;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daftar Giro Void</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <style>
            body {
                background-color: #f8f9fa;
                padding: 30px;
            }
            h1 {
                margin-bottom: 20px;
                color: #0056b3;
            }
            table {
                margin-top: 20px;
                border: 1px solid #dee2e6;
            }
            th {
                background-color: #007bff;
                color: white;
            }
            td {
                background-color: white;
            }
            .no-data {
                text-align: center;
                font-style: italic;
                color: #6c757d;
            }
            .group-header {
                font-weight: bold;
                background-color: #e9ecef;
            }
            .subtotal {
                font-weight: bold;
                background-color: #d1ecf1;
            }
            .grand-total {
                font-weight: bold;
                background-color: #c3e6cb;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="text-center">Daftar Giro Issued</h1>
            
            <!-- Search Form -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro, Entitas, atau Bank" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <select name="month" class="form-select">
                            <option value="">Pilih Bulan</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col">
                        <select name="year" class="form-select">
                            <option value="">Pilih Tahun</option>
                            <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($y == $selected_year) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </form>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Entitas</th>
                        <th>No Giro</th>
                        <th>Status</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Tanggal Giro Cair</th>
                        <th>Bank</th>
                        <th>No. Rekening</th>
                        <th>Nominal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($Void_giro_records)): ?>
                    <tr>
                        <td colspan="7" class="no-data">Tidak ada data giro.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $current_entity = '';
                    $current_bank = '';
                    $subtotal = 0;
                    $grand_total = 0;

                    foreach ($Void_giro_records as $giro): 
                        // Update subtotal and grand total
                        $subtotal += $giro['total_nominal'];
                        $grand_total += $giro['total_nominal'];

                        // Check if we need to output a new entity
                        if ($current_entity !== $giro['nama_entitas']) {
                            // Output subtotal for the previous entity
                            if ($current_entity !== '') {
                                echo '<tr class="subtotal"><td colspan="6">Subtotal</td><td>' . number_format($subtotal, 2, ',', '.') . '</td></tr>';
                            }

                            // Reset subtotal for new entity
                            $subtotal = $giro['total_nominal'];
                            $current_entity = $giro['nama_entitas'];

                            echo '<tr class="group-header"><td colspan="7">' . htmlspecialchars($current_entity) . '</td></tr>';
                        }

                        // Check if we need to output a new bank
                        if ($current_bank !== $giro['namabank']) {
                            $current_bank = $giro['namabank'];
                            echo '<tr class="group-header"><td colspan="7">' . htmlspecialchars($current_bank) . '</td></tr>';
                        }
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($giro['nama_entitas']); ?></td>
                            <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                            <td><?php echo htmlspecialchars($giro['StatGiro']); ?></td>
                            <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                            <td><?php echo htmlspecialchars($giro['TglVoid']); ?></td>
                            <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                            <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                            <td><?php echo number_format($giro['total_nominal'], 2, ',', '.'); ?></td>
                            <td <?php echo $giro['StatGiro'] == "Posted" ? "hidden" : ""; ?>>    
                                <input type="date" id="tanggal_cair_giro" style="display:none;">
                                <button class="btn btn-sm btn-primary cair-btn" <?php echo $giro['StatGiro'] == "Void" ? "disabled" : ""; ?> 
                                        data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                        data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-send-check"></i>
                                </button>
                                <button class="btn btn-sm btn-info return-btn" id="return-btn"  <?php echo $giro['StatGiro'] == "Issued" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-backspace"></i></button>
                                <button class="btn btn-sm btn-danger void-btn" id="void-btn" <?php echo $giro['StatGiro'] == "Void" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-x-circle"></i></button>
                        
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Output subtotal for the last entity -->
                    <tr class="subtotal"><td colspan="6">Subtotal</td><td><?php echo number_format($subtotal, 2, ',', '.'); ?></td></tr>
                    <tr class="grand-total"><td colspan="6">Grand Total</td><td><?php echo number_format($grand_total, 2, ',', '.'); ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.cair-btn').forEach(button => {button.addEventListener
                ('click', async () => {
                    const nogiro = button.getAttribute('data-nogiro');
                    const entitas = button.getAttribute('data-entitas');
                    
                    const { value: date } = await Swal.fire({
                        title: "Tanggal Cair",
                        input: "date",
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    });

                    if (date) {
                        // Perform AJAX request to update StatGiro to "Posted"
                        fetch('update_statgiro.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                nogiro: nogiro,
                                //PostedBy: user,
                                tanggal: date,
                                statgiro: 'Posted',
                                action: "cairgiro"
                            })
                        }
                    )
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Giro Berhasil di Posting");
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire("Error", "An error occurred while updating.", "error");
                        });
                    }
                });
            });


        document.querySelectorAll('.return-btn').forEach(button => {button.addEventListener
                ('click', async () => {
                    const nogiro = button.getAttribute('data-nogiro');
                    const entitas = button.getAttribute('data-entitas');
                    
                    const { value: date } = await Swal.fire({
                        title: "Tanggal Return",
                        input: "date",
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    });

                    if (date) {
                        // Perform AJAX request to update StatGiro to "Posted"
                        fetch('update_statgiro.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                nogiro: nogiro,
                                tanggal: date,
                                statgiro: 'Return',
                                action: "returngiro"
                            })
                        }
                    )
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Giro Sudah tercatat kembali ke Bank");
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire("Error", "An error occurred while updating.", "error");
                        });
                    }
                });
            });

        document.querySelectorAll('.void-btn').forEach(button => {button.addEventListener
                ('click', async () => {
                    const nogiro = button.getAttribute('data-nogiro');
                    const entitas = button.getAttribute('data-entitas');
                    
                    const { value: date } = await Swal.fire({
                        title: "Tanggal Void",
                        input: "date",
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    });

                    if (date) {
                        // Perform AJAX request to update StatGiro to "Posted"
                        fetch('update_statgiro.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                nogiro: nogiro,
                                tanggal: date,
                                statgiro: 'Void',
                                action: "voidgiro"
                            })
                        }
                    )
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Giro Void");
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire("Error", "An error occurred while updating.", "error");
                        });
                    }
                });
            });
    </script>
    </body>
    </html>
