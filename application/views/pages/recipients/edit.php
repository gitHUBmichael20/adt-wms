<div class="container-fluid">

    <?php $this->load->view('layouts/_alert') ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Edit Penerima — <?= $content->nama ?></h4>
                    <form action="<?= base_url("recipients/edit/$content->id") ?>" method="POST">
                        <input type="hidden" name="id" value="<?= $content->id ?>">
                        <div class="form-body">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-2">Nama Perusahaan</label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text"><i class="fas fa-building"></i></label>
                                            </div>
                                            <?= form_input('nama', $input->nama, ['class' => 'form-control', 'required' => true]) ?>
                                        </div>
                                        <?= form_error('nama') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-2">Divisi / Departemen</label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text"><i class="fas fa-sitemap"></i></label>
                                            </div>
                                            <?= form_input('divisi', $input->divisi, ['class' => 'form-control', 'required' => true]) ?>
                                        </div>
                                        <?= form_error('divisi') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-2">Nomor Telefon</label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text"><i class="fas fa-phone"></i></label>
                                            </div>
                                            <?= form_input('telefon', $input->telefon, ['class' => 'form-control', 'required' => true]) ?>
                                        </div>
                                        <?= form_error('telefon') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-2">Alamat Lengkap</label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text"><i class="fas fa-map-marker-alt"></i></label>
                                            </div>
                                            <?= form_input('alamat', $input->alamat, ['class' => 'form-control', 'required' => true]) ?>
                                        </div>
                                        <?= form_error('alamat') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="text-right">
                                <button type="submit" class="btn btn-info">Simpan</button>
                                <a href="<?= base_url('recipients') ?>" class="btn btn-dark">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Toko / Cabang milik <?= htmlspecialchars($content->nama) ?></h4>
                    </div>
                    <p class="text-muted">
                        <?= htmlspecialchars($content->nama) ?> adalah perusahaan induk (customer). Setiap toko/cabang
                        di bawahnya punya alamat pengiriman sendiri, dan akan muncul sebagai pilihan
                        "Toko Tujuan" saat membuat transaksi Barang Keluar.
                    </p>

                    <?php if (!empty($stores)) : ?>
                        <div class="table-responsive mb-4">
                            <table class="table no-wrap v-middle mb-0">
                                <thead>
                                    <tr class="border-0">
                                        <th class="border-0 font-14 font-weight-medium text-muted">Nama Toko</th>
                                        <th class="border-0 font-14 font-weight-medium text-muted">Alamat</th>
                                        <th class="border-0 font-14 font-weight-medium text-muted">PIC</th>
                                        <th class="border-0 font-14 font-weight-medium text-muted">Telefon</th>
                                        <th class="border-0"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stores as $store) : ?>
                                        <?php $isEditing = isset($edit_store_id) && $edit_store_id == $store->id; ?>
                                        <?php if ($isEditing) : ?>
                                            <tr>
                                                <td colspan="5" class="px-2 py-3">
                                                    <form action="<?= base_url("recipients/store_edit/$store->id") ?>" method="POST">
                                                        <div class="form-row">
                                                            <div class="col-md-3 mb-2">
                                                                <input type="text" name="nama_toko" class="form-control" placeholder="Nama toko / cabang" value="<?= htmlspecialchars($store_input->nama_toko ?? $store->nama_toko) ?>" required>
                                                                <?= form_error('nama_toko') ?>
                                                            </div>
                                                            <div class="col-md-4 mb-2">
                                                                <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap toko" value="<?= htmlspecialchars($store_input->alamat ?? $store->alamat) ?>" required>
                                                                <?= form_error('alamat') ?>
                                                            </div>
                                                            <div class="col-md-2 mb-2">
                                                                <input type="text" name="pic" class="form-control" placeholder="PIC (opsional)" value="<?= htmlspecialchars((string) ($store_input->pic ?? $store->pic)) ?>">
                                                            </div>
                                                            <div class="col-md-2 mb-2">
                                                                <input type="text" name="telefon" class="form-control" placeholder="Telefon (opsional)" value="<?= htmlspecialchars((string) ($store_input->telefon ?? $store->telefon)) ?>">
                                                            </div>
                                                            <div class="col-md-1 mb-2 d-flex">
                                                                <button type="submit" class="btn btn-info btn-sm mr-1"><i class="fas fa-check"></i></button>
                                                                <a href="<?= base_url("recipients/edit/$content->id") ?>" class="btn btn-dark btn-sm"><i class="fas fa-times"></i></a>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php else : ?>
                                            <tr>
                                                <td class="border-top-0 px-2 py-3 font-weight-medium"><?= htmlspecialchars($store->nama_toko) ?></td>
                                                <td class="border-top-0 px-2 py-3 text-muted" style="max-width:220px;"><?= htmlspecialchars($store->alamat) ?></td>
                                                <td class="border-top-0 px-2 py-3 text-muted"><?= htmlspecialchars($store->pic ?: '-') ?></td>
                                                <td class="border-top-0 px-2 py-3 text-muted"><?= htmlspecialchars($store->telefon ?: '-') ?></td>
                                                <td class="border-top-0 px-2 py-3">
                                                    <a href="<?= base_url("recipients/edit/$content->id") ?>?edit_toko=<?= $store->id ?>" class="btn btn-warning btn-sm btn-rounded text-white"><i class="fas fa-edit"></i></a>
                                                    <form action="<?= base_url('recipients/store_delete') ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="id_toko" value="<?= $store->id ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm btn-rounded" onclick="return confirm('Hapus toko ini?')"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">Belum ada toko/cabang untuk penerima ini.</div>
                    <?php endif ?>

                    <hr>

                    <h6 class="mb-3">Tambah Toko Baru</h6>
                    <form action="<?= base_url("recipients/store_add/$content->id") ?>" method="POST">
                        <div class="form-row">
                            <div class="col-md-3 mb-2">
                                <input type="text" name="nama_toko" class="form-control" placeholder="Nama toko / cabang" value="<?= isset($store_input) && !isset($edit_store_id) ? htmlspecialchars($store_input->nama_toko) : '' ?>" required>
                                <?= (!isset($edit_store_id)) ? form_error('nama_toko') : '' ?>
                            </div>
                            <div class="col-md-4 mb-2">
                                <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap toko" value="<?= isset($store_input) && !isset($edit_store_id) ? htmlspecialchars($store_input->alamat) : '' ?>" required>
                                <?= (!isset($edit_store_id)) ? form_error('alamat') : '' ?>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="pic" class="form-control" placeholder="PIC (opsional)" value="<?= isset($store_input) && !isset($edit_store_id) ? htmlspecialchars((string) $store_input->pic) : '' ?>">
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="telefon" class="form-control" placeholder="Telefon (opsional)" value="<?= isset($store_input) && !isset($edit_store_id) ? htmlspecialchars((string) $store_input->telefon) : '' ?>">
                            </div>
                            <div class="col-md-1 mb-2">
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
