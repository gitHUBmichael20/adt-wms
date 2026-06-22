<!-- ============================================================== -->
<!-- Container fluid  -->
<!-- ============================================================== -->
<div class="container-fluid">
    
    <?php $this->load->view('layouts/_alert') ?>
    
    <!-- Filter -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body py-3">
                    <!-- Search -->
                    <form action="<?= base_url('items/out/search') ?>" method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="keyword" class="form-control" placeholder="Cari nama barang..." value="<?= $this->session->userdata('keyword_out') ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                <a href="<?= base_url('items/out') ?>" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-lg-6">
                            <h6 class="d-inline text-dark font-weight-bold">Filter Satuan &rarr;</h6>
                            <span>
                                <a href="<?= base_url('items/out') ?>" class="btn btn-sm btn-rounded btn-dark mt-1">Semua</a>
                                <?php foreach(getUnits() as $unit) : ?>
                                    <a href="<?= base_url('items/out/unit/' . $unit->id) ?>" class="btn btn-sm btn-rounded btn-primary mt-1"><?= ucfirst($unit->nama) ?></a>
                                <?php endforeach ?>
                            </span>
                        </div>
                        <div class="col-lg-6">
                            <h6 class="d-inline text-dark font-weight-bold">Ketersediaan &rarr;</h6>
                            <span>
                                <a href="<?= base_url('items/out/availability/available') ?>" class="btn btn-sm btn-rounded btn-success mt-1">Ada</a>
                                <a href="<?= base_url('items/out/availability/empty') ?>" class="btn btn-sm btn-rounded btn-danger mt-1">Kosong</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- List Barang -->
    <div class="row">
        <?php if (empty($content)) : ?>
            <div class="col-12">
                <div class="alert alert-info">Tidak ada barang ditemukan.</div>
            </div>
        <?php endif ?>
        <?php foreach ($content as $row) : ?>
            <div class="col-md-6 col-lg-4">
                <div class="card mb-3 shadow-sm <?= $row->qty <= 0 ? 'border-danger' : '' ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title mb-1 font-weight-bold"><?= htmlspecialchars($row->nama_barang) ?></h5>
                                <small class="text-muted"><?= ucfirst($row->nama_satuan) ?></small>
                            </div>
                            <span class="badge badge-<?= $row->qty > 0 ? 'success' : 'danger' ?> badge-pill">
                                <?= $row->qty > 0 ? 'Stok: ' . $row->qty : 'Kosong' ?>
                            </span>
                        </div>
                        <p class="mb-1"><strong>Rp <?= number_format($row->harga, 0, ',', '.') ?>,-</strong></p>
                        <p class="mb-2 small text-muted">Supplier: <?= htmlspecialchars($row->nama_supplier) ?></p>
                        <?php if ($row->qty > 0) : ?>
                            <form action="<?= base_url('cartout/add') ?>" method="POST" class="mt-2">
                                <input type="hidden" name="id_barang" value="<?= $row->id_barang ?>">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="qty_keluar" min="1" max="<?= $row->qty ?>" value="1" class="form-control text-center">
                                    <div class="input-group-append">
                                        <button class="btn btn-warning text-white" type="submit">
                                            <i class="fas fa-cart-arrow-down"></i> Keluarkan
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Maks: <?= $row->qty ?> <?= ucfirst($row->nama_satuan) ?></small>
                            </form>
                        <?php else : ?>
                            <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small">Stok habis, tidak bisa dikeluarkan</div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <div class="row d-flex justify-content-center">
        <nav><?= $pagination ?></nav>
    </div>
</div>
