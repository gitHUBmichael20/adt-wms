<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Keranjang Keluar
 */
class Cartout extends MY_Controller 
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

        $data['title']              = 'Easy WMS - Keranjang Keluar';
        $data['breadcrumb_title']   = "Keranjang Keluar";
        $data['breadcrumb_path']    = 'Barang Keluar / Keranjang Keluar';
        $data['page']               = 'pages/cartout/index';
        $data['content']            = $this->cartout->select([
                'barang.id AS id_barang', 'barang.nama', 'barang.harga',
                'barang.id_satuan', 'keranjang_keluar.id AS id', 
                'keranjang_keluar.qty AS qty_barang_keluar'
            ])
            ->where('keranjang_keluar.id_user', $this->id_user)
            ->join('barang')
            ->get();

        // Load list penerima untuk dropdown
        $this->load->model('Recipients_model', 'recipients_list');
        $data['recipients'] = $this->recipients_list->get();

        $this->view($data);
    }

    /**
     * Menampung barang yang akan dikurangi kuantitasnya
     */
    public function add()
    {
        if (!$_POST || $this->input->post('qty_keluar') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('items'));
            return;
        }
        
        $input = (object) $this->input->post(null, true);

        // Mekanisme penambahan kuantitas
        // Ambil suatu barang di keranjang untuk dicek apakah barang tersebut sudah dimasukan
        $this->cartout->table = 'keranjang_keluar';
        $cart = $this->cartout->where('id_barang', $input->id_barang)
                             ->where('id_user', $this->id_user)
                             ->first();

        if ($cart) {    // Jika ternyata sudah dimasukan user, maka update cart
            $data = ['qty' => $cart->qty + $input->qty_keluar];

            // Update data
            if ($this->cartout->where('id', $cart->id)
                              ->where('id_user', $this->id_user)
                              ->update($data)
            ) {
                $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
            } else {
                $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            }

            redirect(base_url('cartout'));
            return;
        }

        // --- Insert cart baru ---
        $data = [
            'id_user'   => $this->id_user,
            'id_barang' => $input->id_barang,
            'qty'       => $input->qty_keluar
        ];

        if ($this->cartout->create($data)) {   // Jika insert berhasil
            $this->session->set_flashdata('success', 'Barang berhasil dimasukan ke keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartout'));
    }

    /**
     * Update kuantitas di keranjang belanja
     */
    public function update()
    {
        if (!$_POST || $this->input->post('qty_barang_keluar') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('cartout'));
        }

        $id = $this->input->post('id');
        $id_barang = $this->input->post('id_barang');

        // Mengambil data dari keranjang
        $data['content'] = $this->cartout->where('id_barang', $id_barang)
                                         ->where('id', $id)
                                         ->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Data tidak ditemukan');
            redirect(base_url('cartout'));
        }

        $data['input'] = (object) $this->input->post(null, true);

        // Update kuantitas
        $cart = ['qty' => $data['input']->qty_barang_keluar];

        if ($this->cartout->where('id', $id)
                         ->where('id_barang', $id_barang)
                         ->where('id_user', $this->id_user)
                         ->update($cart)
        ) {
            // Jika update berhasil
            $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartout'));
    }

    /**
     * Delete suatu cart di halaman cart
     */
    public function delete()
    {
        if (!$_POST) {
            // Jika diakses tidak dengan menggunakan method post, kembalikan ke home (forbidden)
            $this->session->set_flashdata('error', 'Akses pengeluaran barang dari keranjang ditolak!');
            redirect(base_url('home'));
        }

        $id = $this->input->post('id');

        if (!$this->cartout->where('id', $id)->first()) {  // Jika cart tidak ditemukan
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('cartout'));
        }

        if ($this->cartout->where('id', $id)->delete()) {  // Jika penghapusan cart berhasil
            $this->session->set_flashdata('success', '1 Barang berhasil dikeluarkan dari keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('cartout'));
    }

    /**
     * Menghapus seluruh isi keranjang
     */
    public function drop()
    {
        if (!$_POST) {
            $this->session->set_flashdata('error', 'Aksi ditolak');
            redirect(base_url('cartout'));
        }

        if ($this->cartout->where('id_user', $this->id_user)->count() < 1) {
            $this->session->set_flashdata('warning', 'Tidak ada barang di dalam keranjang');
            redirect(base_url('cartout'));
        }

        // Hapus seluruh isi keranjang dari user
        $this->cartout->where('id_user', $this->id_user)->delete();

        // Jika tabel keranjang dari seluruh user kosong, reset autoincrement id keranjang
        if ($this->cartout->count() < 1) { 
            $this->cartout->resetIndex();
        }

        $this->session->set_flashdata('success', 'Keranjang keluar anda telah dibersihkan');

        redirect(base_url('cartout'));
    }

    /**
     * Fungsi tombol checkout
     * Fungsi ini memasukan informasi pengeluaran barang ke tabel 'barang_keluar' 
     * dan memindahkan list keranjang keluar ke tabel 'barang_keluar_detail'
     * Setelah checkout berhasil, notifikasi email dikirim ke carlosimbolon23@gmail.com
     */
    public function checkout()
    {
        if (!isset($this->id_user)) {
            $this->session->set_flashdata('error', 'Akses checkout ditolak!');
            redirect(base_url('home'));
        }

        // Cek apakah user memiliki barang keluar yang pending di keranjang
        $outputCartCount = $this->cartout->where('id_user', $this->id_user)->count();
        
        if (!$outputCartCount) {
            $this->session->set_flashdata('warning', 'Tidak ada barang yang akan dikeluarkan!');
            redirect(base_url('cartout'));
        }

        if (!$this->cartout->validateStock()) { // Valdasi stok
            return $this->index();
        }

        // Ambil data form dari POST
        $id_penerima = $this->input->post('id_penerima');
        $no_po       = $this->input->post('no_po');
        $keterangan  = $this->input->post('keterangan');

        // Menyiapkan insert table barang_keluar
        $data['id_user']      = $this->id_user;
        $data['id_penerima']  = $id_penerima ? $id_penerima : null;
        $data['no_po']        = $no_po ? $no_po : null;
        $data['keterangan']   = $keterangan ? $keterangan : null;
        $this->cartout->table = 'barang_keluar';

        // Jika insert barang_keluar berhasil, siapkan insert lagi ke dalam barang_keluar_detail
        if ($id_barang_keluar = $this->cartout->create($data)) { 
            // Ambil list keranjang user
            $cart = $this->db->where('id_user', $this->id_user) 
                             ->get('keranjang_keluar')
                             ->result_array();

            // Modifikasi tiap cart
            foreach ($cart as $row) {
                $row['id_barang_keluar'] = $id_barang_keluar;
                unset($row['id'], $row['id_user']);                 // Hapus kolom tidak penting
                $this->db->insert('barang_keluar_detail', $row);    // Insert ke tabel barang_keluar_detail
            }

            $this->db->delete('keranjang_keluar', ['id_user' => $this->id_user]);    // Hapus cart user sekarang

            $this->session->set_flashdata('success', 'Pengeluaran barang berhasil');

            $data['title']              = 'Checkout';
            $data['breadcrumb_title']   = "Checkout";
            $data['breadcrumb_path']    = 'Barang Keluar / Keranjang Keluar / Checkout';
            $data['page']               = 'pages/cartout/checkout';

            // Ambil data pengeluaran barang untuk ditampilkan di halaman checkout
            $this->table = 'barang_keluar';
            $data['barang_keluar']  = $this->cartout->select([
                    'user.id AS id_user', 'user.nama', 'user.email',
                    'barang_keluar.id AS id_barang_keluar', 'barang_keluar.waktu',
                    'barang_keluar.no_po', 'barang_keluar.keterangan',
                    'barang_keluar.id_penerima'
                ])
                ->join('user')
                ->where('barang_keluar.id', $id_barang_keluar)
                ->where('barang_keluar.id_user', $this->id_user)
                ->first();

            $this->cartout->table = 'barang_keluar_detail';
            $data['list_barang'] = $this->cartout->select([
                    'barang_keluar_detail.qty',
                    'barang.id_satuan', 'barang.nama', 'barang.harga',
                ])
                ->join('barang')
                ->where('barang_keluar_detail.id_barang_keluar', $id_barang_keluar)
                ->get();

            // Load data penerima jika ada
            if ($id_penerima) {
                $this->load->model('Recipients_model', 'recipients_co');
                $data['penerima'] = $this->recipients_co->where('id', $id_penerima)->first();
            } else {
                $data['penerima'] = null;
            }

            // ── Kirim email notifikasi ──────────────────────────────────────
            $this->_send_email_keluar($data['barang_keluar'], $data['list_barang']);
            // ───────────────────────────────────────────────────────────────

            $this->view($data);
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            $this->index();
        }
    }

    /**
     * Kirim email notifikasi BARANG KELUAR ke carlosimbolon23@gmail.com
     * Dipanggil otomatis setelah checkout berhasil, dari semua user/admin
     */
    private function _send_email_keluar($barang_keluar, $list_barang)
    {
        $total_keseluruhan = 0;
        $rows_html         = '';

        foreach ($list_barang as $item) {
            $subtotal               = $item->qty * $item->harga;
            $total_keseluruhan     += $subtotal;
            $rows_html             .= '
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8;">'
                        . htmlspecialchars($item->nama) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:center;">'
                        . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:right;">Rp '
                        . number_format($item->harga, 0, ',', '.') . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:right;">Rp '
                        . number_format($subtotal, 0, ',', '.') . '</td>
                </tr>';
        }

        $nama_user      = isset($barang_keluar->nama)  ? htmlspecialchars($barang_keluar->nama)  : '-';
        $email_user     = isset($barang_keluar->email) ? htmlspecialchars($barang_keluar->email) : '-';
        $id_transaksi   = isset($barang_keluar->id_barang_keluar) ? $barang_keluar->id_barang_keluar : '-';
        $waktu          = isset($barang_keluar->waktu) ? $barang_keluar->waktu : date('Y-m-d H:i:s');
        $waktu_fmt      = date('d F Y, H:i:s', strtotime($waktu));

        $subject = '[BARANG KELUAR] Transaksi #' . $id_transaksi . ' — Easy WMS';

        $message = '
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"/></head>
<body style="margin:0;padding:0;background:#f4f0f0;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f0f0;padding:32px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

      <!-- HEADER -->
      <tr>
        <td style="background:#b91c1c;padding:28px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <div style="font-size:11px;color:#fca5a5;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;">Easy WMS — Notifikasi Sistem</div>
                <div style="font-size:22px;font-weight:700;color:#ffffff;">📤 Barang Keluar</div>
              </td>
              <td align="right">
                <div style="background:#ffffff20;border-radius:6px;padding:8px 14px;display:inline-block;">
                  <div style="font-size:10px;color:#fca5a5;text-transform:uppercase;letter-spacing:1px;">ID Transaksi</div>
                  <div style="font-size:18px;font-weight:700;color:#ffffff;">#' . $id_transaksi . '</div>
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- INTRO -->
      <tr>
        <td style="padding:24px 32px 0;">
          <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">
            Kepada Yth. Tim Manajemen,<br><br>
            Sistem <strong>Easy WMS</strong> mencatat bahwa telah terjadi transaksi 
            <strong>Barang Keluar</strong> yang berhasil diproses. Berikut adalah 
            detail lengkap transaksi tersebut.
          </p>
        </td>
      </tr>

      <!-- INFO TRANSAKSI -->
      <tr>
        <td style="padding:20px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdf6f6;border-radius:6px;border:1px solid #fecaca;overflow:hidden;">
            <tr>
              <td colspan="2" style="padding:12px 16px;background:#fee2e2;border-bottom:1px solid #fecaca;">
                <strong style="font-size:12px;color:#b91c1c;text-transform:uppercase;letter-spacing:0.8px;">Informasi Transaksi</strong>
              </td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #fce8e8;">Jenis Transaksi</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#b91c1c;border-bottom:1px solid #fce8e8;">
                &#x2B06; BARANG KELUAR
              </td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #fce8e8;">ID Transaksi</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#111827;border-bottom:1px solid #fce8e8;">#' . $id_transaksi . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #fce8e8;">Waktu Transaksi</td>
              <td style="padding:10px 16px;font-size:13px;color:#111827;border-bottom:1px solid #fce8e8;">' . $waktu_fmt . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #fce8e8;">Diproses Oleh</td>
              <td style="padding:10px 16px;font-size:13px;color:#111827;border-bottom:1px solid #fce8e8;"><strong>' . $nama_user . '</strong></td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;">Email Pengguna</td>
              <td style="padding:10px 16px;font-size:13px;color:#111827;">' . $email_user . '</td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- TABEL BARANG -->
      <tr>
        <td style="padding:0 32px 20px;">
          <div style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;">Detail Barang</div>
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #fecaca;border-radius:6px;overflow:hidden;">
            <tr style="background:#fee2e2;">
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:left;text-transform:uppercase;letter-spacing:0.5px;">Nama Barang</th>
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:center;text-transform:uppercase;letter-spacing:0.5px;">Qty</th>
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:right;text-transform:uppercase;letter-spacing:0.5px;">Harga Satuan</th>
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:right;text-transform:uppercase;letter-spacing:0.5px;">Subtotal</th>
            </tr>
            ' . $rows_html . '
            <tr style="background:#fdf6f6;">
              <td colspan="3" style="padding:12px 14px;font-size:13px;font-weight:700;color:#374151;text-align:right;">TOTAL KESELURUHAN</td>
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#b91c1c;text-align:right;">
                Rp ' . number_format($total_keseluruhan, 0, ',', '.') . '
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- FOOTER -->
      <tr>
        <td style="background:#fdf6f6;border-top:1px solid #fce8e8;padding:20px 32px;">
          <p style="font-size:12px;color:#9ca3af;margin:0;line-height:1.6;">
            Email ini dikirim secara otomatis oleh sistem <strong>Easy WMS</strong>.<br>
            Harap tidak membalas email ini. Jika ada pertanyaan, hubungi administrator sistem.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>';

        $this->email->initialize([
            'mailtype' => 'html',
            'charset'  => 'utf-8',
        ]);
        $this->email->clear();
        $this->email->from($this->config->item('smtp_user'), 'Easy WMS Notification');
        $this->email->to('carlosimbolon23@gmail.com');
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
        // Email error tidak di-throw agar tidak mengganggu alur utama aplikasi
    }
}


/* End of file Cartout.php */
