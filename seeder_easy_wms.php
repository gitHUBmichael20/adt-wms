<?php
/**
 * ============================================================
 *  SEEDER DUMMY DATA - EASY WMS
 * ============================================================
 * Cara pakai:
 * 1. Taruh file ini di root folder project easy-wms-master
 *    (sejajar dengan folder application/system, atau di mana saja
 *    asal koneksi DB di bawah ini disesuaikan).
 * 2. Pastikan MySQL/MariaDB nyala dan database `easy_wms` sudah
 *    ada (import easy_wms.sql dulu jika belum).
 * 3. Buka file ini lewat browser, misal:
 *    http://localhost/seeder_easy_wms.php
 *    atau jalankan lewat CLI: php seeder_easy_wms.php
 * 4. Script ini akan MENGHAPUS data lama di tabel:
 *    barang, barang_masuk, barang_masuk_detail,
 *    barang_keluar, barang_keluar_detail, supplier, penerima
 *    lalu mengisi ulang dengan data dummy.
 *    Tabel user & satuan TIDAK dihapus/diubah.
 * ============================================================
 */

// ------------------------------------------------------------
// KONFIGURASI KONEKSI DATABASE (samakan dgn application/config/database.php)
// ------------------------------------------------------------
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'easy_wms';

// ------------------------------------------------------------
// mulai
// ------------------------------------------------------------
header('Content-Type: text/html; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$log = [];
function logmsg(&$log, $msg, $ok = true) {
    $log[] = ['ok' => $ok, 'msg' => $msg];
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Seeder Easy WMS</title>";
echo "<style>
body{font-family:Segoe UI,Arial,sans-serif;background:#0f172a;color:#e2e8f0;padding:30px;line-height:1.5}
h1{color:#38bdf8}
.box{background:#1e293b;border-radius:10px;padding:20px 25px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.3)}
.ok{color:#4ade80}
.err{color:#f87171}
table{border-collapse:collapse;width:100%;margin-top:10px}
th,td{border:1px solid #334155;padding:6px 10px;font-size:14px;text-align:left}
th{background:#334155}
tr:nth-child(even){background:#1a2436}
code{background:#334155;padding:2px 6px;border-radius:4px}
</style></head><body>";
echo "<h1>🌱 Seeder Dummy Data - Easy WMS</h1>";

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
    logmsg($log, "Berhasil konek ke database <code>{$DB_NAME}</code>");

    $conn->begin_transaction();
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // ------------------------------------------------------------
    // 1. BERSIHKAN TABEL TERKAIT
    // ------------------------------------------------------------
    $truncateTables = [
        'barang_keluar_detail',
        'barang_keluar',
        'barang_masuk_detail',
        'barang_masuk',
        'barang',
        'supplier',
        'penerima',
    ];
    foreach ($truncateTables as $t) {
        $conn->query("TRUNCATE TABLE `$t`");
    }
    logmsg($log, "Tabel lama dikosongkan: " . implode(', ', $truncateTables));

    // ------------------------------------------------------------
    // 2. PASTIKAN SATUAN "unit" ADA
    // ------------------------------------------------------------
    $res = $conn->query("SELECT id FROM satuan WHERE nama = 'unit' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $id_satuan = $res->fetch_assoc()['id'];
    } else {
        $conn->query("INSERT INTO satuan (nama, status) VALUES ('unit', 'valid')");
        $id_satuan = $conn->insert_id;
    }
    logmsg($log, "Satuan 'unit' siap dipakai (id: $id_satuan)");

    // ------------------------------------------------------------
    // 3. PASTIKAN ADA MINIMAL 1 USER (untuk id_user transaksi)
    // ------------------------------------------------------------
    $res = $conn->query("SELECT id FROM user ORDER BY id ASC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $id_user = $res->fetch_assoc()['id'];
        logmsg($log, "Menggunakan user existing (id: $id_user) untuk transaksi");
    } else {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (nama, email, password, telefon, ktp, role, status) VALUES (?,?,?,?,?,?,?)");
        $nama = 'Admin Seeder'; $email = 'admin@easywms.local'; $telp = '081200000000';
        $ktp = '1234567890000001'; $role = 'admin'; $status = 'aktif';
        $stmt->bind_param('sssssss', $nama, $email, $hash, $telp, $ktp, $role, $status);
        $stmt->execute();
        $id_user = $stmt->insert_id;
        logmsg($log, "Tidak ada user, membuat user admin baru (id: $id_user, email: $email, password: admin123)");
    }

    // ------------------------------------------------------------
    // 4. SUPPLIER (nama perusahaan, detail dikarang)
    // ------------------------------------------------------------
    $suppliers = [
        ['PT Sumber Makmur Teknologi', 'purchasing@sumbermakmur.co.id', '0217654321', 'Jl. Gatot Subroto No. 45, Jakarta Selatan', '01.234.567.8-901.000'],
        ['CV Anugerah Elektronik', 'sales@anugerahelektronik.com', '0227712345', 'Jl. Soekarno Hatta No. 88, Bandung', '02.345.678.9-012.000'],
        ['PT Mitra Digital Nusantara', 'info@mitradigitalnusantara.id', '0315566778', 'Jl. Raya Darmo No. 12, Surabaya', '03.456.789.0-123.000'],
        ['CV Karya Prima Office', 'cs@karyaprimaoffice.co.id', '0246633221', 'Jl. Pandanaran No. 21, Semarang', '04.567.890.1-234.000'],
        ['PT Global Cipta Solusi', 'admin@globalciptasolusi.com', '0611234567', 'Jl. Sisingamangaraja No. 9, Medan', '05.678.901.2-345.000'],
    ];
    $stmt = $conn->prepare("INSERT INTO supplier (nama, email, telefon, alamat, npwp, status) VALUES (?,?,?,?,?, 'aktif')");
    $supplierIds = [];
    foreach ($suppliers as $s) {
        $stmt->bind_param('sssss', $s[0], $s[1], $s[2], $s[3], $s[4]);
        $stmt->execute();
        $supplierIds[] = $stmt->insert_id;
    }
    logmsg($log, "Berhasil menambahkan " . count($suppliers) . " supplier");

    // ------------------------------------------------------------
    // 5. PENERIMA (nama perusahaan, detail dikarang)
    // ------------------------------------------------------------
    $penerimaList = [
        ['PT Maju Bersama', 'Purchasing', '081298765432', 'Jl. MH Thamrin No. 10, Jakarta Pusat'],
        ['CV Sukses Abadi', 'Logistik', '081311122233', 'Jl. Ahmad Yani No. 55, Bekasi'],
        ['PT Cahaya Media', 'Umum & GA', '081444455566', 'Jl. Diponegoro No. 30, Bandung'],
        ['Toko Berkah Jaya', 'Operasional', '081555566677', 'Jl. Veteran No. 7, Malang'],
        ['PT Nusantara Logistik', 'Warehouse', '081666677788', 'Jl. Yos Sudarso No. 18, Surabaya'],
    ];
    $stmt = $conn->prepare("INSERT INTO penerima (nama, divisi, telefon, alamat, status) VALUES (?,?,?,?, 'aktif')");
    $penerimaIds = [];
    foreach ($penerimaList as $p) {
        $stmt->bind_param('ssss', $p[0], $p[1], $p[2], $p[3]);
        $stmt->execute();
        $penerimaIds[] = $stmt->insert_id;
    }
    logmsg($log, "Berhasil menambahkan " . count($penerimaList) . " penerima");

    // ------------------------------------------------------------
    // 6. PRODUK PRINTER (barang) - qty awal 0, nanti diisi via barang_masuk
    // ------------------------------------------------------------
    $produk = [
        // nama, harga, kena_pajak, index supplier (0-4)
        ['Canon PIXMA G3020 All-in-One',      2850000, 1, 0],
        ['Epson L3250 EcoTank Printer',       2650000, 1, 1],
        ['HP DeskJet 2820e All-in-One',       1950000, 1, 2],
        ['Brother DCP-T420W Ink Tank',        2400000, 1, 0],
        ['Canon imageCLASS LBP2900 Laser',    1750000, 0, 3],
        ['Epson L120 Ink Tank Printer',       1850000, 1, 1],
        ['HP LaserJet Pro M15w',              2100000, 0, 4],
        ['Brother HL-L2321D Laser Mono',      2250000, 0, 2],
        ['Epson EcoTank L3210 Printer',       2550000, 1, 3],
        ['Canon PIXMA G2020 Ink Tank',        2300000, 1, 4],
    ];
    $stmt = $conn->prepare("INSERT INTO barang (id_supplier, nama, qty, id_satuan, harga, kena_pajak) VALUES (?,?,0,?,?,?)");
    $barangIds = [];
    foreach ($produk as $i => $p) {
        $id_supplier = $supplierIds[$p[3]];
        $nama = $p[0]; $harga = $p[1]; $pajak = $p[2];
        $stmt->bind_param('isiii', $id_supplier, $nama, $id_satuan, $harga, $pajak);
        $stmt->execute();
        $barangIds[] = $stmt->insert_id;
    }
    logmsg($log, "Berhasil menambahkan " . count($produk) . " produk printer");

    // index barangIds sesuai urutan $produk (0..9)
    // Produk yang akan DIKOSONGKAN (masuk habis dikeluarkan semua): index 4, 6, 8
    $emptyTargets = [4, 6, 8];

    // ------------------------------------------------------------
    // 7. BARANG MASUK (transaksi pembelian masuk gudang)
    // ------------------------------------------------------------
    // format: [id_supplier_index, [ [produk_index, qty], ... ] ]
    $masukPlan = [
        [0, [[0, 20], [1, 15]]],
        [1, [[2, 10], [4, 8]]],
        [2, [[3, 12], [6, 10]]],
        [3, [[5, 15], [7, 10], [8, 6], [9, 8]]],
    ];

    $stmtHeader = $conn->prepare("INSERT INTO barang_masuk (id_user, id_supplier, waktu) VALUES (?,?,?)");
    $stmtDetail = $conn->prepare("INSERT INTO barang_masuk_detail (id_barang_masuk, id_barang, qty, subtotal) VALUES (?,?,?,?)");

    $countMasuk = 0;
    foreach ($masukPlan as $m) {
        $id_supplier = $supplierIds[$m[0]];
        $waktu = date('Y-m-d H:i:s', strtotime('-' . rand(2, 20) . ' days'));
        $stmtHeader->bind_param('iis', $id_user, $id_supplier, $waktu);
        $stmtHeader->execute();
        $id_barang_masuk = $stmtHeader->insert_id;
        $countMasuk++;

        foreach ($m[1] as $item) {
            $id_barang = $barangIds[$item[0]];
            $qty = $item[1];
            $harga = $produk[$item[0]][1];
            $subtotal = (int) round($qty * $harga * 1.11); // termasuk pajak 11%, sama seperti logic keranjang_masuk
            $stmtDetail->bind_param('iiii', $id_barang_masuk, $id_barang, $qty, $subtotal);
            $stmtDetail->execute();
        }
    }
    logmsg($log, "Berhasil menambahkan $countMasuk transaksi barang masuk");

    // ------------------------------------------------------------
    // 8. BARANG KELUAR (transaksi pengeluaran barang)
    // ------------------------------------------------------------
    // format: [id_penerima_index, no_po, keterangan, [ [produk_index, qty], ... ] ]
    $keluarPlan = [
        [0, 'PO-2026-0011', 'Pengiriman kebutuhan kantor cabang Jakarta', [[0, 5], [1, 4]]],
        [1, 'PO-2026-0012', 'Permintaan stok gudang Bekasi',              [[2, 3], [4, 8]]], // produk 4 habis
        [2, 'PO-2026-0013', 'Pengadaan printer unit operasional Bandung', [[3, 4], [6, 10]]], // produk 6 habis
        [3, 'PO-2026-0014', 'Distribusi rutin bulanan',                   [[5, 5], [8, 6], [9, 3]]], // produk 8 habis
    ];

    $stmtHeaderK = $conn->prepare("INSERT INTO barang_keluar (id_user, id_penerima, no_po, keterangan, waktu) VALUES (?,?,?,?,?)");
    $stmtDetailK = $conn->prepare("INSERT INTO barang_keluar_detail (id_barang_keluar, id_barang, qty, serial_number) VALUES (?,?,?,?)");

    $countKeluar = 0;
    foreach ($keluarPlan as $k) {
        $id_penerima = $penerimaIds[$k[0]];
        $no_po = $k[1];
        $keterangan = $k[2];
        $waktu = date('Y-m-d H:i:s', strtotime('-' . rand(0, 10) . ' days'));
        $stmtHeaderK->bind_param('iisss', $id_user, $id_penerima, $no_po, $keterangan, $waktu);
        $stmtHeaderK->execute();
        $id_barang_keluar = $stmtHeaderK->insert_id;
        $countKeluar++;

        foreach ($k[3] as $item) {
            $id_barang = $barangIds[$item[0]];
            $qty = $item[1];
            $serial = 'SN-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $stmtDetailK->bind_param('iiis', $id_barang_keluar, $id_barang, $qty, $serial);
            $stmtDetailK->execute();
        }
    }
    logmsg($log, "Berhasil menambahkan $countKeluar transaksi barang keluar");

    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    $conn->commit();
    logmsg($log, "✅ SEMUA DATA DUMMY BERHASIL DIMASUKKAN!");

    // ------------------------------------------------------------
    // TAMPILKAN RINGKASAN STOK AKHIR
    // ------------------------------------------------------------
    echo "<div class='box'>";
    foreach ($log as $l) {
        echo "<div class='" . ($l['ok'] ? 'ok' : 'err') . "'>" . ($l['ok'] ? '✔ ' : '✘ ') . $l['msg'] . "</div>";
    }
    echo "</div>";

    $res = $conn->query("SELECT b.id, b.nama, b.qty, s.nama AS supplier, b.harga, b.kena_pajak FROM barang b LEFT JOIN supplier s ON s.id = b.id_supplier ORDER BY b.id");
    echo "<div class='box'><h3>📦 Ringkasan Stok Produk Printer</h3><table>";
    echo "<tr><th>ID</th><th>Nama Produk</th><th>Supplier</th><th>Harga</th><th>Kena Pajak</th><th>Qty Akhir</th></tr>";
    while ($row = $res->fetch_assoc()) {
        $qtyStyle = $row['qty'] == 0 ? "style='color:#f87171;font-weight:bold'" : "style='color:#4ade80;font-weight:bold'";
        echo "<tr><td>{$row['id']}</td><td>{$row['nama']}</td><td>{$row['supplier']}</td><td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td><td>" . ($row['kena_pajak'] ? 'Ya' : 'Tidak') . "</td><td $qtyStyle>{$row['qty']}" . ($row['qty'] == 0 ? ' (KOSONG)' : '') . "</td></tr>";
    }
    echo "</table></div>";

    echo "<div class='box'><h3>🏢 Supplier</h3><table><tr><th>ID</th><th>Nama</th><th>Email</th><th>Telefon</th><th>Alamat</th></tr>";
    $res = $conn->query("SELECT * FROM supplier ORDER BY id");
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['nama']}</td><td>{$row['email']}</td><td>{$row['telefon']}</td><td>{$row['alamat']}</td></tr>";
    }
    echo "</table></div>";

    echo "<div class='box'><h3>🏬 Penerima</h3><table><tr><th>ID</th><th>Nama</th><th>Divisi</th><th>Telefon</th><th>Alamat</th></tr>";
    $res = $conn->query("SELECT * FROM penerima ORDER BY id");
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['nama']}</td><td>{$row['divisi']}</td><td>{$row['telefon']}</td><td>{$row['alamat']}</td></tr>";
    }
    echo "</table></div>";

    echo "<div class='box'><h3>⬇️ Barang Masuk</h3><table><tr><th>ID</th><th>Supplier</th><th>Waktu</th><th>Total Harga</th></tr>";
    $res = $conn->query("SELECT bm.id, s.nama AS supplier, bm.waktu, bm.total_harga FROM barang_masuk bm LEFT JOIN supplier s ON s.id = bm.id_supplier ORDER BY bm.id");
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['supplier']}</td><td>{$row['waktu']}</td><td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td></tr>";
    }
    echo "</table></div>";

    echo "<div class='box'><h3>⬆️ Barang Keluar</h3><table><tr><th>ID</th><th>Penerima</th><th>No. PO</th><th>Keterangan</th><th>Waktu</th></tr>";
    $res = $conn->query("SELECT bk.id, p.nama AS penerima, bk.no_po, bk.keterangan, bk.waktu FROM barang_keluar bk LEFT JOIN penerima p ON p.id = bk.id_penerima ORDER BY bk.id");
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['penerima']}</td><td>{$row['no_po']}</td><td>{$row['keterangan']}</td><td>{$row['waktu']}</td></tr>";
    }
    echo "</table></div>";

    $conn->close();

} catch (Throwable $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }
    echo "<div class='box'><h3 class='err'>❌ Terjadi Kesalahan</h3>";
    echo "<div class='err'>" . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p>Semua perubahan sudah di-rollback (tidak ada data setengah jadi). Cek kredensial database di bagian atas file (\$DB_HOST, \$DB_USER, \$DB_PASS, \$DB_NAME) lalu coba lagi.</p>";
    echo "</div>";
}

echo "</body></html>";
