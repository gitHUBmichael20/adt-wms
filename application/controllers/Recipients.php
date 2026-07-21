<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller List Penerima
 */
class Recipients extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $is_login = $this->session->userdata('is_login');

        if (!$is_login) {
            $this->session->set_flashdata('warning', 'Anda belum login');
            redirect(base_url('login'));
            return;
        }
    }

    public function index($page = null)
    {
        $this->session->unset_userdata('keyword');

        $data['title']            = 'ADT WMS - List Penerima';
        $data['breadcrumb_title'] = 'List Penerima';
        $data['breadcrumb_path']  = 'Manajemen Penerima / List Penerima';
        $data['content']          = $this->recipients->paginate($page)->get();
        $data['total_rows']       = $this->recipients->count();
        $data['pagination']       = $this->recipients->makePagination(base_url('recipients'), 2, $data['total_rows']);
        $data['page']             = 'pages/recipients/index';

        $this->view($data);
    }

    public function search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword', $this->input->post('keyword'));
        }

        $keyword = $this->session->userdata('keyword');

        if (empty($keyword)) {
            redirect(base_url('recipients'));
        }

        $data['title']            = 'ADT WMS - Cari Penerima';
        $data['breadcrumb_title'] = 'List Penerima';
        $data['breadcrumb_path']  = "List Penerima / Cari / $keyword";
        $data['content']          = $this->recipients->paginate($page)
                                        ->like('nama', $keyword)
                                        ->orLike('divisi', $keyword)
                                        ->paginate($page)
                                        ->get();
        $data['total_rows']       = $this->recipients->like('nama', $keyword)
                                        ->orLike('divisi', $keyword)
                                        ->count();
        $data['pagination']       = $this->recipients->makePagination(base_url('recipients/search'), 3, $data['total_rows']);
        $data['page']             = 'pages/recipients/index';

        $this->view($data);
    }

    public function edit($id)
    {
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Akses edit ditolak!');
            redirect(base_url('home'));
        }

        $data['content'] = $this->recipients->where('id', $id)->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('recipients'));
        }

        if (!$_POST) {
            $data['input'] = $data['content'];
        } else {
            $data['input'] = (object) $this->input->post(null, true);
        }

        // Load toko (cabang) milik penerima ini untuk ditampilkan & dikelola
        // di halaman yang sama
        $this->load->model('Toko_model', 'toko');
        $data['stores'] = $this->toko->getByPenerima($id);

        // Jika ada ?edit_toko=<id>, tampilkan form edit inline untuk toko tsb
        $edit_toko = $this->input->get('edit_toko');
        if ($edit_toko) {
            $store = $this->toko->where('id', $edit_toko)
                ->where('id_penerima', $id)
                ->first();

            if ($store) {
                $data['edit_store_id'] = $store->id;
                $data['store_input']   = $store;
            }
        }

        if (!$this->recipients->validate()) {
            $data['title']            = 'ADT WMS - Edit Penerima';
            $data['page']             = 'pages/recipients/edit';
            $data['breadcrumb_title'] = 'Edit Data Penerima';
            $data['breadcrumb_path']  = 'Manajemen Penerima / Edit Data Penerima / ' . $data['input']->nama;

            return $this->view($data);
        }

        $updateData = [
            'nama'    => $data['input']->nama,
            'divisi'  => $data['input']->divisi,
            'telefon' => $data['input']->telefon,
            'alamat'  => $data['input']->alamat,
        ];

        if ($this->recipients->where('id', $id)->update($updateData)) {
            $this->session->set_flashdata('success', 'Data berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url('recipients'));
    }

    /**
     * ================= MANAJEMEN TOKO (CABANG) =================
     * Satu penerima (perusahaan induk) bisa memiliki banyak toko/cabang.
     * Dikelola langsung dari halaman edit penerima.
     */

    /**
     * Tambah toko baru untuk penerima tertentu
     */
    public function store_add($id_penerima)
    {
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect(base_url('home'));
        }

        $penerima = $this->recipients->where('id', $id_penerima)->first();
        if (!$penerima) {
            $this->session->set_flashdata('warning', 'Penerima tidak ditemukan');
            redirect(base_url('recipients'));
        }

        if (!$_POST) {
            redirect(base_url("recipients/edit/$id_penerima"));
            return;
        }

        $this->load->model('Toko_model', 'toko');

        $input = (object) $this->input->post(null, true);
        $input->id_penerima = $id_penerima;

        if (!$this->toko->validate()) {
            // Tampilkan ulang halaman edit penerima dengan error toko
            $data['content']      = $penerima;
            $data['input']        = $penerima;
            $data['stores']       = $this->toko->getByPenerima($id_penerima);
            $data['store_input']  = $input;
            $data['title']            = 'ADT WMS - Edit Penerima';
            $data['page']             = 'pages/recipients/edit';
            $data['breadcrumb_title'] = 'Edit Data Penerima';
            $data['breadcrumb_path']  = 'Manajemen Penerima / Edit Data Penerima / ' . $penerima->nama;

            return $this->view($data);
        }

        if ($this->toko->run($input)) {
            $this->session->set_flashdata('success', 'Toko berhasil ditambahkan');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url("recipients/edit/$id_penerima"));
    }

    /**
     * Edit toko yang sudah ada
     */
    public function store_edit($id_toko)
    {
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect(base_url('home'));
        }

        $this->load->model('Toko_model', 'toko');
        $store = $this->toko->where('id', $id_toko)->first();

        if (!$store) {
            $this->session->set_flashdata('warning', 'Toko tidak ditemukan');
            redirect(base_url('recipients'));
        }

        if (!$_POST) {
            redirect(base_url("recipients/edit/$store->id_penerima"));
            return;
        }

        $input = (object) $this->input->post(null, true);
        $input->id_penerima = $store->id_penerima;

        if (!$this->toko->validate()) {
            $penerima = $this->recipients->where('id', $store->id_penerima)->first();
            $data['content']        = $penerima;
            $data['input']          = $penerima;
            $data['stores']         = $this->toko->getByPenerima($store->id_penerima);
            $data['edit_store_id']  = $id_toko;
            $data['store_input']    = $input;
            $data['title']            = 'ADT WMS - Edit Penerima';
            $data['page']             = 'pages/recipients/edit';
            $data['breadcrumb_title'] = 'Edit Data Penerima';
            $data['breadcrumb_path']  = 'Manajemen Penerima / Edit Data Penerima / ' . $penerima->nama;

            return $this->view($data);
        }

        $updateData = [
            'nama_toko' => $input->nama_toko,
            'alamat'    => $input->alamat,
            'pic'       => !empty($input->pic) ? $input->pic : null,
            'telefon'   => !empty($input->telefon) ? $input->telefon : null,
        ];

        if ($this->toko->where('id', $id_toko)->update($updateData)) {
            $this->session->set_flashdata('success', 'Toko berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url("recipients/edit/$store->id_penerima"));
    }

    /**
     * Hapus toko
     */
    public function store_delete()
    {
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect(base_url('home'));
        }

        if (!$_POST) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect(base_url('recipients'));
        }

        $id_toko = $this->input->post('id_toko');

        $this->load->model('Toko_model', 'toko');
        $store = $this->toko->where('id', $id_toko)->first();

        if (!$store) {
            $this->session->set_flashdata('warning', 'Toko tidak ditemukan');
            redirect(base_url('recipients'));
        }

        if ($this->toko->where('id', $id_toko)->delete()) {
            $this->session->set_flashdata('success', 'Toko berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan. Pastikan toko ini tidak sedang dipakai di transaksi barang keluar.');
        }

        redirect(base_url("recipients/edit/$store->id_penerima"));
    }
}

/* End of file Recipients.php */
