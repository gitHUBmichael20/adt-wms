<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Keranjang Masuk
 */
class Cartin extends MY_Controller 
{
    private $id_user;

    public function __construct()
    {
        parent::__construct();
        
        $is_login       = $this->session->userdata('is_login');
        $this->id_user  = $this->session->userdata('id_user');

        if (!$is_login) {
            $this->session->set_flashdata('warning', 'Anda belum login');
            redirect(base_url('login'));
            return;
        }
    }

    public function index()
    {
        $this->session->unset_userdata('keyword');

        $data['title']              = 'Easy WMS - Keranjang Masuk';
        $data['breadcrumb_title']   = "Keranjang Masuk";
        $data['breadcrumb_path']    = 'Barang Masuk / Keranjang Masuk';
        $data['page']               = 'pages/cartin/index';
        $data['content']            = $this->cartin->select([
                'barang.id AS id_barang', 'barang.nama', 'barang.harga',
                'barang.id_satuan', 'keranjang_masuk.id AS id', 
                'keranjang_masuk.qty AS qty_barang_masuk', 'keranjang_masuk.subtotal'
            ])
            ->where('keranjang_masuk.id_user', $this->id_user)
            ->join('barang')
            ->get();

        // Kirim list supplier untuk dropdown
        $data['suppliers'] = getSuppliers();

        $this->view($data);
    }

    /**
     * Menampung barang yang akan ditambah kuantitasnya
     */
    public function add()
    {
        if (!$_POST || $this->input->post('qty_masuk') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('items'));
            return;
        }
        
        $input = (object) $this->input->post(null, true);

        // Mengambil data barang yang dipilih
        $this->cartin->table = 'barang';
        $barang = $this->cartin->where('id', $input->id_barang)->first();

        // Mekanisme penambahan kuantitas
        $this->cartin->table = 'keranjang_masuk';
        $cart = $this->cartin->where('id_barang', $input->id_barang)
                             ->where('id_user', $this->id_user)
                             ->first();

        // Subtotal sudah termasuk PPN 11%: harga * qty * 1.11
        $subtotal_penambahan = round($barang->harga * $input->qty_masuk * 1.11);

        if ($cart) {
            $data = [
                'qty'       => $cart->qty + $input->qty_masuk,
                'subtotal'  => $cart->subtotal + $subtotal_penambahan
            ];

            if ($this->cartin->where('id', $cart->id)
                             ->where('id_user', $this->id_user)
                             ->update($data)
            ) {
                $this->session->set_flashdata('success', 'Barang berhasil ditambahkan');
            } else {
                $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            }

            redirect(base_url('cartin'));
            return;
        }

        $data = [
            'id_user'   => $this->id_user,
            'id_barang' => $input->id_barang,
            'qty'       => $input->qty_masuk
        ];

