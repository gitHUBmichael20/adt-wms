<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Tambah Penerima
 */
class Recipient extends MY_Controller
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

    public function index()
    {
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('warning', 'Anda tidak memiliki akses ke menu ini');
            redirect(base_url('home'));
            return;
        }

        if (!$_POST) {
            $input = (object) $this->recipient->getDefaultValues();
        } else {
            $input = (object) $this->input->post(null, true);
        }

        if (!$this->recipient->validate()) {
            $data['title']            = 'Easy WMS - Register Penerima';
            $data['input']            = $input;
            $data['page']             = 'pages/recipient/index';
            $data['breadcrumb_title'] = 'Register Penerima';
            $data['breadcrumb_path']  = 'Manajemen Penerima / Register Penerima';

            return $this->view($data);
        }

        if ($this->recipient->run($input)) {
            $this->session->set_flashdata('success', 'Berhasil menambahkan penerima');
            redirect(base_url('recipient'));
        } else {
            $this->session->set_flashdata('error', 'Oops terjadi suatu kesalahan');
            redirect(base_url('recipient'));
        }
    }

    public function reset()
    {
        redirect(base_url('recipient'));
    }
}

/* End of file Recipient.php */
