<!-- ============================================================== -->
<!-- Container fluid  -->
<!-- ============================================================== -->
<div class="container-fluid">
    
    <?php $this->load->view('layouts/_alert') ?>

    <!-- Row 1: Existing Stats -->
    <div class="row">
        <div class="col-md-12">
        <div class="card-group">
                    <div class="card border-right">
                        <div class="card-body">
                            <div class="d-flex d-lg-flex d-md-block align-items-center">
                                <div>
                                    <div class="d-inline-flex align-items-center">
                                        <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahStaff(); ?></h2>
                                    </div>
                                    <a href="<?= base_url('users') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Staff</h4></a>
                                </div>
                                <div class="ml-auto mt-md-3 mt-lg-0">
                                    <span class="opacity-7 text-muted"><i data-feather="user"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border-right">
                        <div class="card-body">
                            <div class="d-flex d-lg-flex d-md-block align-items-center">
                                <div>
                                    <div class="d-inline-flex align-items-center">
                                        <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahBarang(); ?></h2>
                                    </div>
                                    <a href="<?= base_url('items') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Barang</h4></a>
                                </div>
                                <div class="ml-auto mt-md-3 mt-lg-0">
                                    <span class="opacity-7 text-muted"><i class="fas fa-boxes"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border-right">
                        <div class="card-body">
                            <div class="d-flex d-lg-flex d-md-block align-items-center">
                                <div>
                                        <div class="d-inline-flex align-items-center">
                                            <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahSupplier(); ?></h2>
                                        </div>
                                        <a href="<?= base_url('suppliers') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Supplier</h4></a>
                                </div>
                                <div class="ml-auto mt-md-3 mt-lg-0">
                                    <span class="opacity-7 text-muted"><i class="fas fa-users"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex d-lg-flex d-md-block align-items-center">
                                <div>
                                    <div class="d-inline-flex align-items-center">
                                        <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahStok(); ?></h2>
                                    </div>
                                    <a href="<?= base_url('items') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Stok</h4></a>
                                </div>
                                <div class="ml-auto mt-md-3 mt-lg-0">
                                    <span class="opacity-7 text-muted"><i class="fas fa-box"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <!-- Row 2: Additional Stats -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card-group">
                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahPemasukan(); ?></h2>
                                </div>
                                <a href="<?= base_url('inputs') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Pemasukan</h4></a>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-success"><i class="fas fa-arrow-down"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahPengeluaran(); ?></h2>
                                </div>
                                <a href="<?= base_url('outputs') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Pengeluaran</h4></a>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-danger"><i class="fas fa-arrow-up"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium"><?= getJumlahPenerima(); ?></h2>
                                </div>
                                <a href="<?= base_url('recipients') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Penerima</h4></a>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-info"><i class="fas fa-address-book"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium <?= getJumlahBarangHabis() > 0 ? 'text-danger' : '' ?>"><?= getJumlahBarangHabis(); ?></h2>
                                </div>
                                <a href="<?= base_url('items/availability/empty') ?>" class="btn"><h4 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Barang Habis</h4></a>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-danger"><i class="fas fa-exclamation-triangle"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Stock Summary + Monthly Chart -->
    <div class="row mt-4">
        <!-- Stock Distribution Donut -->
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Distribusi Stok Barang</h4>
                    <div class="d-flex align-items-center justify-content-center" style="height: 280px;">
                        <canvas id="stockDonutChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <span class="legend-dot" style="background:#28a745;"></span> Tersedia: <strong><?= getJumlahBarang(); ?></strong> &nbsp;&nbsp;
                        <span class="legend-dot" style="background:#dc3545;"></span> Habis: <strong><?= getJumlahBarangHabis(); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <!-- Monthly In/Out Bar Chart -->
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Arus Barang Masuk & Keluar (6 Bulan Terakhir)</h4>
                    <div style="height: 320px;">
                        <canvas id="monthlyBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Today's Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Ringkasan Hari Ini</h4>
                    <div class="row text-center">
                        <div class="col-md-3 col-6 border-right py-3">
                            <h2 class="text-success font-weight-medium mb-1"><?= getTodayMasukCount(); ?></h2>
                            <p class="text-muted mb-0"><i class="fas fa-arrow-circle-down"></i> Pemasukan Hari Ini</p>
                        </div>
                        <div class="col-md-3 col-6 border-right py-3">
                            <h2 class="text-danger font-weight-medium mb-1"><?= getTodayKeluarCount(); ?></h2>
                            <p class="text-muted mb-0"><i class="fas fa-arrow-circle-up"></i> Pengeluaran Hari Ini</p>
                        </div>
                        <div class="col-md-3 col-6 border-right py-3">
                            <h2 class="text-primary font-weight-medium mb-1"><?= getJumlahTotalBarang(); ?></h2>
                            <p class="text-muted mb-0"><i class="fas fa-clipboard-list"></i> Total Jenis Barang</p>
                        </div>
                        <div class="col-md-3 col-6 py-3">
                            <h2 class="text-warning font-weight-medium mb-1">
                                <?php
                                    $total = getJumlahTotalBarang();
                                    $habis = getJumlahBarangHabis();
                                    echo $total > 0 ? round(($habis / $total) * 100, 1) . '%' : '0%';
                                ?>
                            </h2>
                            <p class="text-muted mb-0"><i class="fas fa-chart-pie"></i> Persentase Barang Habis</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Aktivitas Terakhir Pemasukan Barang</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table no-wrap v-middle mb-0">
                            <thead>
                                <tr class="border-0">
                                    <th class="border-0 font-14 font-weight-medium text-muted px-2">ID Pemasukan</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted px-2">Nama Staff</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted px-2 text-center">Waktu Pemasukan</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barang_masuk as $row) : ?>
                                    <tr>
                                        <td class="border-top-0 px-2 py-4 font-weight-medium"><?= $row->id ?></td>
                                        <td class="border-top-0 text-muted px-2 py-4 font-14"><?= $row->nama ?></td>
                                        <td class="border-top-0 text-muted px-2 py-4 font-14 text-center"><?= date('d-m-Y H:i:s', strtotime($row->waktu)) ?></td>
                                        <td class="border-top-0 text-center text-muted px-2 py-4">
                                            <a href="<?= base_url("inputs/detail/$row->id") ?>" class="btn btn-primary btn-rounded"><i data-feather="shopping-cart"></i>&nbsp;&nbsp;Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Aktivitas Terakhir Pengeluaran Barang</h4>
                    </div>
                    <div class="table-responsive">
                    <table class="table no-wrap v-middle mb-0">
                            <thead>
                                <tr class="border-0">
                                    <th class="border-0 font-14 font-weight-medium text-muted px-2">ID Pemasukan</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted px-2">Nama Staff</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted px-2 text-center">Waktu Pemasukan</th>
                                    <th class="border-0 font-14 font-weight-medium text-muted"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barang_keluar as $row) : ?>
                                    <tr>
                                        <td class="border-top-0 px-2 py-4 font-weight-medium"><?= $row->id ?></td>
                                        <td class="border-top-0 text-muted px-2 py-4 font-14"><?= $row->nama ?></td>
                                        <td class="border-top-0 text-muted px-2 py-4 font-14 text-center"><?= date('d-m-Y H:i:s', strtotime($row->waktu)) ?></td>
                                        <td class="border-top-0 text-center text-muted px-2 py-4">
                                            <a href="<?= base_url("outputs/detail/$row->id") ?>" class="btn btn-primary btn-rounded"><i data-feather="shopping-cart"></i>&nbsp;&nbsp;Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Container fluid  -->