        if ($this->cartin->create($data)) {
            $this->session->set_flashdata('success', 'Barang berhasil dimasukan ke keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartin'));
    }

    /**
     * Update kuantitas di keranjang belanja
     */
    public function update()
    {
        if (!$_POST || $this->input->post('qty_barang_masuk') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('cartin'));
        }

        $id = $this->input->post('id');
        $id_barang = $this->input->post('id_barang');

        $data['content'] = $this->cartin->where('id_barang', $id_barang)
                                        ->where('id', $id)
                                        ->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Data tidak ditemukan');
            redirect(base_url('cartin'));
        }

        $this->cartin->table = 'barang';
        $barang = $this->cartin->where('id', $data['content']->id_barang)->first();

        $data['input'] = (object) $this->input->post(null, true);
        // Subtotal sudah termasuk PPN 11%: harga * qty * 1.11
        $subtotal_pembaharuan = round($data['input']->qty_barang_masuk * $barang->harga * 1.11);

        $cart = [
            'qty'      => $data['input']->qty_barang_masuk,
            'subtotal' => $subtotal_pembaharuan
        ];

        $this->cartin->table  = 'keranjang_masuk';
        if ($this->cartin->where('id', $id)
                         ->where('id_barang', $id_barang)
                         ->where('id_user', $this->id_user)
                         ->update($cart)
        ) {
            $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartin'));
    }

    /**
     * Delete suatu cart di halaman cart
     */
    public function delete()
    {
        if (!$_POST) {
            $this->session->set_flashdata('error', 'Akses pengeluaran barang dari keranjang ditolak!');
            redirect(base_url('home'));
        }

        $id = $this->input->post('id');

        if (!$this->cartin->where('id', $id)->first()) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('cartin'));
        }

        if ($this->cartin->where('id', $id)->delete()) {
            $this->session->set_flashdata('success', '1 Barang berhasil dikeluarkan dari keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('cartin'));
    }

    /**
     * Menghapus seluruh isi keranjang
     */
    public function drop()
    {
        if (!$_POST) {
            $this->session->set_flashdata('error', 'Aksi ditolak');
            redirect(base_url('cartin'));
        }

        if ($this->cartin->where('id_user', $this->id_user)->count() < 1) {
            $this->session->set_flashdata('warning', 'Tidak ada barang di dalam keranjang');
            redirect(base_url('cartin'));
        }

        $this->cartin->where('id_user', $this->id_user)->delete();

        if ($this->cartin->count() < 1) { 
            $this->cartin->resetIndex();
        }

        $this->session->set_flashdata('success', 'Keranjang masuk anda telah dibersihkan');

        redirect(base_url('cartin'));
    }

    /**
     * Fungsi tombol checkout
     * Menyimpan id_supplier dari POST ke tabel barang_masuk
     */
    public function checkout()
    {
        if (!isset($this->id_user)) {
            $this->session->set_flashdata('error', 'Akses checkout ditolak!');
            redirect(base_url('home'));
        }

        // Validasi: supplier harus dipilih
        $id_supplier = $this->input->post('id_supplier');
        if (empty($id_supplier)) {
            $this->session->set_flashdata('error', 'Harap pilih supplier terlebih dahulu!');
            redirect(base_url('cartin'));
            return;
        }

        // Validasi: supplier harus ada di database
        $supplier_check = $this->db->where('id', $id_supplier)->where('status', 'aktif')->get('supplier')->row();
        if (!$supplier_check) {
            $this->session->set_flashdata('error', 'Supplier tidak valid!');
            redirect(base_url('cartin'));
            return;
        }

        // Cek apakah user memiliki barang masukan pending di keranjang
        $inputCartCount = $this->cartin->where('id_user', $this->id_user)->count();
        
        if (!$inputCartCount) {
            $this->session->set_flashdata('warning', 'Tidak ada barang yang akan dimasukan!');
            redirect(base_url('cartin'));
        }

        // Insert ke barang_masuk dengan id_supplier
        $data['id_user']     = $this->id_user;
        $data['id_supplier'] = (int) $id_supplier;
        $this->cartin->table = 'barang_masuk';

        if ($id_barang_masuk = $this->cartin->create($data)) { 
            // Ambil list keranjang user
            $cart = $this->db->where('id_user', $this->id_user) 
                             ->get('keranjang_masuk')
                             ->result_array();

            foreach ($cart as $row) {
                $row['id_barang_masuk'] = $id_barang_masuk;
                unset($row['id'], $row['id_user']);
                $this->db->insert('barang_masuk_detail', $row);
            }

            $this->db->delete('keranjang_masuk', ['id_user' => $this->id_user]);

            $this->session->set_flashdata('success', 'Penambahan stok berhasil');

            $data['title']              = 'Checkout';
            $data['breadcrumb_title']   = "Checkout";
            $data['breadcrumb_path']    = 'Barang Masuk / Keranjang Masuk / Checkout';
            $data['page']               = 'pages/cartin/checkout';

            // Ambil data pemasukan barang + join supplier
            $this->cartin->table = 'barang_masuk';
            $data['barang_masuk']  = $this->db
                ->select('barang_masuk.id AS id_barang_masuk, barang_masuk.waktu, barang_masuk.id_user, user.nama, user.email, supplier.nama AS nama_supplier, supplier.telefon AS telefon_supplier, supplier.alamat AS alamat_supplier')
                ->join('user', 'barang_masuk.id_user = user.id', 'left')
                ->join('supplier', 'barang_masuk.id_supplier = supplier.id', 'left')
                ->where('barang_masuk.id', $id_barang_masuk)
                ->get('barang_masuk')
                ->row();

            $this->cartin->table = 'barang_masuk_detail';
            $data['list_barang'] = $this->cartin->select([
                    'barang_masuk_detail.qty', 'barang_masuk_detail.subtotal',
                    'barang.id_satuan', 'barang.nama', 'barang.harga',
                ])
                ->join('barang')
                ->where('barang_masuk_detail.id_barang_masuk', $id_barang_masuk)
                ->get();

            // Kirim email notifikasi
            $this->_send_email_masuk($data['barang_masuk'], $data['list_barang']);

            $this->view($data);

        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            $this->index();
        }
    }

    /**
     * Kirim email notifikasi BARANG MASUK
     */
    private function _send_email_masuk($barang_masuk, $list_barang)
    {
        $total_keseluruhan = 0;
        $rows_html         = '';

        foreach ($list_barang as $item) {
            // Subtotal sudah termasuk PPN 11%
            $subtotal               = round($item->qty * $item->harga * 1.11);
            $total_keseluruhan     += $subtotal;
            $rows_html             .= '
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9;">'
                        . htmlspecialchars($item->nama) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9; text-align:center;">'
                        . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9; text-align:right;">Rp '
                        . number_format($item->harga, 0, ',', '.') . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9; text-align:right;">Rp '
                        . number_format($subtotal, 0, ',', '.') . '<br><small style="color:#888;">(incl. PPN 11%)</small></td>
                </tr>';
        }

        $nama_user      = isset($barang_masuk->nama)          ? htmlspecialchars($barang_masuk->nama)          : '-';
        $email_user     = isset($barang_masuk->email)         ? htmlspecialchars($barang_masuk->email)         : '-';
        $nama_supplier  = isset($barang_masuk->nama_supplier) ? htmlspecialchars($barang_masuk->nama_supplier) : '-';
        $id_transaksi   = isset($barang_masuk->id_barang_masuk) ? $barang_masuk->id_barang_masuk : '-';
        $waktu          = isset($barang_masuk->waktu) ? $barang_masuk->waktu : date('Y-m-d H:i:s');
        $waktu_fmt      = date('d F Y, H:i:s', strtotime($waktu));

        $subject = '[BARANG MASUK] Transaksi #' . $id_transaksi . ' — Easy WMS';

        $message = '
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"/></head>
<body style="margin:0;padding:0;background:#f0f4f0;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f0;padding:32px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
      <tr>
        <td style="background:#1a7a3c;padding:28px 32px;">
          <div style="font-size:11px;color:#a8ddb5;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;">Easy WMS — Notifikasi Sistem</div>
          <div style="font-size:22px;font-weight:700;color:#ffffff;">📦 Barang Masuk</div>
        </td>
      </tr>
      <tr>
        <td style="padding:24px 32px 0;">
          <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">
            Kepada Yth. Tim Manajemen,<br><br>
            Transaksi <strong>Barang Masuk #' . $id_transaksi . '</strong> berhasil diproses pada ' . $waktu_fmt . '.
          </p>
        </td>
      </tr>
      <tr>
        <td style="padding:20px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6faf7;border-radius:6px;border:1px solid #d1e8d4;">
            <tr><td colspan="2" style="padding:12px 16px;background:#e8f5eb;border-bottom:1px solid #d1e8d4;"><strong style="font-size:12px;color:#1a7a3c;text-transform:uppercase;">Informasi Transaksi</strong></td></tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #e8f0e9;">Diproses Oleh</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;border-bottom:1px solid #e8f0e9;">' . $nama_user . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #e8f0e9;">Supplier</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#1a7a3c;border-bottom:1px solid #e8f0e9;">' . $nama_supplier . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;">Email Pengguna</td>
              <td style="padding:10px 16px;font-size:13px;">' . $email_user . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="padding:0 32px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #d1e8d4;border-radius:6px;overflow:hidden;">
            <tr style="background:#e8f5eb;">
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:left;">Nama Barang</th>
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:center;">Qty</th>
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:right;">Harga Satuan</th>
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:right;">Subtotal</th>
            </tr>
            ' . $rows_html . '
            <tr style="background:#f6faf7;">
              <td colspan="3" style="padding:12px 14px;font-size:13px;font-weight:700;text-align:right;">TOTAL</td>
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#1a7a3c;text-align:right;">Rp ' . number_format($total_keseluruhan, 0, ',', '.') . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f6faf7;border-top:1px solid #e8f0e9;padding:20px 32px;">
          <p style="font-size:12px;color:#9ca3af;margin:0;">Email ini dikirim otomatis oleh <strong>Easy WMS</strong>.</p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>';

        $this->email->initialize(['mailtype' => 'html', 'charset' => 'utf-8']);
        $this->email->clear();
        $this->email->from($this->config->item('smtp_user'), 'Easy WMS Notification');
        $this->email->to('carlosimbolon23@gmail.com');
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
}


/* End of file Cartin.php */
