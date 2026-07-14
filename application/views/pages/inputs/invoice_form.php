<div class="container-fluid">
    <?php $this->load->view('layouts/_alert') ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">
                        <i class="fas fa-file-invoice"></i> Kustomisasi Invoice DOCX
                    </h4>
                    <p class="text-muted mb-4" style="font-size:13px;">
                        Isi field di bawah sesuai kebutuhan. Kosongkan jika ingin menggunakan nilai default dari sistem.
                    </p>

                    <form action="<?= base_url('inputs/download_docx/' . $barang_masuk->id_barang_masuk) ?>" method="POST">

                        <!-- DATE -->
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-bold">Tanggal Invoice</label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="custom_date"
                                       class="form-control"
                                       placeholder="Cth: 18 Mei 2026  (kosongkan = otomatis dari tanggal transaksi)"
                                       value="<?= date('d F Y', strtotime($barang_masuk->waktu)) ?>">
                                <small class="form-text text-muted">Format bebas, misalnya: 20 Juni 2026</small>
                            </div>
                        </div>

                        <!-- INVOICE NUMBER -->
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-bold">Invoice No</label>
                            <div class="col-lg-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-secondary text-white" style="font-size:12px; letter-spacing:0.5px;">FDI/INV/</span>
                                    </div>
                                    <input type="text"
                                           name="inv_no_date"
                                           class="form-control"
                                           placeholder="2026/06"
                                           value="<?= date('Y/m', strtotime($barang_masuk->waktu)) ?>"
                                           style="max-width:110px; flex:0 0 110px;">
                                    <div class="input-group-prepend input-group-append">
                                        <span class="input-group-text">/</span>
                                    </div>
                                    <input type="text"
                                           name="inv_no_number"
                                           class="form-control"
                                           placeholder="Cth: 01354"
                                           value="<?= str_pad($barang_masuk->id_barang_masuk, 5, '0', STR_PAD_LEFT) ?>">
                                </div>
                                <small class="form-text text-muted">
                                    Hasil: <code>FDI/INV/<span id="preview-inv">
                                        <?= date('Y/m', strtotime($barang_masuk->waktu)) ?>/<?= str_pad($barang_masuk->id_barang_masuk, 5, '0', STR_PAD_LEFT) ?>
                                    </span></code>
                                </small>
                            </div>
                        </div>

                        <!-- NO SP -->
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-bold">No. SP</label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="custom_no_sp"
                                       class="form-control"
                                       placeholder="Nomor Surat Pesanan (kosongkan = ID transaksi)"
                                       value="<?= $barang_masuk->id_barang_masuk ?>">
                            </div>
                        </div>

                        <!-- BTB -->
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-bold">No. BTB</label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="custom_btb"
                                       class="form-control"
                                       placeholder="Nomor Bukti Terima Barang (opsional)">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted mb-3" style="font-size:12px; text-transform:uppercase; letter-spacing:1px;">Info Supplier (dari database)</h6>

                        <!-- INFO SUPPLIER readonly -->
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label text-muted">Nama Supplier</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" readonly
                                       value="<?= htmlspecialchars($barang_masuk->nama_supplier) ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label text-muted">Alamat Supplier</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" readonly
                                       value="<?= htmlspecialchars($barang_masuk->alamat_supplier) ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label text-muted">NPWP Supplier</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" readonly
                                       value="<?= htmlspecialchars(!empty($barang_masuk->npwp_supplier) ? $barang_masuk->npwp_supplier : '(belum diisi — edit di manajemen supplier)') ?>">
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('inputs/detail/' . $barang_masuk->id_barang_masuk) ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-warning text-white">
                                    <i class="fas fa-file-word"></i> Generate &amp; Download DOCX
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var dateInput   = document.querySelector('[name="inv_no_date"]');
    var numInput    = document.querySelector('[name="inv_no_number"]');
    var preview     = document.getElementById('preview-inv');
    function updatePreview() {
        preview.textContent = (dateInput.value || '____') + '/' + (numInput.value || '_____');
    }
    dateInput.addEventListener('input', updatePreview);
    numInput.addEventListener('input', updatePreview);
})();
</script>
