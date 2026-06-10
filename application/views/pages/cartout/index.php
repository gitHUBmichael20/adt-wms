<div class="container-fluid">
    
    <?php $this->load->view('layouts/_alert') ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    Keranjang Pengeluaran Barang
                </div>
                <div class="card-body">
                    <table class="table table-responsive w-100 d-block d-md-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th class="text-center">Jumlah</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($content as $row) : ?>
                                <tr>
                                    <td>
                                        <strong><?= $row->nama ?></strong> / 
                                        <small><?= ucfirst(getUnitName($row->id_satuan)) ?></small>
                                    </td>
                                    <td>
                                        <form action="<?= base_url('cartout/update') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                            <input type="hidden" name="id_barang" value="<?= $row->id_barang ?>">
                                            <div class="input-group">
                                                <input type="number" name="qty_barang_keluar" class="form-control text-center" value="<?= $row->qty_barang_keluar ?>">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-info"><i class="fas fa-check"></i></button>
                                                </div>
                                            </div>
                                            <small class="text-danger mt-1"><?= $this->session->flashdata("qty_cartout_$row->id") ?></small>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="<?= base_url('cartout/delete') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

                <!-- Form Checkout: Penerima, PO, Keterangan -->
                <div class="card-body border-top">
                    <form action="<?= base_url('cartout/checkout') ?>" method="POST" id="form-checkout">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_penerima"><strong>Tujuan Pengiriman</strong></label>
                                    <select name="id_penerima" id="id_penerima" class="form-control">
                                        <option value="">— Pilih Penerima (opsional) —</option>
                                        <?php if (!empty($recipients)) : ?>
                                            <?php foreach ($recipients as $r) : ?>
                                                <option value="<?= $r->id ?>"><?= $r->nama ?> — <?= $r->divisi ?></option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                    <small class="text-muted">Pilih perusahaan / divisi tujuan pengiriman barang</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_po"><strong>Nomor PO / Referensi</strong></label>
                                    <input type="text" name="no_po" id="no_po" class="form-control" placeholder="Contoh: SP 2148353 (opsional)">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keterangan"><strong>Keterangan</strong></label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Keterangan tambahan, contoh: Unit Baru dan Bergaransi (opsional)"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 col-sm-12 mb-2">
                                <a href="<?= base_url('items') ?>" class="btn btn-warning btn-rounded text-white"><i class="fas fa-angle-left"></i> List barang</a>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-2 d-flex justify-content-center">
                                <button type="button" onclick="dropCart()" class="btn btn-danger btn-rounded text-white"><i class="fas fa-trash"></i> Kosongkan keranjang</button>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-2">
                                <button type="submit" class="btn btn-success btn-rounded float-right">Checkout <i class="fas fa-angle-right"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form drop terpisah -->
<form action="<?= base_url('cartout/drop') ?>" method="POST" id="form-drop">
    <input type="hidden" name="id_pesanan" value="">
</form>

<script>
function dropCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        document.getElementById('form-drop').submit();
    }
}
</script>
