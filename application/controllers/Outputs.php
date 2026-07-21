<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Catatan keluar
 */
class Outputs extends MY_Controller 
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
        
        $data['title']              = 'ADT WMS - List Barang Keluar';
        $data['breadcrumb_title']   = 'List Barang Keluar';
        $data['breadcrumb_path']    = 'Barang Keluar / List Barang Keluar';
        $data['content']            = $this->outputs->select([
                'barang_keluar.id', 'user.nama', 
                'barang_keluar.waktu'
            ])
            ->join('user')
            ->orderBy('barang_keluar.waktu', 'DESC')
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->outputs->count();
        $data['pagination'] = $this->outputs->makePagination(base_url('outputs'), 2, $data['total_rows']);
        $data['page']       = 'pages/outputs/index';
        
        $this->view($data);
    }

    /**
     * Fungsi search berdasarkan id_barang_keluar / nama staff
     */
    public function search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword', $this->input->post('keyword'));
        }

        $this->session->unset_userdata('time');
        $keyword = $this->session->userdata('keyword');

        if (empty($keyword)) {
            redirect(base_url('outputs'));
        }

        $data['title']              = 'ADT WMS - List Barang Keluar';
        $data['breadcrumb_title']   = 'List Barang Keluar';
        $data['breadcrumb_path']    = "Barang Keluar / List Barang Keluar / Cari / $keyword";
        $data['content']            = $this->outputs->select([
                'barang_keluar.id', 'user.nama', 
                'barang_keluar.waktu'
            ])
            ->join('user')
            ->like('barang_keluar.id', $keyword)
            ->orLike('user.nama', $keyword)
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->outputs->join('user')
            ->like('barang_keluar.id', $keyword)
            ->orLike('user.nama', $keyword)
            ->count();
        $data['pagination'] = $this->outputs->makePagination(base_url('outputs/search'), 3, $data['total_rows']);
        $data['page']       = 'pages/outputs/index';

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
            redirect(base_url('outputs'));
        }

        $data['title']              = 'ADT WMS - List Barang Keluar';
        $data['breadcrumb_title']   = 'List Barang Keluar';
        $data['breadcrumb_path']    = "Barang Keluar / List Barang Keluar / Filter / $time";
        $data['content']            = $this->outputs->select([
                'barang_keluar.id', 'user.nama', 
                'barang_keluar.waktu'
            ])
            ->join('user')
            ->like('DATE(barang_keluar.waktu)', date('Y-m-d', strtotime($time)))
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->outputs->join('user')
            ->like('DATE(barang_keluar.waktu)', date('Y-m-d', strtotime($time)))
            ->count();
        $data['pagination'] = $this->outputs->makePagination(base_url('outputs/search_time'), 3, $data['total_rows']);
        $data['page']       = 'pages/outputs/index';

        $this->view($data);
    }

    public function detail($id_barang_keluar)
    {
        $data['title']              = 'ADT WMS - List Barang Keluar';
        $data['breadcrumb_title']   = 'List Barang Keluar';
        $data['breadcrumb_path']    = "Barang Keluar / List Barang Keluar / Detail / $id_barang_keluar";
        $data['page']               = 'pages/outputs/detail';

        $data['barang_keluar']  = $this->outputs->select([
                'user.id AS id_user', 'user.nama',
                'barang_keluar.id AS id_barang_keluar', 'barang_keluar.waktu',
                'barang_keluar.no_po', 'barang_keluar.keterangan',
                'barang_keluar.id_penerima', 'barang_keluar.id_toko'
            ])
            ->join('user')
            ->where('barang_keluar.id', $id_barang_keluar)
            ->first();

        $this->outputs->table = 'barang_keluar_detail';
        $data['list_barang'] = $this->outputs->select([
                'barang_keluar_detail.qty', 'barang_keluar_detail.serial_number',
                'barang.id_satuan', 'barang.nama', 'barang.harga',
            ])
            ->join('barang')
            ->where('barang_keluar_detail.id_barang_keluar', $id_barang_keluar)
            ->get();

        // Load data penerima (perusahaan induk / customer) jika ada
        if ($data['barang_keluar'] && $data['barang_keluar']->id_penerima) {
            $this->load->model('Recipients_model', 'recipients_out');
            $data['penerima'] = $this->recipients_out->where('id', $data['barang_keluar']->id_penerima)->first();
        } else {
            $data['penerima'] = null;
        }

        // Load data toko (cabang / alamat kirim spesifik) jika ada
        if ($data['barang_keluar'] && $data['barang_keluar']->id_toko) {
            $this->load->model('Toko_model', 'toko_out');
            $data['toko'] = $this->toko_out->where('id', $data['barang_keluar']->id_toko)->first();
        } else {
            $data['toko'] = null;
        }

        $this->view($data);
    }

    /**
     * Download Delivery Order Barang Keluar sebagai DOCX
     * URL: outputs/download_docx/{id}
     *
     * - Generate DOCX dari template delivery_order_template.docx
     * - Simpan backup ke application/invoices/out/
     * - Stream DOCX ke browser
     */
    public function download_docx($id_barang_keluar)
    {
        // Ambil data barang keluar
        $barang_keluar = $this->outputs->select([
                'user.id AS id_user', 'user.nama',
                'barang_keluar.id AS id_barang_keluar', 'barang_keluar.waktu',
                'barang_keluar.no_po', 'barang_keluar.keterangan',
                'barang_keluar.id_penerima', 'barang_keluar.id_toko'
            ])
            ->join('user')
            ->where('barang_keluar.id', $id_barang_keluar)
            ->first();

        if (!$barang_keluar) {
            show_404();
            return;
        }

        // Ambil list barang
        $this->outputs->table = 'barang_keluar_detail';
        $list_barang = $this->outputs->select([
                'barang_keluar_detail.qty', 'barang_keluar_detail.serial_number',
                'barang.id_satuan', 'barang.nama', 'barang.harga',
            ])
            ->join('barang')
            ->where('barang_keluar_detail.id_barang_keluar', $id_barang_keluar)
            ->get();

        // Load penerima (perusahaan induk / customer)
        $penerima = null;
        if ($barang_keluar->id_penerima) {
            $this->load->model('Recipients_model', 'recipients_out');
            $penerima = $this->recipients_out->where('id', $barang_keluar->id_penerima)->first();
        }

        // Load toko (cabang / alamat kirim spesifik)
        $toko = null;
        if ($barang_keluar->id_toko) {
            $this->load->model('Toko_model', 'toko_out');
            $toko = $this->toko_out->where('id', $barang_keluar->id_toko)->first();
        }

        // Path backup
        $filename  = 'DO_' . str_pad($id_barang_keluar, 5, '0', STR_PAD_LEFT)
                   . '_' . date('Ymd', strtotime($barang_keluar->waktu)) . '.docx';
        $save_path = APPPATH . 'invoices/out/' . $filename;

        // Load library & generate
        $this->load->library('Docx_generator');
        $this->docx_generator->generate_delivery_order($barang_keluar, $list_barang, $penerima, $toko, $save_path);
    }
}

/* End of file Outputs.php */
