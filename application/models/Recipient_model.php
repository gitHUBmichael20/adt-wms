<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recipient_model extends MY_Model
{
    protected $table = 'penerima';

    public function getDefaultValues()
    {
        return [
            'nama'      => '',
            'divisi'    => '',
            'telefon'   => '',
            'alamat'    => '',
        ];
    }

    public function getValidationRules()
    {
        return [
            [
                'field'  => 'nama',
                'label'  => 'Nama Perusahaan',
                'rules'  => 'trim|required',
                'errors' => ['required' => '<h6>%s harus diisi.</h6>']
            ],
            [
                'field'  => 'divisi',
                'label'  => 'Divisi / Departemen',
                'rules'  => 'trim|required',
                'errors' => ['required' => '<h6>%s harus diisi.</h6>']
            ],
            [
                'field'  => 'telefon',
                'label'  => 'Nomor Telefon',
                'rules'  => 'trim|required',
                'errors' => ['required' => '<h6>%s harus diisi.</h6>']
            ],
            [
                'field'  => 'alamat',
                'label'  => 'Alamat',
                'rules'  => 'trim|required',
                'errors' => ['required' => '<h6>%s harus diisi.</h6>']
            ],
        ];
    }

    public function run($input)
    {
        $data = [
            'nama'    => $input->nama,
            'divisi'  => $input->divisi,
            'telefon' => $input->telefon,
            'alamat'  => $input->alamat,
            'status'  => 'aktif',
        ];

        $this->create($data);
        return true;
    }
}

/* End of file Recipient_model.php */
