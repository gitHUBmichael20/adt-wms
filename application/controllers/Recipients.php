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

        $data['title']            = 'Easy WMS - List Penerima';
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

        $data['title']            = 'Easy WMS - Cari Penerima';
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

        if (!$this->recipients->validate()) {
            $data['title']            = 'Easy WMS - Edit Penerima';
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
}

/* End of file Recipients.php */
