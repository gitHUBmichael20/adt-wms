<?php
// Subtotal dari DB sudah termasuk PPN 11% (harga * qty * 1.11)
// Breakdown: harga_pokok = subtotal / 1.11, ppn = subtotal - harga_pokok
$total_incl_ppn = array_sum(array_column($list_barang, 'subtotal'));
$harga_pokok    = round($total_incl_ppn / 1.11);
$ppn            = $total_incl_ppn - $harga_pokok;
?>

<style>
/* ===== PRINT STYLES ===== */
@media print {
    body * { visibility: hidden !important; }
    #invoice-area, #invoice-area * { visibility: visible !important; }
    #invoice-area { position: fixed; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
    .invoice-wrapper { box-shadow: none !important; border: none !important; }
    @page { size: A4; margin: 10mm 15mm; }
}

/* ===== INVOICE STYLES ===== */
.invoice-wrapper {
    background: #fff;
    max-width: 860px;
    margin: 0 auto;
    padding: 0;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    border-radius: 4px;
    overflow: hidden;
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 13px;
    color: #222;
}

.inv-header-band {
    background: #1565c0;
    color: #fff;
    padding: 22px 32px 14px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}
.inv-company-name {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: .5px;
    line-height: 1.2;
}
.inv-company-sub {
    font-size: 11px;
    opacity: .85;
    margin-top: 3px;
}
.inv-logo-box {
    background: #fff;
    border-radius: 6px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.inv-logo-box span {
    font-size: 20px;
    font-weight: 800;
    color: #1565c0;
    letter-spacing: 1px;
}

.inv-title-bar {
    background: #e3f0ff;
    text-align: center;
    padding: 8px;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #1565c0;
    border-bottom: 2px solid #1565c0;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.inv-body { padding: 20px 32px 24px; }

.inv-meta-grid {
    display: flex;
    gap: 24px;
    margin-bottom: 18px;
}
.inv-meta-left { flex: 1; }
.inv-meta-right {
    border: 1.5px solid #1565c0;
    border-radius: 4px;
    padding: 10px 16px;
    min-width: 280px;
    background: #f5f9ff;
}
.inv-meta-right .meta-title {
    font-weight: 700;
    font-size: 13px;
    margin-bottom: 6px;
    color: #1565c0;
}

/* Supplier info box */
.inv-supplier-box {
    border: 1.5px solid #2e7d32;
    border-radius: 4px;
    padding: 10px 16px;
    background: #f1f8e9;
    margin-bottom: 16px;
}
.inv-supplier-box .sup-title {
    font-weight: 700;
    font-size: 13px;
    color: #2e7d32;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.inv-supplier-box .sup-title i { font-size: 12px; }

.meta-table td { padding: 2px 4px; font-size: 12.5px; }
.meta-table td:first-child { color: #555; min-width: 120px; }
.meta-table td:nth-child(2) { padding: 2px 6px; color: #555; }

.inv-berikut { font-size: 12.5px; margin-bottom: 4px; }

.inv-items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    font-size: 12.5px;
}
.inv-items-table thead tr {
    background: #1565c0;
    color: #fff;
}
.inv-items-table thead th {
    padding: 8px 10px;
    font-weight: 600;
    border: 1px solid #1565c0;
}
.inv-items-table tbody td {
    padding: 7px 10px;
    border: 1px solid #d0d7e6;
    vertical-align: middle;
}
.inv-items-table tbody tr:nth-child(even) { background: #f7f9ff; }
.inv-items-table .text-right { text-align: right; }
.inv-items-table .text-center { text-align: center; }

.inv-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    font-size: 12.5px;
    border-bottom: 1px solid #eee;
}
.inv-summary-row:last-child { border-bottom: none; }
.inv-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0 4px;
    font-size: 14px;
    font-weight: 700;
    border-top: 2px solid #1565c0;
    margin-top: 4px;
    color: #1565c0;
}
.inv-summary-box {
    background: #f5f9ff;
    border: 1.5px solid #d0d7e6;
    border-radius: 4px;
    padding: 10px 16px;
    min-width: 300px;
}

.inv-terbilang {
    font-style: italic;
    font-size: 12px;
    color: #555;
    border-left: 3px solid #1565c0;
    padding: 6px 10px;
    background: #f5f9ff;
    margin: 12px 0 18px;
}

.inv-perhatian {
    font-size: 12px;
    margin-bottom: 18px;
}
.inv-perhatian strong { display: block; margin-bottom: 4px; }
.inv-perhatian ul { margin: 0; padding-left: 18px; }
.inv-perhatian ul li { margin-bottom: 4px; color: #444; }

.inv-signature-grid {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    align-items: flex-end;
}
.inv-sig-box {
    text-align: center;
    font-size: 12.5px;
}
.inv-sig-box .sig-name {
    font-weight: 700;
    text-decoration: underline;
    margin-top: 0;
}
.inv-sig-box .sig-title { color: #555; }
.inv-sig-space { height: 56px; }

.inv-footer-band {
    background: #f0f4fa;
    border-top: 2px solid #1565c0;
    padding: 10px 32px;
    display: flex;
    justify-content: space-between;
    font-size: 11.5px;
    color: #555;
    gap: 24px;
}
.inv-footer-band .fc { display: flex; flex-direction: column; gap: 2px; }
.inv-footer-band .fc-title { font-weight: 700; color: #1565c0; margin-bottom: 2px; font-size: 12px; }
</style>

<div class="container-fluid">
    <?php $this->load->view('layouts/_alert') ?>

    <div class="no-print mb-3 d-flex justify-content-between align-items-center">
        <a href="<?= base_url('inputs') ?>" class="btn btn-primary btn-rounded text-white">
            <i class="fas fa-angle-left"></i> List Barang Masuk
        </a>
        <button class="btn btn-success btn-rounded" onclick="printDiv('invoice-area')">
            <i class="fas fa-print"></i> Cetak Invoice
        </button>
    </div>

    <div id="invoice-area">
        <div class="invoice-wrapper">

            <!-- Header -->
            <div class="inv-header-band">
                <div>
                    <div class="inv-company-name">PT FIRNAS DIGITAL INDONESIA</div>
                    <div class="inv-company-sub">Grand Galaxy City Jl. Boulevard Raya Blok RSN-2 No.18 Jaka Setia, Bekasi, 17147</div>
                </div>
                <div class="inv-logo-box">
                    <span>FDI</span>
                </div>
            </div>

            <!-- Title -->
            <div class="inv-title-bar">INVOICE BARANG MASUK</div>

            <!-- Body -->
            <div class="inv-body">

                <!-- Supplier Info Box -->
                <?php if (!empty($barang_masuk->nama_supplier)) : ?>
                <div class="inv-supplier-box">
                    <div class="sup-title">
                        <i class="fas fa-truck"></i> Supplier / Pemasok Barang
                    </div>
                    <table class="meta-table">
                        <tr>
                            <td>Nama Supplier</td>
                            <td>:</td>
                            <td><strong><?= htmlspecialchars($barang_masuk->nama_supplier) ?></strong></td>
                        </tr>
                        <?php if (!empty($barang_masuk->telefon_supplier)) : ?>
                        <tr>
                            <td>Telepon</td>
                            <td>:</td>
                            <td><?= htmlspecialchars($barang_masuk->telefon_supplier) ?></td>
                        </tr>
                        <?php endif ?>
                        <?php if (!empty($barang_masuk->email_supplier)) : ?>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td><?= htmlspecialchars($barang_masuk->email_supplier) ?></td>
                        </tr>
                        <?php endif ?>
                        <?php if (!empty($barang_masuk->alamat_supplier)) : ?>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td><?= htmlspecialchars($barang_masuk->alamat_supplier) ?></td>
                        </tr>
                        <?php endif ?>
                    </table>
                </div>
                <?php else : ?>
                <div class="alert alert-warning no-print" style="font-size:12px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Data supplier tidak tersedia untuk transaksi ini (transaksi lama sebelum fitur supplier ditambahkan).
                </div>
                <?php endif ?>

                <!-- Meta info -->
                <div class="inv-meta-grid">
                    <div class="inv-meta-left">
                        <table class="meta-table">
                            <tr>
                                <td>Nomor Pemasukan</td>
                                <td>:</td>
                                <td><strong><?= $barang_masuk->id_barang_masuk ?></strong></td>
                            </tr>
                            <tr>
                                <td>NIP Staff</td>
                                <td>:</td>
                                <td><?= $barang_masuk->id_user ?></td>
                            </tr>
                            <tr>
                                <td>Nama Staff</td>
                                <td>:</td>
                                <td><?= $barang_masuk->nama ?></td>
                            </tr>
                            <tr>
                                <td>Supplier</td>
                                <td>:</td>
                                <td>
                                    <?php if (!empty($barang_masuk->nama_supplier)) : ?>
                                        <strong style="color:#2e7d32;">
                                            <i class="fas fa-truck" style="font-size:11px;"></i>
                                            <?= htmlspecialchars($barang_masuk->nama_supplier) ?>
                                        </strong>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td>:</td>
                                <td><?= date('d F Y', strtotime($barang_masuk->waktu)) ?></td>
                            </tr>
                            <tr>
                                <td>Waktu</td>
                                <td>:</td>
                                <td><?= date('H:i:s', strtotime($barang_masuk->waktu)) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="inv-meta-right">
                        <div class="meta-title">Pembayaran melalui Transfer :</div>
                        <table class="meta-table">
                            <tr>
                                <td>Bank</td><td>:</td><td><strong>Maybank</strong></td>
                            </tr>
                            <tr>
                                <td>No. Rekening</td><td>:</td><td><strong>2.767.001480</strong></td>
                            </tr>
                            <tr>
                                <td>Atas Nama</td><td>:</td><td><strong>PT Firnas Digital Indonesia</strong></td>
                            </tr>
                            <tr>
                                <td>NPWP</td><td>:</td><td>21.148.914.1-427.000</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Items -->
                <div class="inv-berikut">Berikut :</div>
                <table class="inv-items-table">
                    <thead>
                        <tr>
                            <th style="width:40px">No</th>
                            <th>Deskripsi Produk</th>
                            <th class="text-center" style="width:80px">Kuantitas</th>
                            <th class="text-right" style="width:140px">Harga Satuan</th>
                            <th class="text-right" style="width:140px">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($list_barang as $barang) : ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><strong><?= $barang->nama ?></strong></td>
                            <td class="text-center">
                                <?= $barang->qty ?>
                                <small><?= ucfirst(getUnitName($barang->id_satuan)) ?></small>
                            </td>
                            <td class="text-right">Rp<?= number_format($barang->harga, 0, ',', '.') ?></td>
                            <td class="text-right">Rp<?= number_format($barang->subtotal, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <!-- Summary + Terbilang -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="inv-terbilang">
                            <em><?= angkaTerbilang($total_incl_ppn) ?> Rupiah</em>
                        </div>

                        <div class="inv-perhatian">
                            <strong>Perhatian :</strong>
                            <ul>
                                <li>Pembayaran yang ditransfer selain ke rek PT Firnas Digital Indonesia, kami anggap belum melakukan pembayaran.</li>
                                <li>Bilamana terjadi keterlambatan pembayaran, kami akan mengenakan biaya bunga sesuai dengan tingkat suku bunga Bank yang berlaku.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="inv-summary-box">
                            <div class="inv-summary-row">
                                <span style="color:#555">Harga Pokok (DPP)</span>
                                <span>Rp<?= number_format($harga_pokok, 0, ',', '.') ?></span>
                            </div>
                            <div class="inv-summary-row">
                                <span style="color:#555">PPN 11%</span>
                                <span>Rp<?= number_format($ppn, 0, ',', '.') ?></span>
                            </div>
                            <div class="inv-summary-total">
                                <span>Total (incl. PPN)</span>
                                <span>Rp<?= number_format($total_incl_ppn, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signature -->
                <div class="inv-signature-grid mt-4">
                    <div style="font-size:12.5px; color:#555;">
                        Bekasi, <?= date('d F Y', strtotime($barang_masuk->waktu)) ?><br>
                        <strong>PT Firnas Digital Indonesia</strong>
                    </div>
                    <div class="inv-sig-box">
                        <div class="inv-sig-space"></div>
                        <div class="sig-name">Yudha Kurnia Pangestu</div>
                        <div class="sig-title">Managing Director</div>
                    </div>
                </div>

            </div><!-- /.inv-body -->

            <!-- Footer -->
            <div class="inv-footer-band">
                <div class="fc">
                    <span class="fc-title">Call</span>
                    <span>+62 851 9890 2304</span>
                    <span>+62 21 569 28063</span>
                </div>
                <div class="fc">
                    <span class="fc-title">Email</span>
                    <span>sales@firnasdigital.com</span>
                </div>
                <div class="fc">
                    <span class="fc-title">Website</span>
                    <span>www.firnasdigital.com</span>
                </div>
            </div>

        </div><!-- /.invoice-wrapper -->
    </div><!-- /#invoice-area -->
</div>
