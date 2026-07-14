<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Catatan Masuk
 */
class Inputs extends MY_Controller 
{
    private $id_user;

    public function __construct()
    {
        parent::__construct();
        
        $this->id_user  = $this->session->userdata('id_user');
        $is_login       = $this->session->userdata('is_login');

        if (!$is_login) {
            $this->session->set_flashdata('warning', 'Anda belum login');
            redirect(base_url('login'));
            return;
        }
    }

    public function index($page = null)
    {
        $this->session->unset_userdata('keyword');
        $this->session->unset_userdata('time');
        
        $data['title']              = 'Easy WMS - List Barang Masuk';
        $data['breadcrumb_title']   = 'List Barang Masuk';
        $data['breadcrumb_path']    = 'Barang Masuk / List Barang Masuk';
        $data['content']            = $this->inputs->select([
                'barang_masuk.id', 'user.nama', 
                'barang_masuk.waktu', 'barang_masuk.total_harga',
                'supplier.nama AS nama_supplier'
            ])
            ->join('user')
            ->join('supplier')
            ->orderBy('barang_masuk.waktu', 'DESC')
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->inputs->count();
        $data['pagination'] = $this->inputs->makePagination(base_url('inputs'), 2, $data['total_rows']);
        $data['page']       = 'pages/inputs/index';
        
        $this->view($data);
    }

