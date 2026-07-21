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
                                <th>Serial Number</th>
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
                                        <!-- Serial number dikirim saat checkout via hidden input (lihat form di bawah) -->
                                        <div class="sn-scan-wrapper" data-id-barang="<?= $row->id_barang ?>" data-qty="<?= $row->qty_barang_keluar ?>" style="min-width:220px;">
                                            <div class="input-group input-group-sm mb-1">
                                                <input
                                                    type="text"
                                                    class="form-control sn-scan-input"
                                                    placeholder="Scan / ketik SN, lalu Enter"
                                                    autocomplete="off"
                                                >
                                                <div class="input-group-append">
                                                    <span class="input-group-text sn-count-badge">0/<?= $row->qty_barang_keluar ?></span>
                                                </div>
                                            </div>
                                            <div class="sn-chips"></div>
                                            <small class="text-muted">Opsional. Arahkan scanner ke kolom ini lalu scan satu-satu</small>
                                        </div>
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
                    <form action="<?= base_url('cartout/request_checkout') ?>" method="POST" id="form-checkout">

                        <!-- Hidden inputs untuk serial number — diisi via JS sebelum submit -->
                        <?php foreach ($content as $row) : ?>
                            <input
                                type="hidden"
                                name="serial_numbers[<?= $row->id_barang ?>]"
                                id="sn-hidden-<?= $row->id_barang ?>"
                                value=""
                            >
                        <?php endforeach ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_penerima"><strong>Penerima (Customer)</strong></label>
                                    <select name="id_penerima" id="id_penerima" class="form-control">
                                        <option value="">— Pilih Penerima (opsional) —</option>
                                        <?php if (!empty($recipients)) : ?>
                                            <?php foreach ($recipients as $r) : ?>
                                                <option value="<?= $r->id ?>"><?= $r->nama ?> — <?= $r->divisi ?></option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                    <small class="text-muted">Perusahaan induk / customer tujuan pengiriman</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_toko"><strong>Toko Tujuan (Ship To)</strong></label>
                                    <select name="id_toko" id="id_toko" class="form-control" disabled>
                                        <option value="">— Pilih Penerima dahulu —</option>
                                    </select>
                                    <small class="text-muted">Cabang / alamat kirim spesifik milik penerima di atas</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                                <a href="<?= base_url('items/out') ?>" class="btn btn-warning btn-rounded text-white"><i class="fas fa-angle-left"></i> List barang</a>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-2 d-flex justify-content-center">
                                <button type="button" onclick="dropCart()" class="btn btn-danger btn-rounded text-white"><i class="fas fa-trash"></i> Kosongkan keranjang</button>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-2">
                                <button type="submit" class="btn btn-success btn-rounded float-right"><i class="fas fa-envelope"></i> Kirim Konfirmasi <i class="fas fa-angle-right"></i></button>
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

<style>
.sn-chips { display: flex; flex-wrap: wrap; gap: 4px; min-height: 4px; }
.sn-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe;
    border-radius: 12px; padding: 2px 8px; font-size: 12px; line-height: 1.6;
}
.sn-chip button {
    background: none; border: none; color: #6366f1; font-weight: bold;
    line-height: 1; padding: 0; cursor: pointer; font-size: 13px;
}
.sn-chip button:hover { color: #dc2626; }
.sn-count-badge { min-width: 46px; text-align: center; font-weight: 600; }
.sn-count-badge.sn-ok { background: #d1fae5 !important; color: #065f46; }
.sn-count-badge.sn-over { background: #fee2e2 !important; color: #991b1b; }
.sn-scan-input.sn-flash { background-color: #ecfdf5; transition: background-color .15s ease; }
</style>

<script>
function dropCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        document.getElementById('form-drop').submit();
    }
}

// ==== Dynamic Toko (Ship To) dropdown ====
// Saat "Penerima" dipilih, ambil daftar toko miliknya via AJAX supaya
// "Toko Tujuan" selalu sesuai dengan penerima yang dipilih.
(function () {
    var penerimaSelect = document.getElementById('id_penerima');
    var tokoSelect      = document.getElementById('id_toko');
    var baseUrl          = '<?= base_url('cartout/stores/') ?>';

    function resetTokoSelect(message) {
        tokoSelect.innerHTML = '<option value="">' + message + '</option>';
        tokoSelect.disabled = true;
    }

    function loadStores(idPenerima) {
        if (!idPenerima) {
            resetTokoSelect('— Pilih Penerima dahulu —');
            return;
        }

        resetTokoSelect('Memuat daftar toko...');

        fetch(baseUrl + idPenerima)
            .then(function (res) { return res.json(); })
            .then(function (stores) {
                if (!stores || stores.length === 0) {
                    resetTokoSelect('— Belum ada toko untuk penerima ini —');
                    return;
                }

                tokoSelect.innerHTML = '<option value="">— Pilih Toko (opsional) —</option>';
                stores.forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.nama_toko + ' — ' + s.alamat;
                    tokoSelect.appendChild(opt);
                });
                tokoSelect.disabled = false;
            })
            .catch(function () {
                resetTokoSelect('— Gagal memuat toko, coba lagi —');
            });
    }

    if (penerimaSelect) {
        penerimaSelect.addEventListener('change', function () {
            loadStores(this.value);
        });

        // Kalau halaman reload dengan penerima sudah terpilih (mis. validasi
        // gagal), langsung muat toko-nya juga
        if (penerimaSelect.value) {
            loadStores(penerimaSelect.value);
        }
    }
})();

