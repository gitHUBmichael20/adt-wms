<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Toko_model
 *
 * Toko (cabang) adalah lokasi pengiriman spesifik milik satu Penerima
 * (perusahaan induk). Satu penerima bisa punya banyak toko.
 */
class Toko_model extends MY_Model
{
    protected $table = 'toko';

    public function getDefaultValues()
    {
        return [
            'id_penerima' => '',
            'nama_toko'   => '',
            'alamat'      => '',
            'pic'         => '',
            'telefon'     => '',
        ];
    }

    public function getValidationRules()
    {
        return [
            [
                'field'  => 'id_penerima',
                'label'  => 'Penerima',
                'rules'  => 'trim|required|numeric',
                'errors' => ['required' => '<h6>%s harus dipilih.</h6>']
            ],
            [
                'field'  => 'nama_toko',
                'label'  => 'Nama Toko / Cabang',
                'rules'  => 'trim|required',
                'errors' => ['required' => '<h6>%s harus diisi.</h6>']
            ],
            [
                'field'  => 'alamat',
                'label'  => 'Alamat Toko',
                'rules'  => 'trim|required',
                'errors' => ['required' => '<h6>%s harus diisi.</h6>']
            ],
        ];
    }

    /**
     * Ambil semua toko milik satu penerima
     */
    public function getByPenerima($id_penerima)
    {
        return $this->where('id_penerima', $id_penerima)
            ->orderBy('nama_toko', 'ASC')
            ->get();
    }

    public function run($input)
    {
        $data = [
            'id_penerima' => $input->id_penerima,
            'nama_toko'   => $input->nama_toko,
            'alamat'      => $input->alamat,
            'pic'         => !empty($input->pic) ? $input->pic : null,
            'telefon'     => !empty($input->telefon) ? $input->telefon : null,
            'status'      => 'aktif',
        ];

        return $this->create($data);
    }
}

/* End of file Toko_model.php */
