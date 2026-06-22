<style>
@media print {
    body * { visibility: hidden !important; }
    #do-area, #do-area * { visibility: visible !important; }
    #do-area { position: fixed; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
    .do-wrapper { box-shadow: none !important; border: none !important; }
    @page { size: A4; margin: 10mm 15mm; }
}
.do-wrapper {
    background: #fff;
    max-width: 820px;
    margin: 0 auto;
    padding: 0;
    box-shadow: 0 2px 20px rgba(0,0,0,0.10);
    border-radius: 4px;
    overflow: hidden;
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 13px;
    color: #222;
}
.do-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 18px 28px 12px;
    border-bottom: 2px solid #1565c0;
}
.do-company-name { font-size: 20px; font-weight: 700; color: #1565c0; }
.do-company-sub  { font-size: 10.5px; color: #555; margin-top: 2px; }
.do-logo-box {
    background: #1565c0;
    border-radius: 6px;
    padding: 6px 14px;
    color: #fff;
    font-size: 22px;
    font-weight: 800;
}
.do-title-bar {
    text-align: center;
    padding: 10px;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 3px;
    color: #1565c0;
    text-decoration: underline;
    text-underline-offset: 4px;
    border-bottom: 1px solid #e3eaf5;
}
.do-body { padding: 18px 28px 20px; }
.do-meta-grid { display: flex; gap: 20px; margin-bottom: 16px; }
.do-meta-left { flex: 1; }
.do-meta-table td { padding: 2px 4px; font-size: 12.5px; }
.do-meta-table td:first-child { color: #555; min-width: 130px; }
.do-meta-table td:nth-child(2) { padding: 2px 6px; color: #555; }
.do-shipto {
    border: 1.5px solid #1565c0;
    border-radius: 4px;
    padding: 10px 16px;
    min-width: 260px;
    background: #f0f4ff;
    font-size: 12.5px;
}
.do-shipto .do-shipto-label { font-size: 11.5px; color: #555; margin-bottom: 4px; }
.do-shipto .do-shipto-name { font-weight: 700; font-size: 13.5px; color: #1565c0; }
.do-shipto .do-shipto-divisi { font-weight: 700; font-size: 13px; }
.do-shipto .do-shipto-addr { color: #555; margin-top: 4px; font-size: 12px; }
.do-berikut { font-size: 12.5px; margin-bottom: 6px; }
.do-items-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 12.5px; }
.do-items-table thead tr { background: #1565c0; color: #fff; }
.do-items-table thead th { padding: 8px 10px; font-weight: 600; border: 1px solid #1565c0; text-align: center; }
.do-items-table thead th:nth-child(2) { text-align: left; }
.do-items-table tbody td { padding: 8px 10px; border: 1px solid #c5cee0; vertical-align: middle; }
.do-items-table .tc { text-align: center; }
.do-signature-grid { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px; }
.do-sig-box { text-align: center; font-size: 12.5px; min-width: 180px; }
.do-sig-space { height: 60px; }
.do-sig-name { font-weight: 700; text-decoration: underline; }
.do-sig-sub  { color: #555; font-size: 11.5px; }
.do-footer {
    border-top: 2px solid #1565c0;
    background: #f0f4ff;
    padding: 10px 28px;
    display: flex;
    justify-content: space-between;
    font-size: 11.5px;
    color: #555;
    gap: 20px;
}
.do-footer .fc { display: flex; flex-direction: column; gap: 2px; }
.do-footer .fc-title { font-weight: 700; color: #1565c0; font-size: 12px; margin-bottom: 2px; }
</style>

<div class="container-fluid">
    <?php $this->load->view('layouts/_alert') ?>

    <div class="no-print mb-3 d-flex justify-content-between align-items-center">
        <a href="<?= base_url('outputs') ?>" class="btn btn-primary btn-rounded text-white">
            <i class="fas fa-angle-left"></i> List Barang Keluar
        </a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('outputs/download_docx/' . $barang_keluar->id_barang_keluar) ?>"
               class="btn btn-warning btn-rounded text-white"
               title="Download Delivery Order sebagai DOCX — buka lalu Ctrl+P untuk cetak / Save as PDF">
                <i class="fas fa-file-word"></i> Download DOCX
            </a>
            <button class="btn btn-success btn-rounded" onclick="printDiv('do-area')">
                <i class="fas fa-print"></i> Cetak Delivery Order
            </button>
        </div>
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
                            <th style="width:160px">Serial Number</th>
                            <th style="width:150px">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($list_barang as $barang) : ?>
                        <tr>
                            <td class="tc"><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($barang->nama) ?></strong></td>
                            <td class="tc"><?= $barang->qty ?></td>
                            <td class="tc"><?= ucfirst(getUnitName($barang->id_satuan)) ?></td>
                            <td><?= !empty($barang->serial_number) ? htmlspecialchars($barang->serial_number) : '-' ?></td>
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
