<div class="container-fluid">

    <?php $this->load->view('layouts/_alert') ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Tambah Penerima</h4>
                    <form action="<?= base_url('recipient') ?>" method="POST">
                        <div class="form-body">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-2">Nama Perusahaan</label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text"><i class="fas fa-building"></i></label>
                                            </div>
                                            <?= form_input('nama', $input->nama, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Nama perusahaan penerima']) ?>
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
                                            <?= form_input('divisi', $input->divisi, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Contoh: DEVELOPMENT, FINANCE, dll']) ?>
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
                                            <?= form_input('telefon', $input->telefon, ['class' => 'form-control', 'placeholder' => 'Masukan nomor telefon aktif', 'required' => true]) ?>
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
                                            <?= form_input('alamat', $input->alamat, ['class' => 'form-control', 'placeholder' => 'Masukan alamat lengkap penerima', 'required' => true]) ?>
                                        </div>
                                        <?= form_error('alamat') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="text-right">
                                <button type="submit" class="btn btn-info">Submit</button>
                                <a href="<?= base_url('recipient/reset') ?>" class="btn btn-dark">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
