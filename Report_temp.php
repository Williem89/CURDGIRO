<?php
include 'koneksi.php'; // Pastikan Anda memiliki file koneksi

// Query untuk menggabungkan tabel data_giro dan detail_giro
$sql = "SELECT dg.nogiro, 
               dg.namabank, 
               dg.ac_number, 
               dg.ac_name, 
               d.tanggal_giro, 
               d.tanggal_jatuh_tempo, 
               d.Nominal, 
               d.nama_penerima, 
               d.bank_penerima, 
               d.ac_penerima, 
               d.statgiro, 
               d.keterangan, 
               d.tanggal_cair_giro
        FROM detail_giro d
        JOIN data_giro dg ON d.nogiro = dg.nogiro";

// Siapkan statement
$stmt = $conn->prepare($sql);

// Eksekusi statement
$stmt->execute();

// Ambil hasil
$result = $stmt->get_result();

// Periksa dan tampilkan data
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<thead>
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
                <th>Status Giro</th>
                <th>Keterangan</th>
                <th>Tanggal Cair Giro</th>
            </tr>
          </thead>
          <tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nogiro']) . "</td>";
        echo "<td>" . htmlspecialchars($row['namabank']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ac_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ac_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tanggal_giro']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tanggal_jatuh_tempo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Nominal']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_penerima']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bank_penerima']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ac_penerima']) . "</td>";
        echo "<td>" . htmlspecialchars($row['statgiro']) . "</td>";
        echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tanggal_cair_giro']) . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
} else {
    echo "Tidak ada data ditemukan.";
}

// Tutup statement dan koneksi
$stmt->close();
$conn->close();
?>
