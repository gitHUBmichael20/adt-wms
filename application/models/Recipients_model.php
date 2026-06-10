<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recipients_model extends MY_Model
{
    protected $table   = 'penerima';
    protected $perPage = 10;

    public function getValidationRules()
    {
        return [
            [
                'field'  => 'nama',
                'label'  => 'Nama Penerima',
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
}

/* End of file Recipients_model.php */