<!-- ============================================================== -->

<!-- Chart.js CDN -->
<script src="<?= base_url('assets/libs/chart.js/Chart.bundle.min.js') ?>"></script>

<!-- Inline CSS for legend dots -->
<style>
.legend-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 4px;
    vertical-align: middle;
}
</style>

<!-- Chart Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ---- Stock Donut Chart ----
    var donutCtx = document.getElementById('stockDonutChart').getContext('2d');
    var tersedia = <?= getJumlahBarang(); ?>;
    var habis = <?= getJumlahBarangHabis(); ?>;

    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tersedia', 'Habis'],
            datasets: [{
                data: [tersedia, habis],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 65,
            legend: { display: false },
            plugins: {
                datalabels: false
            }
        }
    });

    // ---- Monthly Bar Chart ----
    var barCtx = document.getElementById('monthlyBarChart').getContext('2d');
    var labels = <?= $chart_labels ?>;
    var masukData = <?= $chart_masuk ?>;
    var keluarData = <?= $chart_keluar ?>;

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Barang Masuk',
                    data: masukData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Barang Keluar',
                    data: keluarData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    gridLines: { color: 'rgba(0,0,0,0.05)' }
                }],
                xAxes: [{
                    gridLines: { display: false }
                }]
            },
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            }
        }
    });

    // Re-init feather icons for dynamically rendered content
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>