    /**
     * Fungsi search berdasarkan id_barang_masuk / nama staff
     */
    public function search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword', $this->input->post('keyword'));
        }

        $this->session->unset_userdata('time');
        $keyword = $this->session->userdata('keyword');

        if (empty($keyword)) {
            redirect(base_url('inputs'));
        }

        $data['title']              = 'Easy WMS - List Barang Masuk';
        $data['breadcrumb_title']   = "List Barang Masuk";
        $data['breadcrumb_path']    = "Barang Masuk / List Penjualan / Cari / $keyword";
        $data['content']            = $this->inputs->select([
                'barang_masuk.id', 'user.nama', 
                'barang_masuk.waktu', 'barang_masuk.total_harga',
                'supplier.nama AS nama_supplier'
            ])
            ->join('user')
            ->join('supplier')
            ->like('barang_masuk.id', $keyword)
            ->orLike('user.nama', $keyword)
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->inputs->join('user')
            ->like('barang_masuk.id', $keyword)
            ->orLike('user.nama', $keyword)
            ->count();
        $data['pagination'] = $this->inputs->makePagination(base_url('inputs/search'), 3, $data['total_rows']);
        $data['page']       = 'pages/inputs/index';

        $this->view($data);
    }

    /**
     * Fungsi search berdasarkan waktu
     */
    public function search_time($page = null)
    {
        if (isset($_POST['time'])) {
            $this->session->set_userdata('time', $this->input->post('time'));
        }

        $this->session->unset_userdata('keyword');
        $time = $this->session->userdata('time');

        if (empty($time)) {
            redirect(base_url('inputs'));
        }

        $data['title']              = 'Easy WMS - List Barang Masuk';
        $data['breadcrumb_title']   = "List Barang Masuk";
        $data['breadcrumb_path']    = "Barang Masuk / List Barang Masuk / Filter / $time";
        $data['content']            = $this->inputs->select([
                'barang_masuk.id', 'user.nama', 
                'barang_masuk.waktu', 'barang_masuk.total_harga',
                'supplier.nama AS nama_supplier'
            ])
            ->join('user')
            ->join('supplier')
            ->like('DATE(barang_masuk.waktu)', date('Y-m-d', strtotime($time)))
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->inputs->join('user')
            ->like('DATE(barang_masuk.waktu)', date('Y-m-d', strtotime($time)))
            ->count();
        $data['pagination'] = $this->inputs->makePagination(base_url('inputs/search_time'), 3, $data['total_rows']);
        $data['page']       = 'pages/inputs/index';

        $this->view($data);
    }

    public function detail($id_barang_masuk)
    {
        $data['title']              = 'Easy WMS - Detail Barang Masuk';
        $data['breadcrumb_title']   = "Detail Barang Masuk";
        $data['breadcrumb_path']    = "Barang Masuk / List Barang Masuk / Detail Barang Masuk / $id_barang_masuk";
        $data['page']               = 'pages/inputs/detail';

        $data['barang_masuk']  = $this->inputs->select([
                'user.id AS id_user', 'user.nama',
                'barang_masuk.id AS id_barang_masuk', 'barang_masuk.waktu',
                'supplier.nama    AS nama_supplier',
                'supplier.telefon AS telefon_supplier',
                'supplier.email   AS email_supplier',
                'supplier.alamat  AS alamat_supplier',
            ])
            ->join('user')
            ->join('supplier')
            ->where('barang_masuk.id', $id_barang_masuk)
            ->where('barang_masuk.id_user', $this->id_user)
            ->first();

        // Simpan table asli, ubah sementara, lalu reset kembali setelah query selesai
        $original_table = $this->inputs->table;
        $this->inputs->table = 'barang_masuk_detail';
        $data['list_barang'] = $this->inputs->select([
                'barang_masuk_detail.qty', 'barang_masuk_detail.subtotal',
                'barang.id_satuan', 'barang.nama', 'barang.harga',
            ])
            ->join('barang')
            ->where('barang_masuk_detail.id_barang_masuk', $id_barang_masuk)
            ->get();
        $this->inputs->table = $original_table;

        $this->view($data);
    }

    /**
     * Form kustomisasi invoice sebelum di-download sebagai DOCX
     * URL: inputs/invoice_form/{id}
     *
     * Ditampilkan saat user klik tombol "Download Invoice DOCX" di halaman detail.
     * Form ini submit (POST) ke inputs/download_docx/{id}.
     */
    public function invoice_form($id_barang_masuk)
    {
        $data['title']              = 'Easy WMS - Kustomisasi Invoice';
        $data['breadcrumb_title']   = 'Kustomisasi Invoice';
        $data['breadcrumb_path']    = "Barang Masuk / Detail / Kustomisasi Invoice / $id_barang_masuk";
        $data['page']               = 'pages/inputs/invoice_form';

        $data['barang_masuk'] = $this->inputs->select([
                'user.id AS id_user', 'user.nama',
                'barang_masuk.id AS id_barang_masuk', 'barang_masuk.waktu',
                'supplier.nama    AS nama_supplier',
                'supplier.telefon AS telefon_supplier',
                'supplier.email   AS email_supplier',
                'supplier.alamat  AS alamat_supplier',
            ])
            ->join('user')
            ->join('supplier')
            ->where('barang_masuk.id', $id_barang_masuk)
            ->where('barang_masuk.id_user', $this->id_user)
            ->first();

        if (!$data['barang_masuk']) {
            show_404();
            return;
        }

        $this->view($data);
    }

    /**
     * Download Invoice Barang Masuk sebagai DOCX
     * URL: inputs/download_docx/{id}
     *
     * - Generate DOCX dari template invoice_template.docx
     * - Simpan backup ke application/invoices/in/
     * - Stream DOCX ke browser (print dialog akan muncul otomatis setelah user buka file)
     */
    public function download_docx($id_barang_masuk)
    {
        // Ambil data barang masuk
        $barang_masuk = $this->inputs->select([
                'user.id AS id_user', 'user.nama',
                'barang_masuk.id AS id_barang_masuk', 'barang_masuk.waktu',
                'supplier.nama    AS nama_supplier',
                'supplier.telefon AS telefon_supplier',
                'supplier.email   AS email_supplier',
                'supplier.alamat  AS alamat_supplier',
            ])
            ->join('user')
            ->join('supplier')
            ->where('barang_masuk.id', $id_barang_masuk)
            ->where('barang_masuk.id_user', $this->id_user)
            ->first();

        if (!$barang_masuk) {
            show_404();
            return;
        }

        // Terapkan override dari form kustomisasi (inputs/invoice_form), jika ada
        $custom_date  = trim((string) $this->input->post('custom_date'));
        $inv_no_date  = trim((string) $this->input->post('inv_no_date'));
        $inv_no_num   = trim((string) $this->input->post('inv_no_number'));
        $custom_no_sp = trim((string) $this->input->post('custom_no_sp'));
        $custom_btb   = trim((string) $this->input->post('custom_btb'));

        if ($custom_date !== '') {
            $barang_masuk->custom_date = $custom_date;
        }
        if ($inv_no_date !== '' || $inv_no_num !== '') {
            $barang_masuk->custom_inv_no = 'FDI/INV/'
                . ($inv_no_date !== '' ? $inv_no_date : date('Y/m', strtotime($barang_masuk->waktu)))
                . '/'
                . ($inv_no_num !== '' ? $inv_no_num : str_pad($id_barang_masuk, 5, '0', STR_PAD_LEFT));
        }
        if ($custom_no_sp !== '') {
            $barang_masuk->custom_no_sp = $custom_no_sp;
        }
        if ($custom_btb !== '') {
            $barang_masuk->custom_btb = $custom_btb;
        }

        // Ambil list barang
        $original_table = $this->inputs->table;
        $this->inputs->table = 'barang_masuk_detail';
        $list_barang = $this->inputs->select([
                'barang_masuk_detail.qty', 'barang_masuk_detail.subtotal',
                'barang.id_satuan', 'barang.nama', 'barang.harga',
            ])
            ->join('barang')
            ->where('barang_masuk_detail.id_barang_masuk', $id_barang_masuk)
            ->get();
        $this->inputs->table = $original_table;

        // Path backup
        $filename  = 'INV_' . str_pad($id_barang_masuk, 5, '0', STR_PAD_LEFT)
                   . '_' . date('Ymd', strtotime($barang_masuk->waktu)) . '.docx';
        $save_path = APPPATH . 'invoices/in/' . $filename;

        // Load library & generate
        $this->load->library('Docx_generator');
        $this->docx_generator->generate_invoice($barang_masuk, $list_barang, $save_path);
    }
}

/* End of file Inputs.php */
