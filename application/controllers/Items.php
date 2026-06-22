<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller List Barang
 * Memisahkan halaman barang masuk (in) dan barang keluar (out)
 */
class Items extends MY_Controller
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

    // ─── Halaman lama (redirect ke in) ────────────────────────────────────────
    public function index($page = null)
    {
        redirect(base_url('items/in'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BARANG MASUK (IN)
    // ══════════════════════════════════════════════════════════════════════════

    /** Halaman utama pilih barang untuk dimasukkan ke keranjang masuk */
    public function in($page = null)
    {
        $this->session->unset_userdata('keyword_in');

        $data['title']            = 'Easy WMS - Barang Masuk';
        $data['breadcrumb_title'] = 'Barang Masuk';
        $data['breadcrumb_path']  = 'Barang Masuk / Pilih Barang';
        $data['content']          = $this->_getItemsQuery()->paginate($page)->get();
        $data['total_rows']       = $this->items->count();
        $data['pagination']       = $this->items->makePagination(base_url('items/in'), 2, $data['total_rows']);
        $data['page']             = 'pages/items/index_in';

        $this->view($data);
    }

    public function in_unit($id_unit, $page = null)
    {
        $this->session->unset_userdata('keyword_in');

        $data['title']            = 'Easy WMS - Barang Masuk';
        $data['breadcrumb_title'] = 'Barang Masuk';
        $data['breadcrumb_path']  = 'Barang Masuk / Filter / ' . ucfirst(getUnitName($id_unit));
        $data['content']          = $this->_getItemsQuery()->where('id_satuan', $id_unit)->paginate($page)->get();
        $data['total_rows']       = $this->items->where('id_satuan', $id_unit)->count();
        $data['pagination']       = $this->items->makePagination(base_url("items/in/unit/$id_unit"), 4, $data['total_rows']);
        $data['page']             = 'pages/items/index_in';

        $this->view($data);
    }

    public function in_availability($param, $page = null)
    {
        $this->session->unset_userdata('keyword_in');

        $data['title']            = 'Easy WMS - Barang Masuk';
        $data['breadcrumb_title'] = 'Barang Masuk';
        $data['breadcrumb_path']  = 'Barang Masuk / Ketersediaan / ' . ucfirst($param);
        $data['page']             = 'pages/items/index_in';

        $condition = ($param === 'available') ? ['qty >', 0] : ['qty', 0];
        $data['content']    = $this->_getItemsQuery()->where($condition[0], $condition[1])->paginate($page)->get();
        $data['total_rows'] = $this->items->where($condition[0], $condition[1])->count();
        $data['pagination'] = $this->items->makePagination(base_url("items/in/availability/$param"), 4, $data['total_rows']);

        $this->view($data);
    }

    public function in_search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword_in', $this->input->post('keyword'));
        }

        $keyword = $this->session->userdata('keyword_in');
        if (empty($keyword)) redirect(base_url('items/in'));

        $data['title']            = 'Easy WMS - Barang Masuk';
        $data['breadcrumb_title'] = 'Barang Masuk';
        $data['breadcrumb_path']  = "Barang Masuk / Cari / $keyword";
        $data['content']          = $this->_getItemsQuery()->like('barang.nama', $keyword)->paginate($page)->get();
        $data['total_rows']       = $this->items->like('nama', $keyword)->count();
        $data['pagination']       = $this->items->makePagination(base_url('items/in/search'), 3, $data['total_rows']);
        $data['page']             = 'pages/items/index_in';

        $this->view($data);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BARANG KELUAR (OUT)
    // ══════════════════════════════════════════════════════════════════════════

    /** Halaman utama pilih barang untuk dimasukkan ke keranjang keluar */
    public function out($page = null)
    {
        $this->session->unset_userdata('keyword_out');

        $data['title']            = 'Easy WMS - Barang Keluar';
        $data['breadcrumb_title'] = 'Barang Keluar';
        $data['breadcrumb_path']  = 'Barang Keluar / Pilih Barang';
        $data['content']          = $this->_getItemsQuery()->paginate($page)->get();
        $data['total_rows']       = $this->items->count();
        $data['pagination']       = $this->items->makePagination(base_url('items/out'), 2, $data['total_rows']);
        $data['page']             = 'pages/items/index_out';

        $this->view($data);
    }

    public function out_unit($id_unit, $page = null)
    {
        $this->session->unset_userdata('keyword_out');

        $data['title']            = 'Easy WMS - Barang Keluar';
        $data['breadcrumb_title'] = 'Barang Keluar';
        $data['breadcrumb_path']  = 'Barang Keluar / Filter / ' . ucfirst(getUnitName($id_unit));
        $data['content']          = $this->_getItemsQuery()->where('id_satuan', $id_unit)->paginate($page)->get();
        $data['total_rows']       = $this->items->where('id_satuan', $id_unit)->count();
        $data['pagination']       = $this->items->makePagination(base_url("items/out/unit/$id_unit"), 4, $data['total_rows']);
        $data['page']             = 'pages/items/index_out';

        $this->view($data);
    }

    public function out_availability($param, $page = null)
    {
        $this->session->unset_userdata('keyword_out');

        $data['title']            = 'Easy WMS - Barang Keluar';
        $data['breadcrumb_title'] = 'Barang Keluar';
        $data['breadcrumb_path']  = 'Barang Keluar / Ketersediaan / ' . ucfirst($param);
        $data['page']             = 'pages/items/index_out';

        $condition = ($param === 'available') ? ['qty >', 0] : ['qty', 0];
        $data['content']    = $this->_getItemsQuery()->where($condition[0], $condition[1])->paginate($page)->get();
        $data['total_rows'] = $this->items->where($condition[0], $condition[1])->count();
        $data['pagination'] = $this->items->makePagination(base_url("items/out/availability/$param"), 4, $data['total_rows']);

        $this->view($data);
    }

    public function out_search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword_out', $this->input->post('keyword'));
        }

        $keyword = $this->session->userdata('keyword_out');
        if (empty($keyword)) redirect(base_url('items/out'));

        $data['title']            = 'Easy WMS - Barang Keluar';
        $data['breadcrumb_title'] = 'Barang Keluar';
        $data['breadcrumb_path']  = "Barang Keluar / Cari / $keyword";
        $data['content']          = $this->_getItemsQuery()->like('barang.nama', $keyword)->paginate($page)->get();
        $data['total_rows']       = $this->items->like('nama', $keyword)->count();
        $data['pagination']       = $this->items->makePagination(base_url('items/out/search'), 3, $data['total_rows']);
        $data['page']             = 'pages/items/index_out';

        $this->view($data);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────
    private function _getItemsQuery()
    {
        return $this->items->select([
            'barang.id AS id_barang', 'barang.nama AS nama_barang', 'qty', 'harga',
            'barang.kena_pajak',
            'supplier.nama AS nama_supplier', 'satuan.nama AS nama_satuan',
            'barang.id_satuan'
        ])
        ->join('supplier')
        ->join('satuan');
    }

    // ─── Search lama (untuk kompatibilitas jika masih ada link lama) ──────────
    public function search($page = null)
    {
        redirect(base_url('items/in'));
    }

    public function unit($id_unit, $page = null)
    {
        redirect(base_url("items/in/unit/$id_unit"));
    }

    public function availability($param, $page = null)
    {
        redirect(base_url("items/in/availability/$param"));
    }
}

/* End of file Items.php */
