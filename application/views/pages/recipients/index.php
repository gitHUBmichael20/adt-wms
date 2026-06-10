<div class="container-fluid">

    <?php $this->load->view('layouts/_alert') ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">List Penerima</h4>
                        <div class="d-flex gap-2">
                            <form action="<?= base_url('recipients/search') ?>" method="POST" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="Cari penerima...">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table no-wrap v-middle mb-0">
                            <thead>
                                <tr class="border-0">
                                    <th class="border-0 font-14 font-weight-medium text-muted">ID</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted">Nama Perusahaan</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted">Divisi</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted">Telefon</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted">Alamat</th>
                                    <?php if ($this->session->userdata('role') == 'admin') : ?>
                                        <th class="border-0"></th>
                                    <?php endif ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($content as $row) : ?>
                                    <tr>
                                        <td class="border-top-0 px-2 py-4"><?= $row->id ?></td>
                                        <td class="border-top-0 px-2 py-4 font-weight-medium"><?= $row->nama ?></td>
                                        <td class="border-top-0 px-2 py-4 text-muted"><?= $row->divisi ?></td>
                                        <td class="border-top-0 px-2 py-4 text-muted"><?= $row->telefon ?></td>
                                        <td class="border-top-0 px-2 py-4 text-muted" style="max-width:220px;"><?= $row->alamat ?></td>
                                        <?php if ($this->session->userdata('role') == 'admin') : ?>
                                            <td class="border-top-0 px-2 py-4">
                                                <a href="<?= base_url("recipients/edit/$row->id") ?>" class="btn btn-warning btn-sm btn-rounded text-white"><i class="fas fa-edit"></i> Edit</a>
                                            </td>
                                        <?php endif ?>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($this->uri->segment(2)) : ?>
                    <div class="card-footer bg-white">
                        <nav>
                            <?= $pagination ?>
                        </nav>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
