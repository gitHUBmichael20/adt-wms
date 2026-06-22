<!-- ============================================================== -->
<!-- Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->
<aside class="left-sidebar" data-sidebarbg="skin6">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar" data-sidebarbg="skin6">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <!-- Submemu Dashboard -->
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="<?= base_url('home') ?>" aria-expanded="false">
                        <i data-feather="home" class="feather-icon"></i>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

                <li class="list-divider"></li>

                <li class="nav-small-cap"><span class="hide-menu">Manajemen Barang</span></li>
                <?php if ($this->session->userdata('role') == 'admin') : ?>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= base_url('item') ?>" aria-expanded="false">
                            <i data-feather="clipboard" class="feather-icon"></i>
                            <span class="hide-menu">Register Barang</span>
                        </a>
                    </li>
                <?php endif ?>

                <?php if ($this->session->userdata('role') == 'admin') : ?>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= base_url('unit') ?>" aria-expanded="false">
                            <i data-feather="plus-square" class="feather-icon"></i>
                            <span class="hide-menu">Tambah Satuan</span>
                        </a>
                    </li>
                <?php endif ?>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="<?= base_url('units') ?>" aria-expanded="false">
                        <i data-feather="box" class="feather-icon"></i>
                        <span class="hide-menu">List Satuan</span>
                    </a>
                </li>

                <li class="list-divider"></li>

                <li class="nav-small-cap"><span class="hide-menu">Manajemen Supplier</span></li>
                <?php if ($this->session->userdata('role') == 'admin') : ?>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= base_url('supplier') ?>" aria-expanded="false">
                            <i data-feather="file-plus" class="feather-icon"></i>
                            <span class="hide-menu">Tambah Supplier</span>
                        </a>
                    </li>
                <?php endif ?>
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="<?= base_url('suppliers') ?>" aria-expanded="false">
                        <i data-feather="truck" class="feather-icon"></i>
                        <span class="hide-menu">List Supplier</span>
                    </a>
                </li>

                <li class="list-divider"></li>

                <li class="nav-small-cap"><span class="hide-menu">Manajemen Penerima</span></li>
                <?php if ($this->session->userdata('role') == 'admin') : ?>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= base_url('recipient') ?>" aria-expanded="false">
                            <i data-feather="user-plus" class="feather-icon"></i>
                            <span class="hide-menu">Tambah Penerima</span>
                        </a>
                    </li>
                <?php endif ?>
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="<?= base_url('recipients') ?>" aria-expanded="false">
                        <i data-feather="users" class="feather-icon"></i>
                        <span class="hide-menu">List Penerima</span>
                    </a>
                </li>

                <li class="list-divider"></li>

                <!-- Submemu Barang Masuk -->
                <li class="nav-small-cap"><span class="hide-menu">Barang Masuk</span></li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="<?= base_url('items/in') ?>" aria-expanded="false">
                        <i data-feather="log-in" class="feather-icon"></i>
                        <span class="hide-menu">Pilih Barang Masuk</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="<?= base_url('cartin') ?>" aria-expanded="false">
                        <i data-feather="shopping-cart" class="feather-icon"></i>
                        <span class="hide-menu">Keranjang Masuk</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="<?= base_url('inputs') ?>" aria-expanded="false">
                        <i data-feather="file-text" class="feather-icon"></i>
                        <span class="hide-menu">Catatan Masuk</span>
                    </a>
                </li>

                <li class="list-divider"></li>

                <!-- Submemu Barang Keluar -->
                <li class="nav-small-cap"><span class="hide-menu">Barang Keluar</span></li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="<?= base_url('items/out') ?>" aria-expanded="false">
                        <i data-feather="log-out" class="feather-icon"></i>
                        <span class="hide-menu">Pilih Barang Keluar</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="<?= base_url('cartout') ?>" aria-expanded="false">
                        <i data-feather="shopping-cart" class="feather-icon"></i>
                        <span class="hide-menu">Keranjang Keluar</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="<?= base_url('outputs') ?>" aria-expanded="false">
                        <i data-feather="file-text" class="feather-icon"></i>
                        <span class="hide-menu">Catatan Keluar</span>
                    </a>
                </li>

                <li class="list-divider"></li>

                <!-- Submemu Manajemen Karyawan -->
                <li class="nav-small-cap"><span class="hide-menu">Manajemen Staff</span></li>
                <li class="sidebar-item"> 
                    <a class="sidebar-link sidebar-link" href="<?= base_url('users') ?>" aria-expanded="false">
                        <i data-feather="users" class="feather-icon"></i>
                        <span class="hide-menu">List Staff</span>
                    </a>
                </li>
                <?php if ($this->session->userdata('role') == 'admin') : ?>
                    <li class="sidebar-item"> 
                        <a class="sidebar-link sidebar-link" href="<?= base_url('register') ?>" aria-expanded="false">
                            <i data-feather="user-plus" class="feather-icon"></i>
                            <span class="hide-menu">Register Staff</span>
                        </a>
                    </li>
                <?php endif ?>
                
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<!-- ============================================================== -->
<!-- End Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->