// ==== Dynamic serial number scanning ====
// Setiap wrapper .sn-scan-wrapper punya array serial number sendiri.
// Scanner barcode pada umumnya bekerja seperti keyboard: ketik kode lalu kirim
// tombol Enter, jadi kita cukup dengar event Enter pada input untuk menambah SN.

function initSerialScanning() {
    document.querySelectorAll('.sn-scan-wrapper').forEach(function (wrapper) {
        var idBarang = wrapper.getAttribute('data-id-barang');
        var qty      = parseInt(wrapper.getAttribute('data-qty'), 10) || 0;
        var input    = wrapper.querySelector('.sn-scan-input');
        var chipsBox = wrapper.querySelector('.sn-chips');
        var badge    = wrapper.querySelector('.sn-count-badge');
        var hidden   = document.getElementById('sn-hidden-' + idBarang);
        var serials  = [];

        function render() {
            chipsBox.innerHTML = '';
            serials.forEach(function (sn, idx) {
                var chip = document.createElement('span');
                chip.className = 'sn-chip';
                chip.innerHTML = '<span></span> <button type="button" title="Hapus">&times;</button>';
                chip.querySelector('span').textContent = sn;
                chip.querySelector('button').addEventListener('click', function () {
                    serials.splice(idx, 1);
                    render();
                    input.focus();
                });
                chipsBox.appendChild(chip);
            });

            badge.textContent = serials.length + (qty ? ('/' + qty) : '');
            badge.classList.remove('sn-ok', 'sn-over');
            if (qty) {
                if (serials.length === qty) badge.classList.add('sn-ok');
                else if (serials.length > qty) badge.classList.add('sn-over');
            }

            if (hidden) hidden.value = serials.join(',');
        }

        function addFromInput() {
            var raw = input.value.trim();
            input.value = '';
            if (!raw) return;

            // Sebagian scanner/paste bisa mengirim beberapa kode sekaligus dipisah koma
            raw.split(',').forEach(function (part) {
                var sn = part.trim();
                if (!sn) return;
                if (serials.indexOf(sn) !== -1) {
                    // Duplikat: tetap beri tanda visual tapi tidak digandakan
                    input.classList.add('is-invalid');
                    setTimeout(function () { input.classList.remove('is-invalid'); }, 600);
                    return;
                }
                serials.push(sn);
            });

            render();
            input.classList.add('sn-flash');
            setTimeout(function () { input.classList.remove('sn-flash'); }, 150);
            input.focus();
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addFromInput();
            }
        });

        // Kalau input kehilangan fokus tapi masih ada teks tersisa (misal scanner
        // tidak mengirim Enter), tetap simpan supaya tidak hilang begitu saja.
        input.addEventListener('blur', function () {
            if (input.value.trim()) addFromInput();
        });

        render();
    });

    // Fokuskan input scan pertama otomatis, biar siap langsung dipakai scanner
    var firstInput = document.querySelector('.sn-scan-input');
    if (firstInput) firstInput.focus();
}

document.addEventListener('DOMContentLoaded', initSerialScanning);

// Jaga-jaga: pastikan hidden input tersinkron sebelum submit (sudah dihandle
// realtime oleh render(), ini hanya lapisan pengaman tambahan)
document.getElementById('form-checkout').addEventListener('submit', function (e) {
    var overCapacity = false;
    document.querySelectorAll('.sn-scan-wrapper').forEach(function (wrapper) {
        var badge = wrapper.querySelector('.sn-count-badge');
        if (badge.classList.contains('sn-over')) overCapacity = true;
    });
    if (overCapacity && !confirm('Ada jumlah serial number yang melebihi kuantitas barang. Lanjutkan checkout?')) {
        e.preventDefault();
    }
});
</script>
