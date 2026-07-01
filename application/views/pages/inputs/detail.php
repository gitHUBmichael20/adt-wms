<div class="container-fluid mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?= base_url('inputs') ?>" class="btn btn-primary btn-rounded">
            <i class="fas fa-angle-left"></i> Kembali ke Catatan Masuk
        </a>
        <a href="<?= base_url('inputs/invoice_form/' . $barang_masuk->id_barang_masuk) ?>"
           class="btn btn-warning btn-rounded text-white"
           title="Download Invoice sebagai DOCX">
            <i class="fas fa-file-word"></i> Download Invoice DOCX
        </a>
    </div>
</div>
<?php
// Subtotal dari DB sudah termasuk PPN 11% (harga * qty * 1.11)
// Breakdown: harga_pokok = subtotal / 1.11, ppn = subtotal - harga_pokok
$total_incl_ppn = array_sum(array_column($list_barang, 'subtotal'));
$harga_pokok    = round($total_incl_ppn / 1.11);
$ppn            = $total_incl_ppn - $harga_pokok;
?>

<div class="container-fluid">
    <?php $this->load->view('layouts/_alert') ?>

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
