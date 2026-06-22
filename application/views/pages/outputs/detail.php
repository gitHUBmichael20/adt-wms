<div class="container-fluid mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?= base_url('outputs') ?>" class="btn btn-primary btn-rounded">
            <i class="fas fa-angle-left"></i> Kembali ke Catatan Keluar
        </a>
        <a href="<?= base_url('outputs/download_docx/' . $barang_keluar->id_barang_keluar) ?>"
           class="btn btn-warning btn-rounded text-white"
           title="Download Delivery Order sebagai DOCX">
            <i class="fas fa-file-word"></i> Download DO DOCX
        </a>
    </div>
</div>
<div class="container-fluid">
    <?php $this->load->view('layouts/_alert') ?>

    </div>

    <div id="do-area">
        <div class="do-wrapper">

            <div class="do-header">
                <div>
                    <div class="do-company-name">PT FIRNAS DIGITAL INDONESIA</div>
                    <div class="do-company-sub">Grand Galaxy City Jl. Boulevard Raya Blok RSN-2 No.18 Jaka Setia, Bekasi, 17147</div>
                </div>
                <div class="do-logo-box">FDI</div>
            </div>

            <div class="do-title-bar">DELIVERY ORDER</div>

            <div class="do-body">

                <div class="do-meta-grid">
                    <div class="do-meta-left">
                        <table class="do-meta-table">
                            <tr>
                                <td>DO Number</td>
                                <td>:</td>
                                <td><strong>FDI/DO/<?= date('Y/m', strtotime($barang_keluar->waktu)) ?>/<?= str_pad($barang_keluar->id_barang_keluar, 5, '0', STR_PAD_LEFT) ?></strong></td>
                            </tr>
                            <tr>
                                <td>Date</td>
                                <td>:</td>
                                <td>Bekasi, <?= date('d F Y', strtotime($barang_keluar->waktu)) ?></td>
                            </tr>
                            <tr>
                                <td>PO</td>
                                <td>:</td>
                                <td><?= !empty($barang_keluar->no_po) ? htmlspecialchars($barang_keluar->no_po) : '-' ?></td>
                            </tr>
                            <tr>
                                <td>Customer</td>
                                <td>:</td>
                                <td>
                                    <?php if ($penerima) : ?>
                                        <strong><?= htmlspecialchars($penerima->nama) ?></strong>
                                        <?php if ($penerima->divisi) : ?>
                                            <br><small><?= htmlspecialchars($penerima->divisi) ?></small>
                                        <?php endif ?>
                                    <?php else : ?>
                                        —
                                    <?php endif ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="do-shipto">
                        <div class="do-shipto-label">Ship To :</div>
                        <?php if ($penerima) : ?>
                            <div class="do-shipto-divisi"><?= htmlspecialchars($penerima->divisi) ?></div>
                            <div class="do-shipto-name"><?= htmlspecialchars($penerima->nama) ?></div>
                            <div class="do-shipto-addr"><?= nl2br(htmlspecialchars($penerima->alamat)) ?></div>
                        <?php else : ?>
                            <div style="color:#999; font-style:italic;">Penerima tidak dicatat</div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="do-berikut">Berikut :</div>

                <table class="do-items-table">
                    <thead>
                        <tr>
                            <th style="width:36px">No</th>
                            <th style="text-align:left">Deskripsi Produk</th>
                            <th style="width:80px">Kuantitas</th>
                            <th style="width:80px">Satuan</th>
                            <th style="width:180px">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($list_barang as $barang) : ?>
                        <tr>
                            <td class="tc"><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($barang->nama) ?></strong></td>
                            <td class="tc"><?= $barang->qty ?></td>
                            <td class="tc"><?= ucfirst(getUnitName($barang->id_satuan)) ?></td>
                            <td><?= !empty($barang_keluar->keterangan) ? htmlspecialchars($barang_keluar->keterangan) : '' ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <div class="do-signature-grid">
                    <div class="do-sig-box">
                        <div style="margin-bottom:4px; font-size:12.5px;">Pengirim,</div>
                        <strong>PT Firnas Digital Indonesia</strong>
                        <div class="do-sig-space"></div>
                        <div class="do-sig-name">Nova Saputra</div>
                        <div class="do-sig-sub">08571109 4473</div>
                        <div class="do-sig-sub">admin@firnasdigital.com</div>
                    </div>
                    <div class="do-sig-box">
                        <div style="margin-bottom:4px; font-size:12.5px;">Penerima,</div>
                        <div style="color:#555; font-size:12px;">Barang telah diterima<br>dengan baik dan cukup</div>
                        <div class="do-sig-space"></div>
                        <div style="font-size:12.5px;">( <span style="display:inline-block; width:160px; border-bottom:1px solid #222;"></span> )</div>
                    </div>
                </div>

            </div>

            <div class="do-footer">
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

        </div>
    </div>
</div>
