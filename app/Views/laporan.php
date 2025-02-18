<?php
require_once(ROOTPATH . 'Vendor/autoload.php'); // Sesuaikan path jika perlu

// Buat objek PDF
$pdf = new TCPDF();
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);


    // Menampilkan data dalam format vertikal
    $pdf->Cell(0, 10, 'Username: ' . $user->username, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Email: ' . $user->email, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Tanggal Daftar: ' . $user->tanggal_daftar, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Jam Daftar: ' . $user->jam_daftar, 0, 1, 'L');


// Output PDF langsung ke browser
$pdf->Output('data_user.pdf', 'I');

header('Content-Type: application/pdf');

exit;
?>

<?php
require_once(ROOTPATH . 'Vendor/autoload.php'); // Sesuaikan path jika perlu

// Buat objek PDF
$pdf = new TCPDF();
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Pastikan variabel $user valid
if (isset($user) && is_object($user)) {
    // Buat tabel HTML
    $html = '
    <h2>Data User</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th><b>Field</b></th>
            <th><b>Value</b></th>
        </tr>
        <tr>
            <td>Username</td>
            <td>' . htmlspecialchars($user->username) . '</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>' . htmlspecialchars($user->email) . '</td>
        </tr>
        <tr>
            <td>Tanggal Daftar</td>
            <td>' . htmlspecialchars($user->tanggal_daftar) . '</td>
        </tr>
        <tr>
            <td>Jam Daftar</td>
            <td>' . htmlspecialchars($user->jam_daftar) . '</td>
        </tr>
    </table>';

    // Tambahkan HTML ke PDF
    $pdf->writeHTML($html, true, false, true, false, '');
} else {
    $pdf->Cell(0, 10, 'User data not found.', 0, 1, 'C');
}

// Output PDF langsung ke browser
$pdf->Output('data_user.pdf', 'I');

exit;
?>
