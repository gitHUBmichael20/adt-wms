<!-- ============================================================== -->
<!-- Container fluid  -->
<!-- ============================================================== -->
<div class="container-fluid">
    
    <?php $this->load->view('layouts/_alert') ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    Keranjang Penambahan Barang
                </div>
                <div class="card-body">
                    <table class="table table-responsive w-100 d-block d-md-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($content as $row) : ?>
                                <tr>
                                    <td>
                                        <strong><?= $row->nama ?></strong>
                                    </td>
                                    <td class="text-center">
                                        Rp.<?= number_format($row->harga, 0, ',', '.') ?>,- / 
                                        <small><?= ucfirst(getUnitName($row->id_satuan)) ?></small>
                                    </td>
                                    <td>
                                        <form action="<?= base_url('cartin/update') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                            <input type="hidden" name="id_barang" value="<?= $row->id_barang ?>">
                                            <div class="input-group">
                                                <input type="number" name="qty_barang_masuk" class="form-control text-center" value="<?= $row->qty_barang_masuk ?>">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-info"><i class="fas fa-check"></i></button>
                                                </div>
                                            </div>
                                            <small class="text-danger mt-1"><?= $this->session->flashdata("qty_cartin_$row->id") ?></small>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        Rp.<?= number_format($row->subtotal, 0, ',', '.') ?>,-
                                        <br><small class="text-muted">(incl. PPN 11%)</small>
                                    </td>
                                    <td>
                                        <form action="<?= base_url('cartin/delete') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            <tr>
                                <td colspan="3"><strong>Total (incl. PPN 11%):</strong></td>
                                <td class="text-center"><strong>Rp.<?= number_format(array_sum(array_column($content, 'subtotal')), 0, ',', '.') ?>,-</strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <div class="row">
                        <!-- Tombol kiri: kembali ke list barang -->
                        <div class="col-md-3 col-sm-12 mb-2">
                            <a href="<?= base_url('items') ?>" class="btn btn-warning btn-rounded text-white">
                                <i class="fas fa-angle-left"></i> List barang
                            </a>
                        </div>

                        <!-- Tengah: dropdown supplier + tombol kosongkan -->
                        <div class="col-md-6 col-sm-12 mb-2">
                            <!-- Form checkout: supplier dropdown + tombol checkout -->
                            <form action="<?= base_url('cartin/checkout') ?>" method="POST" id="form-checkout">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="fas fa-truck"></i>&nbsp; Supplier
                                        </span>
                                    </div>
                                    <select name="id_supplier" class="form-control" id="select-supplier" required>
                                        <option value="">-- Pilih Supplier --</option>
                                        <?php foreach ($suppliers as $sup) : ?>
                                            <option value="<?= $sup->id ?>"><?= htmlspecialchars($sup->nama) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <small class="text-muted">Pilih supplier asal barang yang masuk</small>
                            </form>
                        </div>

                        <!-- Kanan: tombol kosongkan + checkout -->
                        <div class="col-md-3 col-sm-12 mb-2 d-flex justify-content-end align-items-start gap-2" style="gap:8px;">
                            <form action="<?= base_url('cartin/drop') ?>" method="POST" class="mr-2">
                                <button type="submit" class="btn btn-danger btn-rounded text-white">
                                    <i class="fas fa-trash"></i> Kosongkan
                                </button>
                            </form>
                            <button type="submit" form="form-checkout" class="btn btn-success btn-rounded">
                                Checkout <i class="fas fa-angle-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Container fluid  -->
<!-- ============================================================== -->
